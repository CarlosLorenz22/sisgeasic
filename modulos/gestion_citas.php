<?php
session_start();
$id_medico = $_SESSION['id_medico'] ?? 0;
$id_rol = $_SESSION['id_rol'] ?? 0;

include_once __DIR__ . '/../backend/conexion.php';

// Si el usuario es médico, filtra solo sus citas
$where = [];
if ($id_rol == 2 && $id_medico) {
    $where[] = "id_medico = $id_medico";
}
// Puedes agregar más filtros aquí si lo necesitas

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT * FROM cita $where_sql ORDER BY fecha_inicio DESC";
$result = pg_query($conn, $query);
?>

<!-- Contenedor principal -->
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Gestión de Citas</h1>
                    <?php if ($id_rol == 1 || $id_rol == 3): ?>
                    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                        <label class="mr-2 mb-0">Especialidad:</label>
                        <select id="filtro-especialidad" class="form-control mr-3" style="width:auto;display:inline-block;">
                            <option value="">Todas</option>
                        </select>
                        <label class="mr-2 mb-0">Estado:</label>
                        <select id="filtro-estado" class="form-control" style="width:auto;display:inline-block;">
                            <option value="todas">Todas</option>
                            <option value="no_atendida">No atendidas</option>
                            <option value="pendientes">Pendientes</option>
                            <option value="atendida">Atendidas</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Citas</h6>
                            <a href="/sisgeasic/dashboard.php?modulo=citas" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar Cita
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <!-- <th>ID Cita</th> -->
                                            <th>Paciente</th>
                                            <th>Médico</th>
                                            <th>Especialidad</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Status</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="citas-list">
                                        <!-- Los datos se llenarán dinámicamente con JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            <nav aria-label="Paginación">
                                <ul class="pagination justify-content-center" id="pagination">
                                    <!-- Los botones de paginación se generarán dinámicamente -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        let citas = [];
        let especialidades = [];
        const rowsPerPage = 6;
        let currentPage = 1;
        let filteredCitas = [];
        let modoAtendidas = false;

        <?php if ($id_rol == 1 || $id_rol == 3): ?>
        function cargarEspecialidades() {
            fetch('/sisgeasic/backend/citas/gestion_citas/especialidades_backend.php')
                .then(r => r.json())
                .then(data => {
                    especialidades = data;
                    const select = document.getElementById('filtro-especialidad');
                    select.innerHTML = '<option value="">Todas</option>';
                    especialidades.forEach(e => {
                        select.innerHTML += `<option value="${e.id_especialidad}">${e.nombre_especialidad}</option>`;
                    });
                });
        }
        <?php endif; ?>

        function cargarCitas() {
            let estado;
            <?php if ($id_rol == 1 || $id_rol == 3): ?>
                estado = document.getElementById('filtro-estado').value;
            <?php else: ?>
                estado = 'todas';
            <?php endif; ?>
            if (estado === 'atendida') {
                modoAtendidas = true;
                fetch('/sisgeasic/backend/citas/gestion_citas/citas_atendidas_backend.php')
                    .then(response => response.json())
                    .then(data => {
                        citas = data || [];
                        aplicarFiltros();
                    });
            } else {
                modoAtendidas = false;
                fetch('/sisgeasic/backend/citas/gestion_citas/gestion_citas_backend.php')
                    .then(response => response.json())
                    .then(data => {
                        citas = data || [];
                        aplicarFiltros();
                    });
            }
        }

        function aplicarFiltros() {
            let filtroEspecialidad, filtroEstado;
            <?php if ($id_rol == 1 || $id_rol == 3): ?>
                filtroEspecialidad = document.getElementById('filtro-especialidad').value;
                filtroEstado = document.getElementById('filtro-estado').value;
            <?php else: ?>
                filtroEspecialidad = '';
                filtroEstado = 'todas';
            <?php endif; ?>

            filteredCitas = citas.filter(cita => {
                let matchEspecialidad = !filtroEspecialidad || cita.id_especialidad == filtroEspecialidad;
                let matchEstado = true;
                if (!modoAtendidas) {
                    if (filtroEstado === 'no_atendida') {
                        matchEstado =
                            (!cita.status || cita.status.trim() === '' || cita.status !== 'atendida')
                            && cita.fecha_fin && (new Date() > new Date(cita.fecha_fin));
                    } else if (filtroEstado === 'pendientes') {
                        matchEstado = cita.status === 'pendiente';
                    }
                }
                return matchEspecialidad && matchEstado;
            });
            currentPage = 1;
            renderPagination();
            renderTable(currentPage);
        }

        function renderTable(page = 1) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const visibleCitas = filteredCitas.slice(start, end);

            const tableBody = document.getElementById('citas-list');
            tableBody.innerHTML = '';

            visibleCitas.forEach(cita => {
                const fueraDePlazo = !modoAtendidas && cita.status !== 'atendida' && new Date() > new Date(cita.fecha_fin);
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${cita.nombre_paciente || 'N/A'}</td>
                    <td>${cita.nombre_medico || 'N/A'}</td>
                    <td>${cita.especialidad || 'N/A'}</td>
                    <td>${cita.fecha_inicio || 'N/A'}</td>
                    <td>${cita.fecha_fin || 'N/A'}</td>
                    <td>
                        ${modoAtendidas ? '<span class="badge badge-success">Atendida</span>' :
                            (cita.status === 'atendida' ? '<span class="badge badge-success">Atendida</span>' :
                            (fueraDePlazo ? '<span class="badge badge-danger">Fuera de plazo</span>' :
                            (cita.status === 'en_proceso' ? '<span class="badge badge-info">En proceso</span>' :
                            '<span class="badge badge-warning">Pendiente</span>')))}
                    </td>
                    <td>
                        ${modoAtendidas ? '' : fueraDePlazo ? `
                            <button class="btn-icon btn-primary" title="Renovar cita" type="button"
                                onclick="renovarCitaPrompt(${cita.id_cita}, '${cita.fecha_inicio}', '${cita.fecha_fin}')">
                                <i class="fas fa-redo"></i>
                            </button>` : `
                            <button class="btn-icon btn-info" title="Enviar cita" onclick="notificarCita(${cita.id_cita})">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <button class="btn-icon btn-success" title="Cita atendida" onclick="marcarCitaAtendida(${cita.id_cita})">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            <button class="btn-icon btn-warning" title="Editar" onclick="editarCitaPrompt(${cita.id_cita})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" title="Eliminar" onclick="eliminarCita(${cita.id_cita})">
                                <i class="fas fa-trash"></i>
                            </button>`}
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredCitas.length / rowsPerPage);
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';

            if (totalPages > 1) {
                // Botón "Anterior"
                const prevLi = document.createElement('li');
                prevLi.className = `page-item${currentPage === 1 ? ' disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link" href="#" tabindex="-1" aria-label="Anterior" title="Anterior"><i class="fas fa-chevron-left"></i></a>`;
                if (currentPage > 1) {
                    prevLi.addEventListener('click', (e) => {
                        e.preventDefault();
                        currentPage--;
                        renderTable(currentPage);
                        renderPagination();
                    });
                }
                pagination.appendChild(prevLi);

                // Botones de páginas
                for (let i = 1; i <= totalPages; i++) {
                    const pageLi = document.createElement('li');
                    pageLi.className = `page-item${i === currentPage ? ' active' : ''}`;
                    pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                    if (i !== currentPage) {
                        pageLi.addEventListener('click', (e) => {
                            e.preventDefault();
                            currentPage = i;
                            renderTable(currentPage);
                            renderPagination();
                        });
                    }
                    pagination.appendChild(pageLi);
                }

                // Botón "Siguiente"
                const nextLi = document.createElement('li');
                nextLi.className = `page-item${currentPage === totalPages ? ' disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Siguiente" title="Siguiente"><i class="fas fa-chevron-right"></i></a>`;
                if (currentPage < totalPages) {
                    nextLi.addEventListener('click', (e) => {
                        e.preventDefault();
                        currentPage++;
                        renderTable(currentPage);
                        renderPagination();
                    });
                }
                pagination.appendChild(nextLi);
            }
        }

        function cambiarEstadoCita(id) {
            Swal.fire({
                title: '¿Cambiar estado de la cita?',
                text: "Esta acción actualizará el estado de la cita.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`cambiar_estado_cita.php?id=${id}`, { method: 'GET' })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Estado actualizado',
                                    text: 'El estado de la cita se ha actualizado con éxito.',
                                    showConfirmButton: false,
                                    timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo actualizar el estado de la cita.',
                                    confirmButtonText: 'Aceptar'
                            });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al intentar actualizar el estado.',
                                confirmButtonText: 'Aceptar'
                            });
                        });
                }
            });
        }

        function marcarCitaAtendida(id) {
            Swal.fire({
                title: '¿Marcar cita como atendida?',
                text: "Esta acción moverá la cita a historial y la eliminará de la lista.",
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, marcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/sisgeasic/backend/citas/gestion_citas/marcar_cita_atendida.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id=' + encodeURIComponent(id)
                    })
                    .then(response => response.json())
                    .then ( data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cita atendida',
                                text: 'La cita ha sido movida al historial.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                cargarCitas();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.error || 'No se pudo marcar la cita como atendida.',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al intentar marcar la cita como atendida.',
                            confirmButtonText: 'Aceptar'
                        });
                    });
                }
            });
        }

        function renovarCitaPrompt(id, fecha_inicio, fecha_fin) {
            Swal.fire({
                title: 'Renovar cita',
                html:
                    '<label>Nueva fecha y hora de inicio</label><input id="swal-fecha-inicio" type="datetime-local" class="swal2-input" value="' + (fecha_inicio || '') + '">' +
                    '<label>Nueva fecha y hora de fin</label><input id="swal-fecha-fin" type="datetime-local" class="swal2-input" value="' + (fecha_fin || '') + '">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const fechaInicio = document.getElementById('swal-fecha-inicio').value;
                    const fechaFin = document.getElementById('swal-fecha-fin').value;
                    if (!fechaInicio || !fechaFin) {
                        Swal.showValidationMessage('Debe ingresar ambas fechas');
                        return false;
                    }
                    return { fecha_inicio: fechaInicio, fecha_fin: fechaFin };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const datos = `id=${encodeURIComponent(id)}&fecha_inicio=${encodeURIComponent(result.value.fecha_inicio)}&fecha_fin=${encodeURIComponent(result.value.fecha_fin)}`;
                    fetch('/sisgeasic/backend/citas/gestion_citas/renovar_cita.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: datos
                    })
                    .then(response => response.text())
                    .then(text => {
                        if (text.includes('Cita actualizada correctamente')) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Cita renovada!',
                                text: 'La cita ha sido renovada con éxito.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // Mostrar el mensaje de error real devuelto por el backend
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: text || 'No se pudo renovar la cita.',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                    .catch((error) => {
                        // Mostrar el error real en la alerta y en la consola
                        console.error('Error al renovar cita:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al intentar renovar la cita: ' + error,
                            confirmButtonText: 'Aceptar'
                        });
                    });
                }
            });
        }

        function notificarCita(idCita) {
            Swal.fire({
                title: '¿Enviar notificación al paciente?',
                text: 'Se enviará un correo y un SMS con los datos de la cita.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('notificar_cita.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id_cita=' + encodeURIComponent(idCita)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Enviado', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo enviar la notificación.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                    });
                }
            });
        }

        function eliminarCita(id) {
            Swal.fire({
                title: '¿Cancelar cita?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/sisgeasic/backend/citas/gestion_citas/eliminar_cita.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id=' + encodeURIComponent(id)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cita cancelada',
                                text: 'La cita ha sido cancelada.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                cargarCitas();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.error || 'No se pudo eliminar la cita.',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al intentar eliminar la cita: ' + error,
                            confirmButtonText: 'Aceptar'
                        });
                    });
                }
            });
        }

        // Nueva función para cargar médicos por especialidad desde el backend
        function cargarMedicosPorEspecialidad(idEspecialidad, idMedicoSeleccionado = null) {
            if (!idEspecialidad || isNaN(idEspecialidad) || idEspecialidad === "") {
                document.getElementById('swal-medico').innerHTML = '<option value="">No hay médicos</option>';
                return;
            }
            const medicoSelect = document.getElementById('swal-medico');
            medicoSelect.innerHTML = '<option value="">Cargando médicos...</option>';
            fetch(`/sisgeasic/backend/citas/gestion_citas/medicos_por_especialidad.php?id_especialidad=${idEspecialidad}`)
                .then(r => r.json())
                .then(medicos => {
                    medicoSelect.innerHTML = '';
                    if (Array.isArray(medicos) && medicos.length > 0) {
                        medicos.forEach(medico => {
                            medicoSelect.innerHTML += `<option value="${medico.id_medico}"${idMedicoSeleccionado == medico.id_medico ? ' selected' : ''}>${medico.nombre_medico}</option>`;
                        });
                    } else {
                        medicoSelect.innerHTML = '<option value="">No hay médicos</option>';
                    }
                })
                .catch(() => {
                    medicoSelect.innerHTML = '<option value="">Error al cargar médicos</option>';
                });
        }

        function editarCitaPrompt(idCita) {
            const cita = citas.find(c => c.id_cita == idCita);
            if (!cita) {
                Swal.fire('Error', 'No se encontró la cita.', 'error');
                return;
            }

            // Mostrar la modal con selects vacíos
            Swal.fire({
                title: 'Editar cita',
                html:
                    '<label>Título</label><input id="swal-titulo" class="swal2-input" value="' + (cita.titulo || '') + '">' +
                    '<label>Fecha y hora de inicio</label><input id="swal-fecha-inicio" type="datetime-local" class="swal2-input" value="' + (cita.fecha_inicio ? cita.fecha_inicio.replace(" ", "T") : "") + '">' +
                    '<label>Fecha y hora de fin</label><input id="swal-fecha-fin" type="datetime-local" class="swal2-input" value="' + (cita.fecha_fin ? cita.fecha_fin.replace(" ", "T") : "") + '">' +
                    '<label>Especialidad</label><select id="swal-especialidad" class="swal2-input"></select>' +
                    '<label>Médico</label><select id="swal-medico" class="swal2-input"><option value="">Cargando médicos...</option></select>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                didOpen: () => {
                    // 1. Cargar especialidades desde el backend
                    fetch('/sisgeasic/backend/citas/gestion_citas/especialidades_backend.php')
                        .then(res => res.json())
                        .then(especialidades => {
                            const selectEsp = document.getElementById('swal-especialidad');
                            selectEsp.innerHTML = '';
                            especialidades.forEach(e => {
                                selectEsp.innerHTML += `<option value="${e.id_especialidad}"${cita.id_especialidad == e.id_especialidad ? ' selected' : ''}>${e.nombre_especialidad}</option>`;
                            });

                            // 2. Cargar médicos para la especialidad seleccionada
                            cargarMedicosPorEspecialidad(selectEsp.value, cita.id_medico);

                            // 3. Al cambiar especialidad, cargar médicos de esa especialidad
                            selectEsp.addEventListener('change', function() {
                                cargarMedicosPorEspecialidad(this.value);
                            });
                        });
                },
                preConfirm: () => {
                    const titulo = document.getElementById('swal-titulo').value;
                    const fechaInicio = document.getElementById('swal-fecha-inicio').value;
                    const fechaFin = document.getElementById('swal-fecha-fin').value;
                    const id_especialidad = document.getElementById('swal-especialidad').value;
                    const id_medico = document.getElementById('swal-medico').value;

                    if (!titulo || !fechaInicio || !fechaFin || !id_especialidad || !id_medico) {
                        Swal.showValidationMessage('Debe completar todos los campos');
                        return false;
                    }
                    return { titulo, fecha_inicio: fechaInicio, fecha_fin: fechaFin, id_especialidad, id_medico };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const datos = `id=${encodeURIComponent(idCita)}&titulo=${encodeURIComponent(result.value.titulo)}&fecha_inicio=${encodeURIComponent(result.value.fecha_inicio)}&fecha_fin=${encodeURIComponent(result.value.fecha_fin)}&id_especialidad=${encodeURIComponent(result.value.id_especialidad)}&id_medico=${encodeURIComponent(result.value.id_medico)}`;
                    fetch('/sisgeasic/backend/citas/gestion_citas/editar_cita.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: datos
                    })
                    .then(response => response.text())
                    .then(text => {
                        if (text.includes('Cita actualizada correctamente')) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Cita actualizada!',
                                text: 'La cita ha sido editada con éxito.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                cargarCitas();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: text || 'No se pudo editar la cita.',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al intentar editar la cita: ' + error,
                            confirmButtonText: 'Aceptar'
                        });
                    });
                }
            });
        }

        <?php if ($id_rol == 1 || $id_rol == 3): ?>
    document.getElementById('filtro-especialidad').addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-estado').addEventListener('change', cargarCitas);
    cargarEspecialidades();
<?php endif; ?>
    cargarCitas();
</script>