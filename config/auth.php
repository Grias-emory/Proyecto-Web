<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

function verificarRol($rol) {
    if ($_SESSION['usuario']['rol'] !== $rol) {
        header('Location: ../../login.php');
        exit();
    }
}
?>