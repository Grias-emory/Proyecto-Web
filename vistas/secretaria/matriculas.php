<?php
require '../../config/conexion.php';
require '../../config/auth.php';

verificarRol('SECRETARIA');

$cedulaSesion = $_SESSION['usuario']['cedula'];

// Mensajes
$mensaje = '';
$tipoMensaje = '';

if (isset($_GET['error'])) {
    $mensaje = $_GET['error'];
    $tipoMensaje = 'danger';
} elseif (isset($_GET['success'])) {
    $mensaje = $_GET['success'];
    $tipoMensaje = 'success';
}

// Edición
$editar = false;
$matriculaEditar = null;

if (isset($_GET['editar'])) {
    $editar = true;
    $idMatricula = $_GET['editar'];
    
    $stmt = $conexion->prepare("SELECT * FROM Matriculas WHERE IdMatricula = ? AND CedulaSecretaria = ?");
    $stmt->execute([$idMatricula, $cedulaSesion]);
    $matriculaEditar = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificamos que la matrícula le pertenezca a la secretaria actual
    if (!$matriculaEditar) {
        header("Location: matriculas.php?error=No puedes editar esta matrícula");
        exit;
    }
}

// Filtro
$filtro = $_GET['filtro'] ?? '';

$sql = "SELECT m.IdMatricula, m.CedulaEstudiante, m.CedulaSecretaria, m.IdMateria, m.RepiteMateria,
               e.PrimerNombre AS NombreEstudiante, e.PrimerApellido AS ApellidoEstudiante,
               s.PrimerNombre AS NombreSecretaria, s.PrimerApellido AS ApellidoSecretaria,
               mat.NombreMateria
        FROM Matriculas m
        JOIN Usuarios e ON m.CedulaEstudiante = e.Cedula
        JOIN Usuarios s ON m.CedulaSecretaria = s.Cedula
        JOIN Materias mat ON m.IdMateria = mat.IdMateria
        WHERE m.CedulaSecretaria = :cedulaSesion 
          AND (m.CedulaEstudiante LIKE :filtro OR m.CedulaSecretaria LIKE :filtro)
        ORDER BY m.IdMatricula DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    'cedulaSesion' => $cedulaSesion,
    'filtro' => "%$filtro%"
]);
$matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Materias
$materiasStmt = $conexion->query("SELECT IdMateria, NombreMateria FROM Materias");
$materias = $materiasStmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre materia si está en edición
$nombreMateriaEditar = '';
if ($editar && $matriculaEditar) {
    $stmtNombreMateria = $conexion->prepare("SELECT NombreMateria FROM Materias WHERE IdMateria = ?");
    $stmtNombreMateria->execute([$matriculaEditar['IdMateria']]);
    $materiaInfo = $stmtNombreMateria->fetch(PDO::FETCH_ASSOC);
    if ($materiaInfo) {
        $nombreMateriaEditar = $materiaInfo['NombreMateria'];
    }
}
?>

<!-- Aquí empieza el HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Matrículas</title>
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
    <h2 class="mb-4">Gestión de Matrículas</h2>
    <a href="../../dashboard.php" class="btn btn-rojo mb-3">← Volver al Dashboard</a>

    <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form action="procesar_matricula.php" method="POST" class="border p-4 rounded shadow-sm bg-light mb-4">
        <input type="hidden" name="modo" value="<?= $editar ? 'editar' : 'crear' ?>">
        <?php if ($editar): ?>
            <input type="hidden" name="id_matricula" value="<?= $matriculaEditar['IdMatricula'] ?>">
        <?php endif; ?>

        <div class="row g-3">
            <!-- Estudiante -->
            <div class="col-md-3">
                <label for="cedula_estudiante">Cédula Estudiante</label>
                <div class="input-group">
                    <input type="text" name="cedula_estudiante" id="cedula_estudiante" class="form-control" required
                           value="<?= $editar ? $matriculaEditar['CedulaEstudiante'] : '' ?>" readonly>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalEstudiantes">Buscar</button>
                </div>
            </div>

            <!-- Secretaria (solo su propia cédula) -->
            <div class="col-md-3">
                <label for="cedula_secretaria">Cédula Secretaria</label>
                <input type="text" name="cedula_secretaria" id="cedula_secretaria" class="form-control" value="<?= $cedulaSesion ?>" readonly>
            </div>

            <!-- Materia -->
            <div class="col-md-3">
                <label for="id_materia">Materia</label>
                <div class="input-group">
                    <select name="id_materia" id="id_materia" class="form-select" required>
                        <option value="">Seleccione una materia</option>
                        <?php foreach ($materias as $materia): ?>
                            <option value="<?= $materia['IdMateria'] ?>" <?= ($editar && $matriculaEditar['IdMateria'] == $materia['IdMateria']) ? 'selected' : '' ?>>
                                <?= $materia['NombreMateria'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalMaterias">Buscar</button>
                </div>
                <small class="form-text text-muted"><?= $nombreMateriaEditar ?></small>
            </div>

            <!-- Repite -->
            <div class="col-md-3">
                <label for="repite_materia">Repite Materia</label>
                <select name="repite_materia" id="repite_materia" class="form-select" required>
                    <option value="1" <?= ($editar && $matriculaEditar['RepiteMateria'] == 1) ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= ($editar && $matriculaEditar['RepiteMateria'] == 2) ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= ($editar && $matriculaEditar['RepiteMateria'] == 3) ? 'selected' : '' ?>>3</option>
                </select>
            </div>
            <div class="col-md-12 text-end">
                <button class="btn btn-rojo mt-3" type="submit">
                    <?= $editar ? 'Actualizar Matrícula' : 'Guardar Matrícula' ?>
                </button>
                <?php if ($editar): ?>
                    <a href="matriculas.php" class="btn btn-secondary mt-3">Cancelar</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Buscador -->
    <form method="GET" class="mb-3">
        <input type="text" name="filtro" class="form-control" placeholder="Buscar por cédula de estudiante o secretaria" value="<?= htmlspecialchars($filtro) ?>">
    </form>

    <!-- Tabla de Matrículas -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover shadow-sm">
            <thead>
                <tr>
                    <th>ID Matrícula</th>
                    <th>Estudiante</th>
                    <th>Secretaria</th>
                    <th>Materia</th>
                    <th>Repite</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matriculas as $m): ?>
                    <tr>
                        <td><?= $m['IdMatricula'] ?></td>
                        <td><?= $m['NombreEstudiante'] . ' ' . $m['ApellidoEstudiante'] ?></td>
                        <td><?= $m['NombreSecretaria'] . ' ' . $m['ApellidoSecretaria'] ?></td>
                        <td><?= $m['NombreMateria'] ?></td>
                        <td><?= $m['RepiteMateria'] ?></td>
                        <td>
                            <a href="matriculas.php?editar=<?= $m['IdMatricula'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_matricula.php?id=<?= $m['IdMatricula'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta matrícula?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modales -->
<?php include 'modal_estudiantes.php'; ?>
<?php include 'modal_materias.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
