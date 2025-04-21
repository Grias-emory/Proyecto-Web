<?php
require '../../config/conexion.php';

$cedula = $_GET['cedula'] ?? null;

if ($cedula) {
    $sql = "DELETE FROM usuarios WHERE Cedula = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$cedula]);
}

header("Location: usuarios.php");
exit;