<?php 
require '../../config/conexion.php';

$modo = $_POST['modo'] ?? 'crear';

$cedula = $_POST['cedula'];
$nombre = $_POST['primer_nombre'];
$snombre = $_POST['segundo_nombre'];
$apellido = $_POST['primer_apellido'];
$sapellido = $_POST['segundo_apellido'];
$correo = $_POST['correo'];
$provincia = $_POST['provincia'];
$rol = $_POST['rol'];
$contrasena = $_POST['contrasena'];

if ($modo === 'crear') {
    $sql = "INSERT INTO usuarios (Cedula, PrimerNombre, SegundoNombre, PrimerApellido, SegundoApellido, CorreoInstitucional, Provincia, Rol, Contrasena)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$cedula, $nombre, $snombre, $apellido, $sapellido, $correo, $provincia, $rol, password_hash($contrasena, PASSWORD_DEFAULT)]);
} else if ($modo === 'editar') {
    if (!empty($contrasena)) {
        $sql = "UPDATE usuarios 
                SET PrimerNombre=?, SegundoNombre=?, PrimerApellido=?, SegundoApellido=?, 
                    CorreoInstitucional=?, Provincia=?, Rol=?, Contrasena=?
                WHERE Cedula=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nombre, $snombre, $apellido, $sapellido, $correo, $provincia, $rol, password_hash($contrasena, PASSWORD_DEFAULT), $cedula]);
    } else {
        $sql = "UPDATE usuarios 
                SET PrimerNombre=?, SegundoNombre=?, PrimerApellido=?, SegundoApellido=?, 
                    CorreoInstitucional=?, Provincia=?, Rol=?
                WHERE Cedula=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nombre, $snombre, $apellido, $sapellido, $correo, $provincia, $rol, $cedula]);
    }
}

header("Location: usuarios.php");
exit;