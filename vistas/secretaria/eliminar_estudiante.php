<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('SECRETARIA');

$cedula = $_GET['cedula'] ?? null;

if ($cedula) {
    try {
        // Verificar que el usuario sea estudiante antes de eliminar
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE Cedula = ? AND Rol = 'ESTUDIANTE'");
        $stmt->execute([$cedula]);
    } catch (PDOException $e) {
        echo "Error al eliminar estudiante: " . $e->getMessage();
        exit;
    }
}

header("Location: usuarios.php");
exit;
