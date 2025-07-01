<!--modulo de especialidades-->

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gestión de Especialidades Médicas</h1>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Especialidades</h6>
            <a href="#" class="btn btn-primary btn-circle btn-sm" id="btnAgregarEspecialidad" title="Nueva Especialidad">
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-especialidades">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Color</th>
                            <th>Horario</th>
                            <th style="width:160px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="especialidades-list">
                        <!-- Se llena dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let especialidades = [];
const pageSize = 7;
let currentPage = 1;

async function cargarEspecialidades() {
    try {
        const resp = await fetch('/sisgeasic/backend/especialidades/especialidades_backend.php');
        const data = await resp.json();
        if (data.success) {
            especialidades = data.data;
            // Ordenar por id_especialidad ascendente
            especialidades.sort((a, b) => a.id_especialidad - b.id_especialidad);
        } else {
            especialidades = [];
            Swal.fire('Error', data.message || 'No se pudieron cargar las especialidades.', 'error');
        }
    } catch (e) {
        especialidades = [];
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    }
    renderEspecialidades();
}

function renderEspecialidades() {
    const tableBody = document.getElementById('especialidades-list');
    tableBody.innerHTML = '';
    const total = especialidades.length;
    const totalPages = Math.ceil(total / pageSize);
    const start = (currentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, total);

    if (total > 0) {
        for (let i = start; i < end; i++) {
            const esp = especialidades[i];
            const row = document.createElement('tr');
            // Mostrar horario como "HH:MM - HH:MM"
            let horario = '';
            if (esp.hora_inicio && esp.hora_fin) {
                horario = `${esp.hora_inicio} - ${esp.hora_fin}`;
            } else if (esp.hora_inicio) {
                horario = `${esp.hora_inicio} - <span class="text-muted">No asignado</span>`;
            } else if (esp.hora_fin) {
                horario = `<span class="text-muted">No asignado</span> - ${esp.hora_fin}`;
            } else {
                horario = '<span class="text-muted">No asignado</span>';
            }
            row.innerHTML = `
                <td>${esp.id_especialidad}</td>
                <td>${esp.nombre_especialidad}</td>
                <td>
                    <span style="
                        display:inline-block;
                        width:24px;
                        height:24px;
                        border-radius:50%;
                        background:${esp.color};
                        border:1px solid #ccc;
                        vertical-align:middle;
                    "></span>
                </td>
                <td>${horario}</td>
                <td>
                    <button class="btn btn-warning btn-circle btn-sm" title="Editar" onclick="editarEspecialidad(${esp.id_especialidad})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-circle btn-sm" title="Eliminar" onclick="eliminarEspecialidad(${esp.id_especialidad})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        }
    } else {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="5" class="text-center">No hay especialidades registradas.</td>`;
        tableBody.appendChild(row);
    }
    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    let pagination = document.getElementById('especialidades-pagination');
    if (!pagination) {
        pagination = document.createElement('nav');
        pagination.id = 'especialidades-pagination';
        pagination.className = 'mt-3';
        document.querySelector('.card-body').appendChild(pagination);
    }
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    let html = `<ul class="pagination justify-content-center mb-0">`;
    html += `<li class="page-item${currentPage === 1 ? ' disabled' : ''}">
                <a class="page-link" href="#" tabindex="-1" onclick="gotoPage(${currentPage - 1});return false;" title="Anterior">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item${currentPage === i ? ' active' : ''}">
                    <a class="page-link" href="#" onclick="gotoPage(${i});return false;" title="Página ${i}">
                        ${i}
                    </a>
                </li>`;
    }
    html += `<li class="page-item${currentPage === totalPages ? ' disabled' : ''}">
                <a class="page-link" href="#" onclick="gotoPage(${currentPage + 1});return false;" title="Siguiente">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
    html += `</ul>`;
    pagination.innerHTML = html;
}

window.gotoPage = function(page) {
    const totalPages = Math.ceil(especialidades.length / pageSize);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderEspecialidades();
}

document.getElementById('btnAgregarEspecialidad').addEventListener('click', () => {
    Swal.fire({
        title: 'Agregar Especialidad',
        html: `
            <input type="text" id="nombre_especialidad" class="swal2-input" placeholder="Nombre de la especialidad" required>
            <input type="color" id="color" class="swal2-input" value="#007bff" style="width:100px;">
            <input type="time" id="hora_inicio" class="swal2-input" placeholder="Hora de inicio">
            <input type="time" id="hora_fin" class="swal2-input" placeholder="Hora de fin">
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const nombre = document.getElementById('nombre_especialidad').value.trim();
            const color = document.getElementById('color').value;
            const hora_inicio = document.getElementById('hora_inicio').value;
            const hora_fin = document.getElementById('hora_fin').value;
            if (!nombre) {
                Swal.showValidationMessage('Debe ingresar un nombre.');
                return false;
            }
            // Validar formato de hora (opcional)
            if ((hora_inicio && !/^\d{2}:\d{2}$/.test(hora_inicio)) || (hora_fin && !/^\d{2}:\d{2}$/.test(hora_fin))) {
                Swal.showValidationMessage('Las horas deben estar en formato HH:MM.');
                return false;
            }
            return { nombre_especialidad: nombre, color: color, hora_inicio: hora_inicio, hora_fin: hora_fin };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/sisgeasic/backend/especialidades/agregar_especialidad.php', {
                method: 'POST',
                body: new URLSearchParams({
                    nombre_especialidad: result.value.nombre_especialidad,
                    color: result.value.color,
                    hora_inicio: result.value.hora_inicio,
                    hora_fin: result.value.hora_fin
                })
            })
            .then(async r => {
                let data;
                try {
                    data = await r.json();
                } catch (e) {
                    const text = await r.text();
                    Swal.fire('Error', 'Respuesta inesperada del servidor: ' + text, 'error');
                    return;
                }
                if (data.success) {
                    Swal.fire('Éxito', 'Especialidad agregada.', 'success').then(() => location.reload());
                } else {
                    // Validar si el error es por clave duplicada
                    if (data.message && data.message.includes('duplicate key value')) {
                        Swal.fire('Error', 'Ya existe una especialidad con ese identificador. Por favor, intente con otro nombre o revise la base de datos.', 'error');
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo agregar.', 'error');
                    }
                }
            })
            .catch(error => {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            });
        }
    });
});

