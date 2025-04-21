<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('SECRETARIA');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_nota'])) {
    $idNota = $_POST['id_nota'];
    $nota1 = $_POST['nota1'] !== '' ? $_POST['nota1'] : null;
    $nota2 = $_POST['nota2'] !== '' ? $_POST['nota2'] : null;
    $supletorio = $_POST['supletorio'] !== '' ? $_POST['supletorio'] : null;
    
    try {
        $sql = "UPDATE Notas SET Nota1 = ?, Nota2 = ?, Supletorio = ? WHERE IdNota = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nota1, $nota2, $supletorio, $idNota]);
        
        header("Location: notas.php?success=Notas actualizadas correctamente");
        exit;
    } catch (Exception $e) {
        header("Location: notas.php?error=Error al actualizar las notas: " . $e->getMessage());
        exit;
    }
} else {
    header("Location: notas.php?error=Petición inválida");
    exit;
}