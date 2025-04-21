<?php 
// Obtener estudiantes desde la BD
$estudiantesStmt = $conexion->prepare("SELECT Cedula, PrimerNombre, PrimerApellido FROM Usuarios WHERE Rol = 'ESTUDIANTE'");
$estudiantesStmt->execute();
$estudiantes = $estudiantesStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener secretarias y administradores
$secretariasStmt = $conexion->prepare("SELECT Cedula, PrimerNombre, PrimerApellido, Rol FROM Usuarios WHERE Rol IN ('SECRETARIA', 'ADMINISTRADOR')");
$secretariasStmt->execute();
$secretarias = $secretariasStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal para Estudiantes -->
<div class="modal fade" id="modalEstudiantes" tabindex="-1" aria-labelledby="modalEstudiantesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalEstudiantesLabel">Seleccionar Estudiante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="busquedaEstudiante" class="form-control mb-3" placeholder="Buscar por cédula o nombre...">
                
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaEstudiantes">
                        <thead class="table-dark">
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $e): ?>
                                <tr>
                                    <td><?= $e['Cedula'] ?></td>
                                    <td><?= $e['PrimerNombre'] . ' ' . $e['PrimerApellido'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" onclick="seleccionarEstudiante('<?= $e['Cedula'] ?>')">Seleccionar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Secretarias -->
<div class="modal fade" id="modalSecretarias" tabindex="-1" aria-labelledby="modalSecretariasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalSecretariasLabel">Seleccionar Secretaria o Administrador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="busquedaSecretaria" class="form-control mb-3" placeholder="Buscar por cédula o nombre...">
                
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaSecretarias">
                        <thead class="table-dark">
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($secretarias as $s): ?>
                                <tr>
                                    <td><?= $s['Cedula'] ?></td>
                                    <td><?= $s['PrimerNombre'] . ' ' . $s['PrimerApellido'] ?></td>
                                    <td><?= $s['Rol'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" onclick="seleccionarSecretaria('<?= $s['Cedula'] ?>')">Seleccionar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function seleccionarEstudiante(cedula) {
    document.getElementById("cedula_estudiante").value = cedula;
    var modal = bootstrap.Modal.getInstance(document.getElementById("modalEstudiantes"));
    modal.hide();
}

function seleccionarSecretaria(cedula) {
    document.getElementById("cedula_secretaria").value = cedula;
    var modal = bootstrap.Modal.getInstance(document.getElementById("modalSecretarias"));
    modal.hide();
}

document.getElementById("busquedaEstudiante").addEventListener("input", function () {
    let filtro = this.value.toLowerCase();
    let filas = document.querySelectorAll("#tablaEstudiantes tbody tr");
    
    filas.forEach(fila => {
        let texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? "" : "none";
    });
});

document.getElementById("busquedaSecretaria").addEventListener("input", function () {
    let filtro = this.value.toLowerCase();
    let filas = document.querySelectorAll("#tablaSecretarias tbody tr");
    
    filas.forEach(fila => {
        let texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? "" : "none";
    });
});
</script>