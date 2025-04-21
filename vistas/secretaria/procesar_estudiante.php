<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('SECRETARIA');

// Datos del formulario
$modo = $_POST['modo'];
$cedula = $_POST['cedula'];
$contrasena = $_POST['contrasena'] ?? '';
$primerNombre = $_POST['primer_nombre'];
$segundoNombre = $_POST['segundo_nombre'];
$primerApellido = $_POST['primer_apellido'];
$segundoApellido = $_POST['segundo_apellido'];
$correo = $_POST['correo'];
$provincia = $_POST['provincia'];
$rol = 'ESTUDIANTE'; // Fijado para que secretaria solo cree estudiantes

try {
    if ($modo === 'crear') {
        // Validar que no exista ya la cédula
        $verificar = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE Cedula = ?");
        $verificar->execute([$cedula]);
        if ($verificar->fetchColumn() > 0) {
            echo "La cédula ya está registrada.";
            exit;
        }

        // Insertar nuevo estudiante
        $sql = "INSERT INTO usuarios 
                (Cedula, Contrasena, PrimerNombre, SegundoNombre, PrimerApellido, SegundoApellido, CorreoInstitucional, Provincia, Rol)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $cedula,
            password_hash($contrasena, PASSWORD_DEFAULT),
            $primerNombre,
            $segundoNombre,
            $primerApellido,
            $segundoApellido,
            $correo,
            $provincia,
            $rol
        ]);
    } elseif ($modo === 'editar') {
        // Actualizar datos, si hay nueva contraseña se actualiza
        if (!empty($contrasena)) {
            $sql = "UPDATE usuarios SET
                    Contrasena = ?,
                    PrimerNombre = ?,
                    SegundoNombre = ?,
                    PrimerApellido = ?,
                    SegundoApellido = ?,
                    CorreoInstitucional = ?,
                    Provincia = ?
                    WHERE Cedula = ? AND Rol = 'ESTUDIANTE'";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                password_hash($contrasena, PASSWORD_DEFAULT),
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $correo,
                $provincia,
                $cedula
            ]);
        } else {
            $sql = "UPDATE usuarios SET
                    PrimerNombre = ?,
                    SegundoNombre = ?,
                    PrimerApellido = ?,
                    SegundoApellido = ?,
                    CorreoInstitucional = ?,
                    Provincia = ?
                    WHERE Cedula = ? AND Rol = 'ESTUDIANTE'";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $correo,
                $provincia,
                $cedula
            ]);
        }
    }

    header("Location: usuarios.php");
    exit;

} catch (PDOException $e) {
    echo "Error al procesar estudiante: " . $e->getMessage();
}
