<?php
// Obtener materias desde la BD
$materiasModalStmt = $conexion->prepare("SELECT m.IdMateria, m.NombreMateria, m.IdSemestre, s.NombreSemestre 
                                         FROM Materias m 
                                         LEFT JOIN Semestres s ON m.IdSemestre = s.IdSemestre
                                         ORDER BY m.IdSemestre, m.NombreMateria");
$materiasModalStmt->execute();
$materiasModal = $materiasModalStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal para Materias -->
<div class="modal fade" id="modalMaterias" tabindex="-1" aria-labelledby="modalMateriasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalMateriasLabel">Seleccionar Materia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="busquedaMateria" class="form-control mb-3" placeholder="Buscar por nombre de materia o semestre...">
                
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaMaterias">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Materia</th>
                                <th>Semestre</th>
                                <th>Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materiasModal as $m): ?>
                                <tr data-semestre="<?= $m['IdSemestre'] ?>" data-nombre="<?= strtolower($m['NombreMateria']) ?>" data-nombresemestre="<?= strtolower($m['NombreSemestre'] ?? '') ?>">
                                    <td><?= $m['IdMateria'] ?></td>
                                    <td><?= $m['NombreMateria'] ?></td>
                                    <td><?= $m['IdSemestre'] ?> - <?= $m['NombreSemestre'] ?? 'No asignado' ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" onclick="seleccionarMateria('<?= $m['IdMateria'] ?>', '<?= htmlspecialchars($m['NombreMateria'], ENT_QUOTES) ?>')">Seleccionar</button>
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
function seleccionarMateria(id, nombre) {
    document.getElementById("id_materia").value = id;
    // Si tienes un elemento para mostrar el nombre seleccionado:
    if (document.getElementById("nombre_materia_seleccionada")) {
        document.getElementById("nombre_materia_seleccionada").textContent = nombre;
    }
    var modal = bootstrap.Modal.getInstance(document.getElementById("modalMaterias"));
    modal.hide();
}

document.getElementById("busquedaMateria").addEventListener("input", function () {
    let filtro = this.value.toLowerCase();
    let filas = document.querySelectorAll("#tablaMaterias tbody tr");
    
    filas.forEach(fila => {
        // Buscar por nombre de materia o nombre/id de semestre
        let nombreMateria = fila.getAttribute("data-nombre");
        let idSemestre = fila.getAttribute("data-semestre");
        let nombreSemestre = fila.getAttribute("data-nombresemestre");
        
        if (nombreMateria.includes(filtro) || 
            idSemestre.includes(filtro) || 
            nombreSemestre.includes(filtro)) {
            fila.style.display = "";
        } else {
            fila.style.display = "none";
        }
    });
});
</script>