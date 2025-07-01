<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$cedula = $_GET['cedula'] ?? '';
$paciente = null;
$estados = [];
$municipios = [];
$parroquias = [];
include_once __DIR__ . '/../backend/conexion.php';

// Cargar catálogos SIEMPRE, usando los nombres correctos de columnas según tus capturas
$res = pg_query($conn, "SELECT id_estado, nombre FROM estado ORDER BY nombre");
if ($res) {
    while ($row = pg_fetch_assoc($res)) $estados[] = $row;
}
$res = pg_query($conn, "SELECT id_municipio, nombre, estado_id FROM municipio ORDER BY nombre");
if ($res) {
    while ($row = pg_fetch_assoc($res)) $municipios[] = $row;
}
$res = pg_query($conn, "SELECT id_parroquia, nombre, municipio_id FROM parroquia ORDER BY nombre");
if ($res) {
    while ($row = pg_fetch_assoc($res)) $parroquias[] = $row;
}

if ($cedula) {
    // Consulta al backend para obtener los datos del paciente
    $url = "http://localhost/sisgeasic/backend/paciente/ver.php?cedula=" . urlencode($cedula);
    $json = @file_get_contents($url);
    if ($json) {
        $resp = json_decode($json, true);
        if ($resp && isset($resp['paciente'])) {
            $paciente = $resp['paciente'];
        }
    }

    // Obtener historial de citas
    $historial_citas = [];
    if ($cedula) {
        $url_citas = "http://localhost/sisgeasic/backend/paciente/citas.php?cedula=" . urlencode($cedula);
        $json_citas = @file_get_contents($url_citas);
        if ($json_citas) {
            $resp_citas = json_decode($json_citas, true);
            if ($resp_citas && isset($resp_citas['citas'])) {
                $historial_citas = $resp_citas['citas'];
            }
        }
    }

    // Obtener historial de consultas
    $historial_consultas = [];
    if ($cedula) {
        $url_consultas = "http://localhost/sisgeasic/backend/paciente/consultas.php?cedula=" . urlencode($cedula);
        $json_consultas = @file_get_contents($url_consultas);
        if ($json_consultas) {
            $resp_consultas = json_decode($json_consultas, true);
            // Debug temporal para ver la respuesta del backend
            // echo '<pre>'; print_r($resp_consultas); echo '</pre>';
            if (
                isset($resp_consultas['consultas']) &&
                is_array($resp_consultas['consultas']) &&
                count($resp_consultas['consultas']) > 0
            ) {
                $historial_consultas = $resp_consultas['consultas'];
            }
        }
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix@3.2.7/dist/notiflix-3.2.7.min.css" />
<style>
.seccion-titulo {
    font-size: 1.15rem;
    font-weight: 600;
    color: #5f61e6;
    margin-bottom: 18px;
    margin-top: 32px;
    letter-spacing: 0.03em;
}
.form-label {
    font-weight: 500;
    color: #6c757d;
}
.form-control-plaintext {
    background: #f8f9fa;
    border-radius: 6px;
    padding-left: 12px;
    font-size: 1.05rem;
    color: #222;
}
hr {
    margin: 32px 0 24px 0;
}
.editar-btn {
    position: absolute;
    top: 24px;
    right: 32px;
    z-index: 10;
    background: #f8f9fa;
    border: none;
    color: #4e73df;
    font-size: 1.5rem;
    border-radius: 50%;
    padding: 8px 12px;
    transition: background 0.2s, color 0.2s;
}
.editar-btn:hover {
    background: #4e73df;
    color: #fff;
}
@media (max-width: 900px) {
    .container-xl {
        padding: 0 8px;
    }
}
</style>
<div class="container-xl" style="max-width: 950px;">
    <div class="card shadow-sm mt-4 mb-5 position-relative">
        <?php if ($paciente): ?>
            <button class="editar-btn" title="Editar paciente" onclick="editarPaciente(event)">
                <i class="fas fa-edit"></i>
            </button>
        <?php endif; ?>
        <div class="card-body">
            <h3 class="mb-4" style="font-weight:700;color:#4e73df;">Ficha de Paciente</h3>
            <?php if ($paciente): ?>
            <form id="formPaciente" autocomplete="off">
                <input type="hidden" name="cedula" value="<?= htmlspecialchars($paciente['cedula']) ?>">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cédula</label>
                        <input type="text" name="cedula_mostrar" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['cedula']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="text" name="correo" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['correo'] ?? '') ?>">
                    </div>
                </div>
                <div class="seccion-titulo">1. Información personal</div>
                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Primer Nombre</label>
                        <input type="text" name="pr_nombre" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['pr_nombre']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Segundo Nombre</label>
                        <input type="text" name="sdo_nombre" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['sdo_nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Género</label>
                        <input type="text" name="genero" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['genero'] ?? '') ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Primer Apellido</label>
                        <input type="text" name="pr_apellido" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['pr_apellido']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Segundo Apellido</label>
                        <input type="text" name="sdo_apellido" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['sdo_apellido'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['fecha_nacimiento']) ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Edad</label>
                        <input type="text" readonly class="form-control-plaintext" value="<?php
                            if ($paciente['fecha_nacimiento']) {
                                $fn = new DateTime($paciente['fecha_nacimiento']);
                                $hoy = new DateTime();
                                $edad = $hoy->diff($fn)->y;
                                echo $edad;
                            }
                        ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['direccion'] ?? '') ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estado</label>
                        <select name="id_estado" id="id_estado" class="form-control" disabled>
                            <option value="">Seleccione...</option>
                            <?php foreach ($estados as $e): ?>
                                <option value="<?= $e['id_estado'] ?>" <?= ($paciente['id_estado'] == $e['id_estado']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Municipio</label>
                        <select name="id_municipio" id="id_municipio" class="form-control" disabled>
                            <option value="">Seleccione...</option>
                            <?php foreach ($municipios as $m): ?>
                                <option value="<?= $m['id_municipio'] ?>" data-estado="<?= $m['estado_id'] ?>" <?= ($paciente['id_municipio'] == $m['id_municipio']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Parroquia</label>
                        <select name="id_parroquia" id="id_parroquia" class="form-control" disabled>
                            <option value="">Seleccione...</option>
                            <?php foreach ($parroquias as $p): ?>
                                <option value="<?= $p['id_parroquia'] ?>" data-municipio="<?= $p['municipio_id'] ?>" <?= ($paciente['id_parroquia'] == $p['id_parroquia']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Peso</label>
                        <input type="text" name="peso" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['peso'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Talla</label>
                        <input type="text" name="talla" readonly class="form-control-plaintext" value="<?= htmlspecialchars($paciente['talla'] ?? '') ?>">
                    </div>
                </div>
            </form>

            <!-- Apartado: Historial de Citas -->
            <hr>
            <div class="seccion-titulo">2. Historial de Citas</div>
            <?php if (!empty($historial_citas)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Médico</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($historial_citas as $cita): ?>
                            <tr>
                                <td><?= htmlspecialchars($cita['fecha'] ?? '') ?></td>
                                <td><?= htmlspecialchars($cita['hora'] ?? '') ?></td>
                                <td><?= htmlspecialchars($cita['medico'] ?? '') ?></td>
                                <td><?= htmlspecialchars($cita['especialidad'] ?? '') ?></td>
                                <td><?= htmlspecialchars($cita['estado'] ?? '') ?></td>
                                <td><?= htmlspecialchars($cita['tipo'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted mb-3">No hay citas registradas.</div>
            <?php endif; ?>

            <!-- Apartado: Historial de Consultas -->
            <hr>
            <div class="seccion-titulo">3. Historial de Consultas</div>
            <?php if (!empty($historial_consultas)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Médico</th>
                                <th>Motivo</th>
                                <th>Diagnóstico</th>
                                <th>Tratamiento</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($historial_consultas as $consulta): ?>
                            <tr>
                                <td><?= htmlspecialchars($consulta['fecha'] ?? '') ?></td>
                                <td><?= htmlspecialchars($consulta['medico'] ?? '') ?></td>
                                <td><?= htmlspecialchars($consulta['motivo'] ?? '') ?></td>
                                <td><?= htmlspecialchars($consulta['diagnostico'] ?? '') ?></td>
                                <td><?= htmlspecialchars($consulta['tratamiento'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted mb-3">No hay consultas registradas.</div>
            <?php endif; ?>

            <!-- ...botón volver... -->
            <div class="d-flex justify-content-end mt-4">
                <a href="dashboard.php?modulo=pacientes" class="btn btn-primary px-4">Volver a la lista</a>
            </div>
            <?php else: ?>
                <div class="alert alert-danger">Paciente no encontrado.</div>
                <a href="dashboard.php?modulo=pacientes" class="btn btn-primary mt-3">Volver a la lista</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.7/dist/notiflix-aio-3.2.7.min.js"></script>
<script>
function editarPaciente(e) {
    e.preventDefault();
    // Habilita los campos para edición
    document.querySelectorAll('#formPaciente input').forEach(function(input) {
        if (input.type !== 'hidden') input.removeAttribute('readonly');
    });
    document.querySelectorAll('#formPaciente select').forEach(function(sel) {
        sel.removeAttribute('disabled');
    });
    // Cambia el botón editar por un botón guardar/cancelar
    let btnEditar = document.querySelector('.editar-btn');
    if (btnEditar) btnEditar.style.display = 'none';

    // Agrega botones de guardar y cancelar
    let acciones = document.createElement('div');
    acciones.className = 'd-flex justify-content-end gap-2 mt-2';
    acciones.id = 'acciones-edicion';
    acciones.innerHTML = `
        <button type="button" class="btn btn-success" onclick="guardarEdicion()">Guardar</button>
        <button type="button" class="btn btn-secondary" onclick="cancelarEdicion()">Cancelar</button>
    `;
    document.querySelector('#formPaciente').appendChild(acciones);
}

function cancelarEdicion() {
    // Recarga la página para descartar cambios
    location.reload();
}

function guardarEdicion() {
    let form = document.getElementById('formPaciente');
    let formData = new FormData(form);
    formData.append('accion', 'editar');
    fetch('/sisgeasic/backend/paciente/editar.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.ok) {
            Notiflix.Notify.init({ timeout: 1500 });
            Notiflix.Notify.success('Datos actualizados correctamente');
            setTimeout(() => location.reload(), 1500);
        } else {
            Notiflix.Notify.failure(resp.error || 'Error al actualizar');
        }
    })
    .catch(() => Notiflix.Notify.failure('Error de conexión'));
}

// Filtrado dinámico de municipios y parroquias
document.getElementById('id_estado').addEventListener('change', function() {
    let estado = this.value;
    document.querySelectorAll('#id_municipio option').forEach(function(opt) {
        if (!opt.value) return;
        opt.style.display = (opt.getAttribute('data-estado') === estado) ? '' : 'none';
    });
    document.getElementById('id_municipio').value = '';
    document.getElementById('id_parroquia').value = '';
    document.getElementById('id_parroquia').dispatchEvent(new Event('change'));
});
// Al cargar la página, filtra municipios según el estado seleccionado
window.addEventListener('DOMContentLoaded', function() {
    let estado = document.getElementById('id_estado').value;
    document.querySelectorAll('#id_municipio option').forEach(function(opt) {
        if (!opt.value) return;
        opt.style.display = (opt.getAttribute('data-estado') === estado) ? '' : 'none';
    });
    let municipio = document.getElementById('id_municipio').value;
    document.querySelectorAll('#id_parroquia option').forEach(function(opt) {
        if (!opt.value) return;
        opt.style.display = (opt.getAttribute('data-municipio') === municipio) ? '' : 'none';
    });
});
document.getElementById('id_municipio').addEventListener('change', function() {
    let municipio = this.value;
    document.querySelectorAll('#id_parroquia option').forEach(function(opt) {
        if (!opt.value) return;
        opt.style.display = (opt.getAttribute('data-municipio') === municipio) ? '' : 'none';
    });
    document.getElementById('id_parroquia').value = '';
});
</script>
