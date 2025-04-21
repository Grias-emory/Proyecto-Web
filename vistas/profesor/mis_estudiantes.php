<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('PROFESOR');

$cedulaProfesor = $_SESSION['usuario']['cedula'] ?? null;

if (!$cedulaProfesor) {
    header('Location: ../../login.php');
    exit;
}

// Actualizar nota si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $idNota = $_POST['idNota'];
    $nota1 = $_POST['nota1'] !== '' ? floatval($_POST['nota1']) : null;
    $nota2 = $_POST['nota2'] !== '' ? floatval($_POST['nota2']) : null;
    $supletorio = $_POST['supletorio'] !== '' ? floatval($_POST['supletorio']) : null;

    $stmtUpdate = $conexion->prepare("
        UPDATE Notas 
        SET Nota1 = :nota1, Nota2 = :nota2, Supletorio = :supletorio 
        WHERE IdNota = :idNota
    ");
    $stmtUpdate->execute([
        ':nota1' => $nota1,
        ':nota2' => $nota2,
        ':supletorio' => $supletorio,
        ':idNota' => $idNota
    ]);
}

// Obtener materias del profesor
$stmtMaterias = $conexion->prepare("SELECT IdMateria, NombreMateria FROM Materias WHERE IdProfesor = ?");
$stmtMaterias->execute([$cedulaProfesor]);
$materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

$idMateriaSeleccionada = $_GET['materia'] ?? null;
$estudiantes = [];

if ($idMateriaSeleccionada) {
    $stmt = $conexion->prepare("
        SELECT n.IdNota, e.PrimerNombre, e.PrimerApellido, e.Cedula, 
               n.Nota1, n.Nota2, n.Supletorio
        FROM Notas n
        JOIN Matriculas m ON n.IdMatricula = m.IdMatricula
        JOIN Usuarios e ON m.CedulaEstudiante = e.Cedula
        WHERE m.IdMateria = ?
    ");
    $stmt->execute([$idMateriaSeleccionada]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        input[type="number"] {
            width: 80px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-4">Gestión de Usuarios</h2>
    <a href="../../dashboard.php" class="btn btn-rojo mb-3">← Volver al Dashboard</a>

<div class="container mt-5">
    <h2>Estudiantes por Materia</h2>
    <form method="GET" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="materia" class="col-form-label">Seleccione una materia:</label>
            </div>
            <div class="col-auto">
                <select name="materia" id="materia" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Elija una materia --</option>
                    <?php foreach ($materias as $mat): ?>
                        <option value="<?= $mat['IdMateria'] ?>" <?= ($idMateriaSeleccionada == $mat['IdMateria']) ? 'selected' : '' ?>>
                            <?= $mat['NombreMateria'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <?php if ($idMateriaSeleccionada): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Cédula</th>
                        <th>Estudiante</th>
                        <th>Nota 1</th>
                        <th>Nota 2</th>
                        <th>Supletorio</th>
                        <th>Promedio</th>
                        <th>Nota Final</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($estudiantes as $est):
                    $prom = (!is_null($est['Nota1']) && !is_null($est['Nota2'])) ? ($est['Nota1'] + $est['Nota2']) / 2 : null;
                    $final = $prom;
                    if ($prom < 7 && !is_null($est['Supletorio'])) {
                        $final = ($prom + $est['Supletorio']) / 2;
                    }

                    $estado = "Pendiente";
                    if (!is_null($final)) {
                        $estado = $final >= 7 ? "Aprobado" : "Reprobado";
                    }
                ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="idNota" value="<?= $est['IdNota'] ?>">
                            <input type="hidden" name="materia" value="<?= $idMateriaSeleccionada ?>">
                            <td><?= $est['Cedula'] ?></td>
                            <td><?= $est['PrimerNombre'] . ' ' . $est['PrimerApellido'] ?></td>
                            <td><input type="number" name="nota1" step="0.01" min="0" max="10" value="<?= $est['Nota1'] ?>"></td>
                            <td><input type="number" name="nota2" step="0.01" min="0" max="10" value="<?= $est['Nota2'] ?>"></td>
                            <td><input type="number" name="supletorio" step="0.01" min="0" max="10" value="<?= $est['Supletorio'] ?>"></td>
                            <td><?= is_null($prom) ? '-' : number_format($prom, 2) ?></td>
                            <td><?= is_null($final) ? '-' : number_format($final, 2) ?></td>
                            <td>
                                <?php if ($estado === 'Aprobado'): ?>
                                    <span class="badge bg-success">Aprobado</span>
                                <?php elseif ($estado === 'Reprobado'): ?>
                                    <span class="badge bg-danger">Reprobado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><button type="submit" name="guardar" class="btn btn-sm btn-primary">Guardar</button></td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif (isset($_GET['materia'])): ?>
        <div class="alert alert-info">No hay estudiantes matriculados en esta materia.</div>
    <?php endif; ?>
</div>
</body>
</html>
