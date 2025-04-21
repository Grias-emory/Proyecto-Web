<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('ADMINISTRADOR');

// Filtrado y listado de notas
$filtro = $_GET['filtro'] ?? '';

$sql = "SELECT n.IdNota, n.IdMatricula, n.Nota1, n.Nota2, n.Supletorio,
               m.CedulaEstudiante, m.IdMateria,
               e.PrimerNombre AS NombreEstudiante, e.PrimerApellido AS ApellidoEstudiante,
               mat.NombreMateria
        FROM Notas n
        JOIN Matriculas m ON n.IdMatricula = m.IdMatricula
        JOIN Usuarios e ON m.CedulaEstudiante = e.Cedula
        JOIN Materias mat ON m.IdMateria = mat.IdMateria
        WHERE e.PrimerNombre LIKE :filtro OR e.PrimerApellido LIKE :filtro 
              OR m.CedulaEstudiante LIKE :filtro OR mat.NombreMateria LIKE :filtro
        ORDER BY n.IdNota DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute(['filtro' => "%$filtro%"]);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejo de la edición
$editar = false;
$notaEditar = null;

if (isset($_GET['editar'])) {
    $editar = true;
    $idNota = $_GET['editar'];
    
    $stmt = $conexion->prepare("SELECT * FROM Notas WHERE IdNota = ?");
    $stmt->execute([$idNota]);
    $notaEditar = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($notaEditar) {
        // Obtener información adicional de la matrícula
        $stmtMatricula = $conexion->prepare("
            SELECT m.*, 
                   e.PrimerNombre AS NombreEstudiante, e.PrimerApellido AS ApellidoEstudiante, 
                   mat.NombreMateria
            FROM Matriculas m
            JOIN Usuarios e ON m.CedulaEstudiante = e.Cedula
            JOIN Materias mat ON m.IdMateria = mat.IdMateria
            WHERE m.IdMatricula = ?
        ");
        $stmtMatricula->execute([$notaEditar['IdMatricula']]);
        $matriculaInfo = $stmtMatricula->fetch(PDO::FETCH_ASSOC);
    }
}

// Manejo de mensajes de error/éxito
$mensaje = '';
$tipoMensaje = '';

if (isset($_GET['error'])) {
    $mensaje = $_GET['error'];
    $tipoMensaje = 'danger';
} elseif (isset($_GET['success'])) {
    $mensaje = $_GET['success'];
    $tipoMensaje = 'success';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Notas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-rojo {
            background-color: #8B0000;
            color: white;
        }
        .btn-rojo:hover {
            background-color: #a10000;
        }
        .table thead {
            background-color: #8B0000;
            color: white;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-4">Gestión de Notas</h2>
    <a href="../../dashboard.php" class="btn btn-rojo mb-3">← Volver al Dashboard</a>
    <a href="matriculas.php" class="btn btn-secondary mb-3 ms-2">Gestionar Matrículas</a>

    <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if ($editar && $notaEditar && isset($matriculaInfo)): ?>
    <!-- Formulario de Edición de Notas -->
    <form action="procesar_nota.php" method="POST" class="border p-4 rounded shadow-sm bg-light mb-4">
        <input type="hidden" name="id_nota" value="<?= $notaEditar['IdNota'] ?>">
        
        <div class="row g-3">
            <div class="col-md-12">
                <h4>Editar notas de: <?= $matriculaInfo['NombreEstudiante'] . ' ' . $matriculaInfo['ApellidoEstudiante'] ?></h4>
                <p>Materia: <?= $matriculaInfo['NombreMateria'] ?></p>
            </div>
            <div class="col-md-4">
                <label for="nota1">Nota 1</label>
                <input type="number" name="nota1" id="nota1" class="form-control" step="0.01" min="0" max="10" 
                       value="<?= $notaEditar['Nota1'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label for="nota2">Nota 2</label>
                <input type="number" name="nota2" id="nota2" class="form-control" step="0.01" min="0" max="10"
                       value="<?= $notaEditar['Nota2'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label for="supletorio">Supletorio</label>
                <input type="number" name="supletorio" id="supletorio" class="form-control" step="0.01" min="0" max="10"
                       value="<?= $notaEditar['Supletorio'] ?? '' ?>">
            </div>
            <div class="col-md-12 text-end">
                <button class="btn btn-rojo mt-3" type="submit">Guardar Notas</button>
                <a href="notas.php" class="btn btn-secondary mt-3">Cancelar</a>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <!-- Buscador -->
    <form method="GET" class="mb-3">
        <input type="text" name="filtro" class="form-control" placeholder="Buscar por nombre de estudiante, cédula o materia" value="<?= htmlspecialchars($filtro) ?>">
    </form>

    <!-- Tabla de Notas -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover shadow-sm">
            <thead>
                <tr>
                    <th>ID Nota</th>
                    <th>Estudiante</th>
                    <th>Materia</th>
                    <th>Nota 1</th>
                    <th>Nota 2</th>
                    <th>Promedio</th>
                    <th>Supletorio</th>
                    <th>Nota Final</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notas as $n): 
                    // Calcular promedio
                    $promedio = null;
                    if (!is_null($n['Nota1']) && !is_null($n['Nota2'])) {
                        $promedio = ($n['Nota1'] + $n['Nota2']) / 2;
                    }
                    
                    // Calcular nota final
                    $notaFinal = $promedio;
                    if (!is_null($promedio) && $promedio < 7 && !is_null($n['Supletorio'])) {
                        $notaFinal = ($promedio + $n['Supletorio']) / 2;
                    }
                    
                    // Determinar estado
                    $estado = 'Pendiente';
                    if (!is_null($notaFinal)) {
                        $estado = $notaFinal >= 7 ? 'Aprobado' : 'Reprobado';
                    }
                ?>
                    <tr>
                        <td><?= $n['IdNota'] ?></td>
                        <td><?= $n['NombreEstudiante'] . ' ' . $n['ApellidoEstudiante'] ?><br>
                            <small class="text-muted"><?= $n['CedulaEstudiante'] ?></small></td>
                        <td><?= $n['NombreMateria'] ?></td>
                        <td><?= !is_null($n['Nota1']) ? number_format($n['Nota1'], 2) : '-' ?></td>
                        <td><?= !is_null($n['Nota2']) ? number_format($n['Nota2'], 2) : '-' ?></td>
                        <td><?= !is_null($promedio) ? number_format($promedio, 2) : '-' ?></td>
                        <td><?= !is_null($n['Supletorio']) ? number_format($n['Supletorio'], 2) : '-' ?></td>
                        <td><?= !is_null($notaFinal) ? number_format($notaFinal, 2) : '-' ?></td>
                        <td>
                            <?php if ($estado == 'Aprobado'): ?>
                                <span class="badge bg-success">Aprobado</span>
                            <?php elseif ($estado == 'Reprobado'): ?>
                                <span class="badge bg-danger">Reprobado</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="notas.php?editar=<?= $n['IdNota'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>