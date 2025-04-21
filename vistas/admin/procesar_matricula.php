<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('ADMINISTRADOR');

$modo = $_POST['modo'];
$cedulaEstudiante = $_POST['cedula_estudiante'];
$cedulaSecretaria = $_POST['cedula_secretaria'];
$idMateria = $_POST['id_materia'];
$repite = $_POST['repite_materia']; // 1, 2 o 3

// Validar que el estudiante tenga rol ESTUDIANTE
$stmtEstudiante = $conexion->prepare("SELECT Rol FROM Usuarios WHERE Cedula = ?");
$stmtEstudiante->execute([$cedulaEstudiante]);
$estudiante = $stmtEstudiante->fetch(PDO::FETCH_ASSOC);

if (!$estudiante || $estudiante['Rol'] !== 'ESTUDIANTE') {
    // Redirigir con mensaje de error
    header("Location: matriculas.php?error=El usuario con cédula $cedulaEstudiante no es un estudiante");
    exit;
}

// Validar que la secretaria tenga rol SECRETARIA o ADMINISTRADOR
$stmtSecretaria = $conexion->prepare("SELECT Rol FROM Usuarios WHERE Cedula = ?");
$stmtSecretaria->execute([$cedulaSecretaria]);
$secretaria = $stmtSecretaria->fetch(PDO::FETCH_ASSOC);

if (!$secretaria || ($secretaria['Rol'] !== 'SECRETARIA' && $secretaria['Rol'] !== 'ADMINISTRADOR')) {
    // Redirigir con mensaje de error
    header("Location: matriculas.php?error=El usuario con cédula $cedulaSecretaria no es secretaria ni administrador");
    exit;
}

try {
    // Iniciar transacción para garantizar la integridad de los datos
    $conexion->beginTransaction();

    if ($modo === 'crear') {
        $sql = "INSERT INTO Matriculas (CedulaEstudiante, CedulaSecretaria, IdMateria, RepiteMateria)
                VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$cedulaEstudiante, $cedulaSecretaria, $idMateria, $repite]);
        
        // Obtener el ID de la matrícula recién creada
        $idMatricula = $conexion->lastInsertId();
        
        // Crear registro de notas automáticamente
        $sqlNotas = "INSERT INTO Notas (IdMatricula, Nota1, Nota2, Supletorio)
                     VALUES (?, 0, 0, 0)";
        $stmtNotas = $conexion->prepare($sqlNotas);
        $stmtNotas->execute([$idMatricula]);
        
    } elseif ($modo === 'editar') {
        $id = $_POST['id_matricula'];
        $sql = "UPDATE Matriculas
                SET CedulaEstudiante = ?, CedulaSecretaria = ?, IdMateria = ?, RepiteMateria = ?
                WHERE IdMatricula = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$cedulaEstudiante, $cedulaSecretaria, $idMateria, $repite, $id]);
    }
    
    // Confirmar la transacción
    $conexion->commit();
    
    header("Location: matriculas.php?success=Operación realizada con éxito");
    exit;
    
} catch (Exception $e) {
    // Revertir los cambios en caso de error
    $conexion->rollBack();
    header("Location: matriculas.php?error=Error al procesar la operación: " . $e->getMessage());
    exit;
}