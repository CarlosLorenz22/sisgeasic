<!-- Contenedor principal -->
                <div class="container-fluid">
                    <!-- CRUD de médicos -->
                    <h1 class="h3 mb-4 text-gray-800">Gestión de Médicos</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Médicos</h6>
                            <button class="btn btn-primary btn-circle btn-sm" id="openAddMedicoModal"
                                title="Agregar Médico">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Buscador -->
                            <div class="input-group mb-3" style="max-width: 400px;">
                                <input type="text" id="searchInputMedicos" class="form-control"
                                    placeholder="Buscar por cualquier dato del médico">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTableMedicos" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Primer Nombre</th>
                                            <th>Segundo Nombre</th>
                                            <th>Primer Apellido</th>
                                            <th>Segundo Apellido</th>
                                            <th>Especialidad</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="medicosTableBody">
                                        <!-- Las filas se llenan por JS -->
                                    </tbody>
                                </table>
                            </div>
                            <!-- El paginador se crea dinámicamente por JS -->
                        </div>
                    </div>
                </div>

                <!-- Contenedor para la modal -->
                <div id="addMedicoModalContainer"></div>

                
        </div>
    </div>
    </div>

    <script>
const pageSizeMedicos = 6;
let currentPageMedicos = 1;
let medicosData = [];
let medicosFiltrados = [];
let especialidadesData = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarEspecialidades();
    cargarMedicos();

    document.getElementById('searchInputMedicos').addEventListener('input', function() {
        filtrarTablaMedicos(this.value);
    });

    document.getElementById('openAddMedicoModal').addEventListener('click', function() {
        openAddMedicoModal();
    });
});

function cargarEspecialidades(callback) {
    fetch('/sisgeasic/backend/medico/especialidades_backend.php')
        .then(res => res.json())
        .then(data => {
            especialidadesData = data;
            if (typeof callback === 'function') callback();
        });
}

function getEspecialidadesOptions(selectedId = null) {
    let options = '<option value="">Seleccione</option>';
    especialidadesData.forEach(e => {
        options += `<option value="${e.id_especialidad}"${selectedId == e.id_especialidad ? ' selected' : ''}>${e.nombre_especialidad}</option>`;
    });
    return options;
}

function cargarMedicos() {
    fetch('/sisgeasic/backend/medico/medico_backend.php')
        .then(res => res.json())
        .then(data => {
            medicosData = data;
            medicosFiltrados = data;
            currentPageMedicos = 1;
            renderizarTablaMedicos(getMedicosPagina(medicosFiltrados, currentPageMedicos));
            renderizarPaginacionMedicos(medicosFiltrados.length);
        });
}

function getMedicosPagina(medicos, page) {
    const start = (page - 1) * pageSizeMedicos;
    return medicos.slice(start, start + pageSizeMedicos);
}

function renderizarTablaMedicos(medicos) {
    const tbody = document.getElementById('medicosTableBody');
    tbody.innerHTML = '';
    if (!medicos.length) {
        tbody.innerHTML = "<tr><td colspan='6' class='text-center'>No hay médicos registrados</td></tr>";
        return;
    }
    medicos.forEach(medico => {
        tbody.innerHTML += `
            <tr>
                <td>${medico.pr_nombre || ''}</td>
                <td>${medico.sdo_nombre || ''}</td>
                <td>${medico.pr_apellido || ''}</td>
                <td>${medico.sdo_apellido || ''}</td>
                <td>${medico.nombre_especialidad || ''}</td>
                <td>
                    <button class='btn btn-circle btn-info btn-sm' title='Ver' onclick='viewMedicoDetails(${medico.id_medico})'>
                        <i class='fas fa-eye'></i>
                    </button>
                    <button class='btn btn-circle btn-warning btn-sm' title='Editar' onclick='openEditMedicoModal(${medico.id_medico})'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='btn btn-circle btn-danger btn-sm' title='Eliminar' onclick='confirmDeleteMedico(${medico.id_medico})'>
                        <i class='fas fa-trash'></i>
                    </button>
                </td>
            </tr>
        `;
    });
}

