<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('ADMINISTRADOR');

if (isset($_GET['id'])) {
    $idMatricula = $_GET['id'];
    
    try {
        // Iniciar transacción para garantizar la integridad de los datos
        $conexion->beginTransaction();
        
        // Primero eliminar las notas asociadas
        $stmtNotas = $conexion->prepare("DELETE FROM Notas WHERE IdMatricula = ?");
        $stmtNotas->execute([$idMatricula]);
        
        // Luego eliminar la matrícula
        $stmtMatricula = $conexion->prepare("DELETE FROM Matriculas WHERE IdMatricula = ?");
        $stmtMatricula->execute([$idMatricula]);
        
        // Confirmar la transacción
        $conexion->commit();
        
        header("Location: matriculas.php?success=Matrícula eliminada con éxito");
        exit;
        
    } catch (Exception $e) {
        // Revertir los cambios en caso de error
        $conexion->rollBack();
        header("Location: matriculas.php?error=Error al eliminar la matrícula: " . $e->getMessage());
        exit;
    }
}

header("Location: matriculas.php");
exit;