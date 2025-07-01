<?php
session_start();
$id_medico = $_SESSION['id_medico'] ?? 0;
$id_rol = $_SESSION['id_rol'] ?? 0;

// Conexión a la base de datos
$host = 'localhost'; // Cambia esto si tu base de datos está en otro servidor
$port = '5432';      // Puerto predeterminado de PostgreSQL
$dbname = 'bd_asic'; // Nombre de tu base de datos
$user = 'postgres';  // Usuario de la base de datos
$password = '30429913'; // Contraseña del usuario

// Conexión a la base de datos
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Verifica la conexión
if (!$conn) {
    error_log('Error de conexión a la base de datos: ' . pg_last_error());
    die();
}

// Obtener filtros desde GET
$filtro_estado = $_GET['estado'] ?? 'no_atendidas'; // no_atendidas, atendidas, todas
$filtro_especialidad = $_GET['especialidad'] ?? '';

// Obtener especialidades para el filtro
$especialidades = [];
$resEsp = pg_query($conn, "SELECT id_especialidad, nombre_especialidad FROM especialidad ORDER BY nombre_especialidad ASC");
if ($resEsp) {
    while ($row = pg_fetch_assoc($resEsp)) {
        $especialidades[] = $row;
    }
}

// Construir la consulta según los filtros
$where = [];
// Solo filtrar por médico si el rol es médico
if ($id_rol == 2 && $id_medico > 0) {
    $where[] = "c.id_medico = $id_medico";
}

