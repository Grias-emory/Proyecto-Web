<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('ESTUDIANTE');

$cedulaEstudiante = $_SESSION['usuario']['cedula'] ?? null;

if (!$cedulaEstudiante) {
    header('Location: ../../login.php');
    exit;
}

$stmt = $conexion->prepare("
    SELECT m.NombreMateria, n.Nota1, n.Nota2, n.Supletorio
    FROM Notas n
    JOIN Matriculas ma ON n.IdMatricula = ma.IdMatricula
    JOIN Materias m ON ma.IdMateria = m.IdMateria
    WHERE ma.CedulaEstudiante = ?
");
$stmt->execute([$cedulaEstudiante]);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Materias y Notas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #ffffff; }
        .navbar-custom { background-color: #8B0000; }
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link,
        .navbar-custom .navbar-text { color: #ffffff; }
        .btn-rojo { background-color: #8B0000; color: white; }
        .btn-rojo:hover { background-color: #a10000; }
        .table thead { background-color: #8B0000; color: white; }
        h2 { color: #8B0000; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Sistema Académico</a>
        <div class="d-flex">
            <span class="navbar-text me-3">Estudiante</span>
            <a class="btn btn-sm btn-rojo" href="../../logout.php">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Mis Materias y Notas</h2>

    <?php if (count($notas) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Nota 1</th>
                        <th>Nota 2</th>
                        <th>Supletorio</th>
                        <th>Promedio</th>
                        <th>Nota Final</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($notas as $nota):
                    $n1 = $nota['Nota1'];
                    $n2 = $nota['Nota2'];
                    $suple = $nota['Supletorio'];

                    $promedio = (!is_null($n1) && !is_null($n2)) ? ($n1 + $n2) / 2 : null;
                    $notaFinal = $promedio;

                    if ($promedio < 7 && !is_null($suple)) {
                        $notaFinal = ($promedio + $suple) / 2;
                    }

                    $estado = "Pendiente";
                    if (!is_null($notaFinal)) {
                        $estado = $notaFinal >= 7 ? "Aprobado" : "Reprobado";
                    }
                ?>
                    <tr>
                        <td><?= htmlspecialchars($nota['NombreMateria']) ?></td>
                        <td><?= is_null($n1) ? '-' : number_format($n1, 2) ?></td>
                        <td><?= is_null($n2) ? '-' : number_format($n2, 2) ?></td>
                        <td><?= is_null($suple) ? '-' : number_format($suple, 2) ?></td>
                        <td><?= is_null($promedio) ? '-' : number_format($promedio, 2) ?></td>
                        <td><?= is_null($notaFinal) ? '-' : number_format($notaFinal, 2) ?></td>
                        <td>
                            <?php if ($estado === 'Aprobado'): ?>
                                <span class="badge bg-success">Aprobado</span>
                            <?php elseif ($estado === 'Reprobado'): ?>
                                <span class="badge bg-danger">Reprobado</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No tienes materias matriculadas aún.</div>
    <?php endif; ?>
</div>

</body>
</html>
