<?php
require '../../config/conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conexion->prepare("DELETE FROM Materias WHERE IdMateria = ?");
    $stmt->execute([$id]);
}

header("Location: materias.php");
exit;