if ($filtro_estado === 'no_atendidas') {
    $where[] = "NOT EXISTS (SELECT 1 FROM historial_consultas h WHERE h.id_consulta = c.id_consulta)";
} elseif ($filtro_estado === 'atendidas') {
    $where[] = "EXISTS (SELECT 1 FROM historial_consultas h WHERE h.id_consulta = c.id_consulta)";
}
if ($filtro_especialidad !== '') {
    $where[] = "c.id_especialidad = '" . pg_escape_string($filtro_especialidad) . "'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT c.id_consulta, c.cedula, 
                 CONCAT(p.pr_nombre, ' ', p.sdo_nombre, ' ', p.pr_apellido, ' ', p.sdo_apellido) AS nombre_paciente, 
                 CONCAT(m.pr_nombre, ' ', m.sdo_nombre, ' ', m.pr_apellido, ' ', m.sdo_apellido) AS nombre_medico, 
                 e.nombre_especialidad, 
                 c.fecha_consulta, 
                 c.motivo, 
                 CASE WHEN c.primera_vez THEN true ELSE false END AS primera_vez
          FROM consulta c
          JOIN paciente p ON c.cedula = p.cedula
          JOIN medico m ON c.id_medico = m.id_medico
          JOIN especialidad e ON c.id_especialidad = e.id_especialidad
          $where_sql
          ORDER BY c.fecha_consulta DESC";
$result = pg_query($conn, $query);

if (!$result) {
    die('Error al ejecutar la consulta: ' . pg_last_error());
}

$consultas = pg_fetch_all($result);
?>

<?php if ($id_rol == 1 || $id_rol == 3): ?>
<!-- Filtros solo para administrador y personal administrativo -->
<div class="mb-3 d-flex flex-wrap align-items-center gap-2">
    <label class="mr-2 font-weight-bold">Filtrar por:</label>
    <select id="filtroEstado" class="form-control form-control-sm mr-2" style="width:auto;display:inline-block;">
        <option value="no_atendidas" <?= $filtro_estado === 'no_atendidas' ? 'selected' : '' ?>>No atendidas</option>
        <option value="atendidas" <?= $filtro_estado === 'atendidas' ? 'selected' : '' ?>>Atendidas</option>
        <option value="todas" <?= $filtro_estado === 'todas' ? 'selected' : '' ?>>Todas</option>
    </select>
    <select id="filtroEspecialidad" class="form-control form-control-sm" style="width:auto;display:inline-block;">
        <option value="">Todas las especialidades</option>
        <?php foreach ($especialidades as $esp): ?>
            <option value="<?= htmlspecialchars($esp['id_especialidad']) ?>" <?= $filtro_especialidad == $esp['id_especialidad'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($esp['nombre_especialidad']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<script>
document.getElementById('filtroEstado').addEventListener('change', function() {
    aplicarFiltros();
});
document.getElementById('filtroEspecialidad').addEventListener('change', function() {
    aplicarFiltros();
});
function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const especialidad = document.getElementById('filtroEspecialidad').value;
    let url = new URL(window.location.href);
    url.searchParams.set('estado', estado);
    url.searchParams.set('especialidad', especialidad);
    window.location.href = url.toString();
}
</script>
<?php endif; ?>

<!--Modulo lista de consultas-->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gestión de Consultas</h1>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Consultas</h6>
            <!-- Botón para agregar consulta -->
            <button class="btn btn-primary btn-circle btn-sm" title="Nueva Consulta" onclick="verificarPaciente()">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre del Paciente</th>
                            <th>Nombre del Médico</th>
                            <th>Especialidad</th>
                            <th>Fecha Consulta</th>
                            <th>Motivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($consultas): ?>
                            <?php foreach ($consultas as $consulta): ?>
                                <tr>
                                    <td><?= htmlspecialchars($consulta['cedula']) ?></td>
                                    <td><?= htmlspecialchars($consulta['nombre_paciente']) ?></td>
                                    <td><?= htmlspecialchars($consulta['nombre_medico']) ?></td>
                                    <td><?= htmlspecialchars($consulta['nombre_especialidad']) ?></td>
                                    <td><?= htmlspecialchars($consulta['fecha_consulta']) ?></td>
                                    <td><?= htmlspecialchars($consulta['motivo']) ?></td>
                                    <td>
                                        <button class="btn btn-info btn-circle btn-sm" title="Ver Detalles" onclick="verDetalles(<?= htmlspecialchars($consulta['cedula']) ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-success btn-circle btn-sm" title="Atender Consulta" onclick="atenderConsulta('<?= htmlspecialchars($consulta['id_consulta']) ?>','<?= htmlspecialchars($consulta['cedula']) ?>', '<?= htmlspecialchars($consulta['fecha_consulta']) ?>', '<?= htmlspecialchars($consulta['motivo']) ?>', '<?= htmlspecialchars($consulta['nombre_paciente']) ?>', '<?= htmlspecialchars($consulta['nombre_medico']) ?>')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-circle btn-sm" title="Eliminar" onclick="eliminarConsulta(<?= htmlspecialchars($consulta['cedula']) ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay consultas registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles de la consulta -->
<div class="modal fade" id="modalDetallesConsulta" tabindex="-1" role="dialog" aria-labelledby="modalDetallesConsultaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetallesConsultaLabel">Detalles de la Consulta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detallesConsultaContent">
                <!-- Aquí se cargarán los detalles dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function verDetalles(cedula) {
        fetch(`/sisgeasic/backend/consulta/detalles_consulta.php?cedula=${cedula}`)
            .then(response => response.json())
            .then(data => {
                let content = `
                    <p><strong>Cédula:</strong> ${data.cedula ?? 'N/A'}</p>
                    <p><strong>Nombre del Paciente:</strong> ${data.nombre_paciente ?? 'N/A'}</p>
                    <p><strong>Nombre del Médico:</strong> ${data.nombre_medico ?? 'N/A'}</p>
                    <p><strong>Especialidad:</strong> ${data.nombre_especialidad ?? 'N/A'}</p>
                    <p><strong>Fecha de Consulta:</strong> ${data.fecha_consulta ?? 'N/A'}</p>
                    <p><strong>Motivo:</strong> ${data.motivo ?? 'N/A'}</p>
                    <p><strong>Primera Vez:</strong> ${(data.primera_vez === true || data.primera_vez === 't' || data.primera_vez === 1) ? 'Sí' : 'No'}</p>
                `;

                // Mostrar historial solo si no es primera vez y existen datos
                if (
                    (data.primera_vez === false || data.primera_vez === 'f' || data.primera_vez === 0) &&
                    (data.observaciones || data.diagnostico || data.tratamiento)
                ) {
                    content += `
                        <hr>
                        <h6>Consulta Anterior</h6>
                        <p><strong>Fecha:</strong> ${data.consulta_anterior ? data.consulta_anterior : 'N/A'}</p>
                        <p><strong>Observaciones:</strong> ${data.observaciones ? data.observaciones : 'N/A'}</p>
                        <p><strong>Diagnóstico:</strong> ${data.diagnostico ? data.diagnostico : 'N/A'}</p>
                        <p><strong>Tratamiento:</strong> ${data.tratamiento ? data.tratamiento : 'N/A'}</p>
                    `;
                }

                document.getElementById('detallesConsultaContent').innerHTML = content;
                $('#modalDetallesConsulta').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los detalles de la consulta.');
            });
    }

    function eliminarConsulta(cedula) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará la consulta de forma permanente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/sisgeasic/backend/consulta/eliminar_consulta.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ cedula })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'La consulta fue eliminada correctamente.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.error || 'Error al eliminar la consulta.',
                            icon: 'error',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al eliminar la consulta.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                });
            }
        });
    }

    function verificarPaciente() {
        Swal.fire({
            title: 'Verificar Paciente',
            input: 'text',
            inputLabel: 'Ingrese la cédula del paciente',
            inputPlaceholder: 'Cédula del paciente',
            showCancelButton: true,
            confirmButtonText: 'Verificar',
            cancelButtonText: 'Cancelar',
            preConfirm: (cedula) => {
                if (!cedula) {
                    Swal.showValidationMessage('Debe ingresar una cédula');
                }
                return fetch(`/sisgeasic/backend/consulta/verificar_paciente.php?cedula=${cedula}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.existe) {
                            return null; // Indicar que el paciente no existe
                        }
                        return cedula; // Retornar la cédula si el paciente existe
                    })
                    .catch(() => {
                        Swal.showValidationMessage('Error al verificar el paciente');
                    });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    title: 'Paciente encontrado',
                    text: 'El paciente existe en la base de datos. Proceda a llenar los campos de la consulta.',
                    icon: 'success',
                    confirmButtonText: 'Continuar'
                }).then(() => {
                    mostrarFormularioConsulta(result.value);
                });
            } else if (result.isConfirmed && result.value === null) {
                Swal.fire({
                    title: 'Cédula no registrada',
                    text: 'Esta cédula no está registrada en el sistema.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    Swal.fire({
                        title: 'Redirigiendo para agregar al nuevo paciente...',
                        html: '<div class="swal2-spinner"></div>', // Cambiar a una animación estándar de SweetAlert2
                        text: 'Será redirigido al módulo de agregar paciente.',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 3000,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        willClose: () => {
                            window.location.href = 'dashboard.php?modulo=agregar_paciente';
                            }
                        });
                    });
            }
        });
    }

    function mostrarFormularioConsulta(cedula) {
        // Consultar historial antes de mostrar el formulario
        fetch(`/sisgeasic/backend/consulta/detalles_consulta.php?cedula=${cedula}`)
            .then(response => response.json())
            .then(data => {
                let historialHtml = '';
                if (
                    (data.primera_vez === false || data.primera_vez === 'f' || data.primera_vez === 0) &&
                    (data.observaciones || data.diagnostico || data.tratamiento)
                ) {
                    historialHtml = `
                        <div class="alert alert-info">
                            <h6>Última Consulta Anterior</h6>
                            <p><strong>Fecha:</strong> ${data.consulta_anterior ? data.consulta_anterior : 'N/A'}</p>
                            <p><strong>Observaciones:</strong> ${data.observaciones ? data.observaciones : 'N/A'}</p>
                            <p><strong>Diagnóstico:</strong> ${data.diagnostico ? data.diagnostico : 'N/A'}</p>
                            <p><strong>Tratamiento:</strong> ${data.tratamiento ? data.tratamiento : 'N/A'}</p>
                        </div>
                    `;
                }
                Swal.fire({
                    title: 'Formulario de Consulta',
                    html: `
                        ${historialHtml}
                        <form id="formAgregarConsulta">
                            <input type="hidden" id="cedulaPaciente" name="cedula" value="${cedula}">
                            <div class="form-group">
                                <label for="motivoConsulta">Motivo de la Consulta</label>
                                <textarea class="form-control" id="motivoConsulta" name="motivo" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="especialidadConsulta">Especialidad</label>
                                <select class="form-control" id="especialidadConsulta" name="id_especialidad" required>
                                    <option value="">Seleccione una especialidad</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="medicoConsulta">Médico</label>
                                <select class="form-control" id="medicoConsulta" name="id_medico" required>
                                    <option value="">Seleccione un médico</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="fechaHoraConsulta">Fecha y Hora</label>
                                <input type="datetime-local" class="form-control" id="fechaHoraConsulta" name="fecha_consulta" required>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    didOpen: () => {
                        // Cargar especialidades y médicos
                        fetch('/sisgeasic/backend/consulta/get_especialidades.php')
                            .then(res => res.json())
                            .then(data => {
                                const select = document.getElementById('especialidadConsulta');
                                data.forEach(esp => {
                                    const opt = document.createElement('option');
                                    opt.value = esp.id_especialidad;
                                    opt.textContent = esp.nombre_especialidad;
                                    select.appendChild(opt);
                                });
                            });

                        document.getElementById('especialidadConsulta').addEventListener('change', function() {
                            const idEspecialidad = this.value;
                            const medicoSelect = document.getElementById('medicoConsulta');
                            medicoSelect.innerHTML = '<option value="">Seleccione un médico</option>';
                            if (idEspecialidad) {
                                fetch(`/sisgeasic/backend/consulta/get_medicos_por_especialidad.php?id_especialidad=${idEspecialidad}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        data.forEach(med => {
                                            const opt = document.createElement('option');
                                            opt.value = med.id_medico;
                                            opt.textContent = `${med.pr_nombre} ${med.sdo_nombre} ${med.pr_apellido} ${med.sdo_apellido}`;
                                            medicoSelect.appendChild(opt);
                                        });
                                    });
                            }
                        });
                    },
                    preConfirm: () => {
                        guardarConsulta();
                    }
                });
            });
    }

    function guardarConsulta() {
        const formData = new FormData(document.getElementById('formAgregarConsulta'));

        fetch('/sisgeasic/backend/consulta/guardar_consulta.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Consulta guardada',
                    text: 'La consulta se guardó correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al guardar la consulta.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al guardar la consulta.',
                icon: 'error',
                confirmButtonText: 'Cerrar'
            });
        });
    }

    function atenderConsulta(id_consulta, cedula, fecha_consulta, motivo, nombre_paciente, nombre_medico) {
        // Cargar enfermedades
        fetch('/sisgeasic/backend/consulta/get_enfermedades.php')
            .then(res => res.json())
            .then(enfermedades => {
                let enfermedadOptions = '<option value="">(Opcional) Seleccione una enfermedad</option>';
                enfermedades.forEach(e => {
                    enfermedadOptions += `<option value="${e.id_enfermedad}">${e.nombre}</option>`;
                });

                Swal.fire({
                    title: 'Atender Consulta',
                    html: `
                        <form id="formAtenderConsulta">
                            <input type="hidden" name="id_consulta" value="${id_consulta}">
                            <input type="hidden" name="cedula" value="${cedula}">
                            <input type="hidden" name="fecha_consulta" value="${fecha_consulta}">
                            <div class="form-group">
                                <label>Paciente</label>
                                <input type="text" class="form-control" value="${nombre_paciente}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Médico</label>
                                <input type="text" class="form-control" value="${nombre_medico}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Motivo</label>
                                <textarea class="form-control" readonly>${motivo}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Observaciones</label>
                                <textarea class="form-control" name="observaciones" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Diagnóstico</label>
                                <textarea class="form-control" name="diagnostico" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Tratamiento</label>
                                <textarea class="form-control" name="tratamiento" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Enfermedad (opcional)</label>
                                <select class="form-control" name="id_enfermedad">
                                    ${enfermedadOptions}
                                </select>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const form = document.getElementById('formAtenderConsulta');
                        const formData = new FormData(form);
                        return fetch('/sisgeasic/backend/consulta/guardar_historial_consulta.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) {
                                Swal.showValidationMessage(data.error || 'Error al guardar el historial');
                            }
                            return data;
                        })
                        .catch(() => {
                            Swal.showValidationMessage('Error al guardar el historial');
                        });
                    }
                }).then(result => {
                    if (result.isConfirmed && result.value && result.value.success) {
                        Swal.fire({
                            title: 'Atención registrada',
                            text: 'La consulta fue atendida y registrada en el historial.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });
    }
</script>