<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('ADMINISTRADOR');

// Variables
$filtro = $_GET['filtro'] ?? '';
$materiaEditar = null;

// Obtener listado de semestres y profesores para los combos
$semestres = $conexion->query("SELECT * FROM Semestres")->fetchAll(PDO::FETCH_ASSOC);
$profesores = $conexion->query("SELECT Cedula, PrimerNombre, PrimerApellido FROM Usuarios WHERE Rol = 'PROFESOR'")->fetchAll(PDO::FETCH_ASSOC);

// Obtener listado de materias con filtros
$sql = "SELECT m.*, s.NombreSemestre, u.PrimerNombre, u.PrimerApellido 
        FROM Materias m
        JOIN Semestres s ON m.IdSemestre = s.IdSemestre
        JOIN Usuarios u ON m.IdProfesor = u.Cedula
        WHERE m.NombreMateria LIKE :filtro OR s.NombreSemestre LIKE :filtro
        ORDER BY m.NombreMateria ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute(['filtro' => "%$filtro%"]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si se va a editar
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $conexion->prepare("SELECT * FROM Materias WHERE IdMateria = ?");
    $stmt->execute([$id]);
    $materiaEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Materias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; }
        .navbar-custom { background-color: #004080; }
        .navbar-custom .navbar-brand, .navbar-custom .nav-link, .navbar-custom .navbar-text { color: #fff; }
        .btn-azul { background-color: #004080; color: white; }
        .btn-azul:hover { background-color: #0050a0; }
        .table thead { background-color: #004080; color: white; }
        h2 { color: #004080; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="#">Panel Administrador</a>
        <span class="navbar-text">Gestión de Materias</span>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">Gestión de Materias</h2>
    <a href="../../dashboard.php" class="btn btn-azul mb-3">← Volver al Dashboard</a>

    <!-- Formulario -->
    <form action="procesar_materia.php" method="POST" class="border p-4 rounded bg-light shadow-sm mb-4">
        <input type="hidden" name="modo" value="<?= $materiaEditar ? 'editar' : 'crear' ?>">
        <div class="row g-3">
            <div class="col-md-2">
                <input type="text" name="id" class="form-control" placeholder="ID Materia" required value="<?= $materiaEditar['IdMateria'] ?? '' ?>" <?= $materiaEditar ? 'readonly' : '' ?>>
            </div>
            <div class="col-md-4">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre de la materia" required value="<?= $materiaEditar['NombreMateria'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <input type="number" name="creditos" class="form-control" placeholder="Créditos" required value="<?= $materiaEditar['Creditos'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <select name="semestre" class="form-select" required>
                    <option value="">Selecciona Semestre</option>
                    <?php foreach ($semestres as $s): ?>
                        <option value="<?= $s['IdSemestre'] ?>" <?= (isset($materiaEditar) && $materiaEditar['IdSemestre'] == $s['IdSemestre']) ? 'selected' : '' ?>>
                            <?= $s['NombreSemestre'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="profesor" class="form-select" required>
                    <option value="">Selecciona Profesor</option>
                    <?php foreach ($profesores as $p): ?>
                        <option value="<?= $p['Cedula'] ?>" <?= (isset($materiaEditar) && $materiaEditar['IdProfesor'] == $p['Cedula']) ? 'selected' : '' ?>>
                            <?= $p['PrimerNombre'] . ' ' . $p['PrimerApellido'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-azul mt-3"><?= $materiaEditar ? 'Actualizar Materia' : 'Guardar Materia' ?></button>
            </div>
        </div>
    </form>

    <!-- Buscador -->
    <form method="GET" class="mb-3">
        <input type="text" name="filtro" class="form-control" placeholder="Buscar por nombre o semestre..." value="<?= htmlspecialchars($filtro) ?>">
    </form>

    <!-- Tabla de materias -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover shadow-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Créditos</th>
                    <th>Semestre</th>
                    <th>Profesor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materias as $m): ?>
                    <tr>
                        <td><?= $m['IdMateria'] ?></td>
                        <td><?= $m['NombreMateria'] ?></td>
                        <td><?= $m['Creditos'] ?></td>
                        <td><?= $m['NombreSemestre'] ?></td>
                        <td><?= $m['PrimerNombre'] . ' ' . $m['PrimerApellido'] ?></td>
                        <td>
                            <a href="materias.php?editar=<?= $m['IdMateria'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_materia.php?id=<?= $m['IdMateria'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta materia?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>