function renderizarPaginacionMedicos(totalItems) {
    // Elimina el paginador anterior si existe
    let paginador = document.getElementById('medicos-pagination');
    if (paginador) paginador.remove();

    const totalPages = Math.ceil(totalItems / pageSizeMedicos);
    if (totalPages <= 1) return;

    paginador = document.createElement('nav');
    paginador.id = 'medicos-pagination';
    paginador.className = 'mt-3';

    let html = `<ul class="pagination justify-content-center mb-0">`;
    html += `<li class="page-item${currentPageMedicos === 1 ? ' disabled' : ''}">
                <a class="page-link" href="#" tabindex="-1" onclick="gotoPageMedicos(${currentPageMedicos - 1});return false;" title="Anterior">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item${currentPageMedicos === i ? ' active' : ''}">
                    <a class="page-link" href="#" onclick="gotoPageMedicos(${i});return false;" title="Página ${i}">
                        ${i}
                    </a>
                </li>`;
    }
    html += `<li class="page-item${currentPageMedicos === totalPages ? ' disabled' : ''}">
                <a class="page-link" href="#" onclick="gotoPageMedicos(${currentPageMedicos + 1});return false;" title="Siguiente">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
    html += `</ul>`;
    paginador.innerHTML = html;

    // Inserta el paginador después de la tabla
    const cardBody = document.querySelector('.card-body');
    cardBody.appendChild(paginador);
}

window.gotoPageMedicos = function(page) {
    const totalPages = Math.ceil(medicosFiltrados.length / pageSizeMedicos);
    if (page < 1 || page > totalPages) return;
    currentPageMedicos = page;
    renderizarTablaMedicos(getMedicosPagina(medicosFiltrados, currentPageMedicos));
    renderizarPaginacionMedicos(medicosFiltrados.length);
}

function filtrarTablaMedicos(texto) {
    texto = texto.toLowerCase();
    medicosFiltrados = medicosData.filter(m =>
        (m.pr_nombre || '').toLowerCase().includes(texto) ||
        (m.sdo_nombre || '').toLowerCase().includes(texto) ||
        (m.pr_apellido || '').toLowerCase().includes(texto) ||
        (m.sdo_apellido || '').toLowerCase().includes(texto) ||
        (m.nombre_especialidad || '').toLowerCase().includes(texto)
    );
    currentPageMedicos = 1;
    renderizarTablaMedicos(getMedicosPagina(medicosFiltrados, currentPageMedicos));
    renderizarPaginacionMedicos(medicosFiltrados.length);
}

// Implementación básica de las funciones de acción

function viewMedicoDetails(id) {
    const medico = medicosData.find(m => m.id_medico == id);
    if (!medico) return;
    Swal.fire({
        title: 'Detalles del Médico',
        html: `
            <b>ID Médico:</b> ${medico.id_medico || ''}<br>
            <b>Especialidad:</b> ${medico.nombre_especialidad || ''}<br>
            <b>Primer Nombre:</b> ${medico.pr_nombre || ''}<br>
            <b>Segundo Nombre:</b> ${medico.sdo_nombre || ''}<br>
            <b>Primer Apellido:</b> ${medico.pr_apellido || ''}<br>
            <b>Segundo Apellido:</b> ${medico.sdo_apellido || ''}<br>
            <b>Horario de Trabajo:</b> ${medico.horario_trabajo || ''}<br>
            <b>Desde:</b> ${medico.hora_inicio || ''}<br>
            <b>Hasta:</b> ${medico.hora_fin || ''}
        `,
        icon: 'info',
        confirmButtonText: 'Cerrar',
        customClass: { popup: 'swal2-modal-custom' }
    });
}

function openEditMedicoModal(id) {
    const medico = medicosData.find(m => m.id_medico == id);
    if (!medico) return;
    cargarEspecialidades(() => {
        Swal.fire({
            title: 'Editar Médico',
            html: `
                <input id="swal-input1" class="swal2-input" placeholder="Primer Nombre" value="${medico.pr_nombre || ''}">
                <input id="swal-input2" class="swal2-input" placeholder="Segundo Nombre" value="${medico.sdo_nombre || ''}">
                <input id="swal-input3" class="swal2-input" placeholder="Primer Apellido" value="${medico.pr_apellido || ''}">
                <input id="swal-input4" class="swal2-input" placeholder="Segundo Apellido" value="${medico.sdo_apellido || ''}">
                <select id="swal-especialidad" class="swal2-input">${getEspecialidadesOptions(medico.id_especialidad)}</select>
                <input id="swal-input5" class="swal2-input" placeholder="Horario Trabajo" value="${medico.horario_trabajo || ''}">
                <input id="swal-input6" class="swal2-input" type="time" value="${medico.hora_inicio || ''}">
                <input id="swal-input7" class="swal2-input" type="time" value="${medico.hora_fin || ''}">
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Guardar Cambios',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'swal2-modal-custom' },
            preConfirm: () => {
                return {
                    id_medico: id,
                    pr_nombre: document.getElementById('swal-input1').value,
                    sdo_nombre: document.getElementById('swal-input2').value,
                    pr_apellido: document.getElementById('swal-input3').value,
                    sdo_apellido: document.getElementById('swal-input4').value,
                    id_especialidad: document.getElementById('swal-especialidad').value,
                    horario_trabajo: document.getElementById('swal-input5').value,
                    hora_inicio: document.getElementById('swal-input6').value,
                    hora_fin: document.getElementById('swal-input7').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/sisgeasic/backend/medico/editar_medico.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(result.value)
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        Swal.fire('¡Actualizado!', 'El médico ha sido actualizado.', 'success');
                        cargarMedicos();
                    } else {
                        Swal.fire('Error', resp.error || 'No se pudo actualizar el médico.', 'error');
                    }
                });
            }
        });
    });
}

