<?php
require '../../config/conexion.php';

$modo = $_POST['modo'] ?? 'crear';

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$creditos = $_POST['creditos'];
$semestre = $_POST['semestre'];
$profesor = $_POST['profesor'];

if ($modo === 'crear') {
    $sql = "INSERT INTO Materias (IdMateria, NombreMateria, Creditos, IdSemestre, IdProfesor)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id, $nombre, $creditos, $semestre, $profesor]);
} else if ($modo === 'editar') {
    $sql = "UPDATE Materias 
            SET NombreMateria=?, Creditos=?, IdSemestre=?, IdProfesor=?
            WHERE IdMateria=?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nombre, $creditos, $semestre, $profesor, $id]);
}

header("Location: materias.php");
exit;