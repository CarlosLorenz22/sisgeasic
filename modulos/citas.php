<?php
// Conexión a PostgreSQL
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
$especialidades = [];
if ($conn) {
    $res = pg_query($conn, "SELECT id_especialidad, nombre_especialidad, color FROM especialidad");
    while ($row = pg_fetch_assoc($res)) {
        $especialidades[] = $row;
    }
    // Ordenar alfabéticamente por nombre_especialidad
    usort($especialidades, function($a, $b) {
        return strcasecmp($a['nombre_especialidad'], $b['nombre_especialidad']);
    });
    pg_free_result($res);
    pg_close($conn);
}
?>
<!-- Contenedor principal -->
<div class="container mx-auto p-4">
    <div class="mb-4 flex flex-wrap items-center gap-4">
        <div class="flex gap-2 items-center">
            <select id="filtro-especialidad" class="form-control" style="width:auto; min-width:180px;">
                <option value="">Todas las especialidades</option>
                <?php foreach ($especialidades as $esp): ?>
                    <option value="<?= htmlspecialchars($esp['id_especialidad']) ?>">
                        <?= htmlspecialchars($esp['nombre_especialidad']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Calendario de Citas</h1>
    <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 8px;">
        <button id="btn-leyenda" class="btn btn-primary" type="button">
            <i class="fas fa-info-circle"></i> Leyenda
        </button>
    </div>
    <div id="calendar" class="bg-white rounded-lg shadow-md p-4" style="position:relative;"></div>
</div>
<!-- <link rel="stylesheet" href="../assets/css/calendario_citas.css"> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let filtroEspecialidad = '';
    const calendarEl = document.getElementById('calendar');
    let calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today:    'Hoy',
            month:    'Mes',
            week:     'Semana',
            day:      'Día',
            list:     'Agenda'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            let url = '/sisgeasic/backend/citas/calendario_citas/calendario_backend.php';
            if (filtroEspecialidad) {
                url += '?especialidad=' + encodeURIComponent(filtroEspecialidad);
            }
            fetch(url)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            // Evita que el navegador siga el enlace si existe
            info.jsEvent.preventDefault();

            // Obtiene los datos del evento
            const event = info.event;
            const props = event.extendedProps || {};

            const detalles = `
                <div style="text-align:left;">
                    <b>Título:</b> ${event.title || '-'}<br>
                    <b>Paciente:</b> ${props.paciente || props.Paciente || '-'}<br>
                    <b>Médico:</b> ${props.medico || props.Medico || '-'}<br>
                    <b>Especialidad:</b> ${props.especialidad || props.Especialidad || '-'}<br>
                    <b>Fecha inicio:</b> ${event.start ? event.start.toLocaleString() : '-'}<br>
                    <b>Fecha fin:</b> ${event.end ? event.end.toLocaleString() : '-'}<br>
                    <b>Color:</b> <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${event.backgroundColor || event.color || '#ccc'};border:1px solid #aaa;"></span><br>
                    ${props.cedula ? `<b>Cédula:</b> ${props.cedula}<br>` : ''}
                    ${props.status ? `<b>Status:</b> ${props.status}<br>` : ''}
                    ${props.id_medico ? `<b>ID Médico:</b> ${props.id_medico}<br>` : ''}
                    ${props.id_especialidad ? `<b>ID Especialidad:</b> ${props.id_especialidad}<br>` : ''}
                </div>
            `;
            Swal.fire({
                title: 'Detalles de la Cita',
                html: detalles,
                icon: 'info',
                confirmButtonText: 'Cerrar'
            });
        },
        dateClick: async function(info) {
            // Modal para agregar cita
            const especialidades = <?php echo json_encode($especialidades); ?>;
            let especialidadOptions = '<option value="">Seleccione</option>';
            especialidades.forEach(e => {
                especialidadOptions += `<option value="${e.id_especialidad}">${e.nombre_especialidad}</option>`;
            });

            // Modal con campos y contenedores para nombre paciente y médicos (mejorados visualmente)
            const { value: formValues } = await Swal.fire({
                title: 'Agregar Cita',
                html:
                    `<div style="margin-bottom:12px;">
                        <input id="swal-titulo" class="swal2-input" style="font-size:16px;padding:10px;border-radius:7px;border:1px solid #bdbdbd;" placeholder="Descripción de la cita">
                    </div>
                    <div style="margin-bottom:2px;">
                        <input id="swal-cedula" class="swal2-input" style="font-size:16px;padding:10px;border-radius:7px;border:1px solid #bdbdbd;" placeholder="Cédula o nombre del paciente" autocomplete="off">
                    </div>
                    <div id="swal-nombre-paciente" style="margin-bottom:10px;font-size:15px;color:#333;text-align:left;font-weight:500;"></div>
                    <div style="margin-bottom:12px;">
                        <select id="swal-especialidad" class="swal2-input" style="font-size:16px;padding:10px;border-radius:7px;border:1px solid #bdbdbd;background:#f9f9f9;">${especialidadOptions}</select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <select id="swal-medico" class="swal2-input" style="font-size:16px;padding:10px;border-radius:7px;border:1px solid #bdbdbd;background:#f9f9f9;"><option value="">Seleccione un médico</option></select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <input id="swal-fecha-inicio" class="swal2-input" style="font-size:16px;padding:10px;border-radius:7px;border:1px solid #bdbdbd;" type="datetime-local" value="${info.dateStr}T08:00">
                    </div>
                    <div>
                        <input id="swal-fecha-fin" class="swal2-input" style="font-size:16px;padding:10px;border-radius:7px;border:1px solid #bdbdbd;" type="datetime-local" value="${info.dateStr}T09:00">
                    </div>`,
                didOpen: () => {
                    // Buscar paciente por cédula o nombre
                    const cedulaInput = document.getElementById('swal-cedula');
                    const nombrePacienteDiv = document.getElementById('swal-nombre-paciente');
                    let pacienteTimeout = null;
                    cedulaInput.addEventListener('input', function() {
                        clearTimeout(pacienteTimeout);
                        const valor = this.value.trim();
                        if (valor.length < 3) {
                            nombrePacienteDiv.textContent = '';
                            return;
                        }
                        pacienteTimeout = setTimeout(() => {
                            fetch('/sisgeasic/backend/citas/calendario_citas/buscar_paciente.php?q=' + encodeURIComponent(valor))
                                .then(res => res.json())
                                .then(data => {
                                    if (data && data.nombre) {
                                        nombrePacienteDiv.textContent = data.nombre;
                                        nombrePacienteDiv.style.color = '#228B22';
                                    } else {
                                        nombrePacienteDiv.textContent = 'Paciente no encontrado';
                                        nombrePacienteDiv.style.color = '#B22222';
                                    }
                                })
                                .catch(() => {
                                    nombrePacienteDiv.textContent = 'Error buscando paciente';
                                    nombrePacienteDiv.style.color = '#B22222';
                                });
                        }, 400);
                    });

                    // Cargar médicos según especialidad seleccionada
                    const especialidadSelect = document.getElementById('swal-especialidad');
                    const medicoSelect = document.getElementById('swal-medico');
                    especialidadSelect.addEventListener('change', function() {
                        const idEspecialidad = this.value;
                        medicoSelect.innerHTML = '<option value="">Cargando...</option>';
                        if (!idEspecialidad) {
                            medicoSelect.innerHTML = '<option value="">Seleccione un médico</option>';
                            return;
                        }
                        fetch('/sisgeasic/backend/citas/calendario_citas/medicos_por_especialidad.php?id_especialidad=' + encodeURIComponent(idEspecialidad))
                            .then(res => res.json())
                            .then(data => {
                                let options = '<option value="">Seleccione un médico</option>';
                                data.forEach(med => {
                                    options += `<option value="${med.id_medico}">${med.nombre}</option>`;
                                });
                                medicoSelect.innerHTML = options;
                            })
                            .catch(() => {
                                medicoSelect.innerHTML = '<option value="">Error cargando médicos</option>';
                            });
                    });
                },
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '<span style="font-size:16px;padding:4px 18px;">Guardar</span>',
                cancelButtonText: '<span style="font-size:16px;padding:4px 18px;">Cancelar</span>',
                preConfirm: () => {
                    return {
                        titulo: document.getElementById('swal-titulo').value,
                        cedula: document.getElementById('swal-cedula').value,
                        id_medico: document.getElementById('swal-medico').value,
                        id_especialidad: document.getElementById('swal-especialidad').value,
                        fecha_inicio: document.getElementById('swal-fecha-inicio').value,
                        fecha_fin: document.getElementById('swal-fecha-fin').value
                    }
                }
            });

            if (formValues) {
                // Validación simple
                if (!formValues.titulo || !formValues.cedula || !formValues.id_medico || !formValues.id_especialidad) {
                    Swal.fire('Campos requeridos', 'Por favor complete todos los campos.', 'warning');
                    return;
                }
                // Aquí puedes hacer la petición AJAX para guardar la cita
                fetch('/sisgeasic/backend/citas/calendario_citas/agregar_cita.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formValues)
                })
                .then(res => res.json())
                .then (data => {
                    if (data.success) {
                        calendar.refetchEvents();
                        Swal.fire('Cita agregada', '', 'success');
                    } else {
                        Swal.fire('Error', data.error || 'No se pudo agregar la cita', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'No se pudo conectar con el servidor', 'error'));
            }
        }
    });
    calendar.render();

    // Filtrar automáticamente al cambiar el select
    document.getElementById('filtro-especialidad').addEventListener('change', function() {
        filtroEspecialidad = this.value;
        calendar.refetchEvents();
    });

    // Leyenda con SweetAlert2 en dos columnas
    document.getElementById('btn-leyenda').addEventListener('click', function() {
        const especialidades = <?php echo json_encode($especialidades); ?>.slice().sort((a, b) =>
            a.nombre_especialidad.localeCompare(b.nombre_especialidad, 'es', {sensitivity: 'base'})
        );
        const mitad = Math.ceil(especialidades.length / 2);
        let col1 = '', col2 = '';
        especialidades.forEach((esp, idx) => {
            const html = `<div style="margin-bottom:6px;display:flex;align-items:center;">
                <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${esp.color};margin-right:8px;border:1px solid #ccc;"></span>
                <span>${esp.nombre_especialidad}</span>
            </div>`;
            if (idx < mitad) col1 += html;
            else col2 += html;
        });
        Swal.fire({
            title: 'Leyenda de Especialidades',
            html: `
                <div style="display:flex;gap:40px;justify-content:center;">
                    <div>${col1}</div>
                    <div>${col2}</div>
                </div>
            `,
            confirmButtonText: 'Cerrar'
        });
    });
});
</script>