function confirmDeleteMedico(id) {
    Swal.fire({
        title: '¿Eliminar médico?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/sisgeasic/backend/medico/eliminar_medico.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id_medico: id})
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    Swal.fire('¡Eliminado!', 'El médico ha sido eliminado.', 'success');
                    cargarMedicos();
                } else {
                    Swal.fire('Error', resp.error || 'No se pudo eliminar el médico.', 'error');
                }
            });
        }
    });
}

function openAddMedicoModal() {
    cargarEspecialidades(() => {
        Swal.fire({
            title: 'Agregar Médico',
            html: `
                <input id="swal-input1" class="swal2-input" placeholder="Primer Nombre">
                <input id="swal-input2" class="swal2-input" placeholder="Segundo Nombre">
                <input id="swal-input3" class="swal2-input" placeholder="Primer Apellido">
                <input id="swal-input4" class="swal2-input" placeholder="Segundo Apellido">
                <select id="swal-especialidad" class="swal2-input">${getEspecialidadesOptions()}</select>
                <input id="swal-input5" class="swal2-input" placeholder="Horario Trabajo">
                <input id="swal-input6" class="swal2-input" type="time">
                <input id="swal-input7" class="swal2-input" type="time">
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Agregar',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'swal2-modal-custom' },
            preConfirm: () => {
                return {
                    pr_nombre: document.getElementById('swal-input1').value,
                    sdo_nombre: document.getElementById('swal-input2').value,
                    pr_apellido: document.getElementById('swal-input3').value,
                    sdo_apellido: document.getElementById('swal-input4').value,
                    id_especialidad: document.getElementById('swal-especialidad').value,
                    horario_trabajo: document.getElementById('swal-input5').value,
                    hora_inicio: document.getElementById('swal-input6').value,
                    hora_fin: document.getElementById('swal-input7').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/sisgeasic/backend/medico/agregar_medico.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(result.value)
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        Swal.fire('¡Agregado!', 'El médico ha sido agregado.', 'success');
                        cargarMedicos();
                    } else {
                        Swal.fire('Error', resp.error || 'No se pudo agregar el médico.', 'error');
                    }
                });
            }
        });
    });
}
</script>
<style>
/* ...existing code... */
.pagination .page-link {
    color: #4e73df;
    font-weight: bold;
    border-radius: 50% !important;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 2px;
    border: none;
    background: #fff;
}
.pagination .page-item.active .page-link {
    background: #4e73df;
    color: #fff !important;
    border-color: #4e73df;
    box-shadow: 0 2px 6px rgba(78,115,223,0.15);
}
.pagination .page-link:hover {
    background: #e3e6f0;
    color: #224abe;
}

.swal2-modal-custom {
    max-width: 500px !important;
    border-radius: 16px !important;
    padding: 32px 24px 24px 24px !important;
    font-family: 'Montserrat', Arial, sans-serif;
}
.swal2-modal-custom .swal2-title {
    font-size: 2rem !important;
    font-weight: bold !important;
    margin-bottom: 1.5rem !important;
}
.swal2-modal-custom .swal2-input, 
.swal2-modal-custom select.swal2-input {
    margin: 0.5rem 0 !important;
    border-radius: 8px !important;
    border: 1px solid #d1d5db !important;
    font-size: 1rem !important;
    padding: 0.75rem 1rem !important;
}
.swal2-modal-custom .swal2-actions {
    margin-top: 2rem !important;
}
.swal2-modal-custom .swal2-confirm {
    background: #6c63ff !important;
    color: #fff !important;
    border-radius: 8px !important;
    font-weight: bold !important;
    font-size: 1rem !important;
    padding: 0.75rem 2rem !important;
}
.swal2-modal-custom .swal2-cancel {
    background: #6c757d !important;
    color: #fff !important;
    border-radius: 8px !important;
    font-size: 1rem !important;
    padding: 0.75rem 2rem !important;
    margin-left: 1rem !important;
}
.swal2-modal-custom .swal2-html-container {
    text-align: left !important;
    font-size: 1.1rem !important;
    margin-bottom: 1rem !important;
}
</style>