function editarEspecialidad(id) {
    const esp = especialidades.find(e => e.id_especialidad == id);
    Swal.fire({
        title: 'Editar Especialidad',
        html: `
            <input type="text" id="nombre_especialidad" class="swal2-input" value="${esp.nombre_especialidad}" required>
            <input type="color" id="color" class="swal2-input" value="${esp.color}" style="width:100px;">
            <input type="time" id="hora_inicio" class="swal2-input" value="${esp.hora_inicio}" placeholder="Hora de inicio">
            <input type="time" id="hora_fin" class="swal2-input" value="${esp.hora_fin}" placeholder="Hora de fin">
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const nombre = document.getElementById('nombre_especialidad').value.trim();
            const color = document.getElementById('color').value;
            const hora_inicio = document.getElementById('hora_inicio').value;
            const hora_fin = document.getElementById('hora_fin').value;
            if (!nombre) {
                Swal.showValidationMessage('Debe ingresar un nombre.');
                return false;
            }
            // Validar formato de hora (opcional)
            if ((hora_inicio && !/^\d{2}:\d{2}$/.test(hora_inicio)) || (hora_fin && !/^\d{2}:\d{2}$/.test(hora_fin))) {
                Swal.showValidationMessage('Las horas deben estar en formato HH:MM.');
                return false;
            }
            return { nombre_especialidad: nombre, color: color, hora_inicio: hora_inicio, hora_fin: hora_fin };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/sisgeasic/backend/especialidades/editar_especialidad.php', {
                method: 'POST',
                body: new URLSearchParams({
                    id_especialidad: id,
                    nombre_especialidad: result.value.nombre_especialidad,
                    color: result.value.color,
                    hora_inicio: result.value.hora_inicio,
                    hora_fin: result.value.hora_fin
                })
            })
            .then(async r => {
                let data;
                try {
                    data = await r.json();
                } catch (e) {
                    const text = await r.text();
                    Swal.fire('Error', 'Respuesta inesperada del servidor: ' + text, 'error');
                    return;
                }
                if (data.success) {
                    Swal.fire('Éxito', 'Especialidad actualizada.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo actualizar.', 'error');
                }
            });
        }
    });
}

function eliminarEspecialidad(id) {
    Swal.fire({
        title: '¿Eliminar especialidad?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/sisgeasic/backend/especialidades/eliminar_especialidad.php', {
                method: 'POST',
                body: new URLSearchParams({
                    id_especialidad: id
                })
            })
            .then(async r => {
                let data;
                try {
                    data = await r.json();
                } catch (e) {
                    const text = await r.text();
                    Swal.fire('Error', 'Respuesta inesperada del servidor: ' + text, 'error');
                    return;
                }
                if (data.success) {
                    Swal.fire('Eliminado', 'Especialidad eliminada.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo eliminar.', 'error');
                }
            });
        }
    });
}

cargarEspecialidades();
</script>