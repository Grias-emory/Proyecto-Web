<?php
session_start();
require_once 'config/conexion.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'];
    $contrasena = $_POST['contrasena'];

    $stmt = $conexion->prepare("SELECT * FROM Usuarios WHERE Cedula = ?");
    $stmt->execute([$cedula]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['Contrasena'])) {
        $_SESSION['usuario'] = [
            'cedula' => $usuario['Cedula'],
            'nombre' => $usuario['PrimerNombre'] . ' ' . $usuario['PrimerApellido'],
            'rol' => $usuario['Rol'] // MUY IMPORTANTE
        ];
        header("Location: dashboard.php");
        exit();
    } else {
        $mensaje = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #800000; /* rojo oscuro */
            color: white;
        }
        .card {
            margin-top: 10%;
            background-color: white;
            color: #800000;
        }
        .btn-custom {
            background-color: #800000;
            color: white;
        }
        .btn-custom:hover {
            background-color: #a00000;
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            width: 500px;      /* <-- aquí cambias de 150px a 300px */
            height: auto;      /* mantiene la proporción original */         
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <!-- Imagen encima del formulario -->
            <img src="recursos/UTA.png" alt="Logo UTA" class="logo img-fluid">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="text-center mb-4">Iniciar Sesión</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Cédula:</label>
                            <input type="text" name="cedula" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Contraseña:</label>
                            <input type="password" name="contrasena" class="form-control" required>
                        </div>
                        <?php if ($mensaje): ?>
                            <div class="alert alert-danger"> <?= $mensaje ?> </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-custom w-100">Ingresar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
