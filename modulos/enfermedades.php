<!--Modulo para la gestión de enfermedades-->

        <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Gestión de Enfermedades</h1>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Lista de Enfermedades</h6>
                <a href="#" class="btn btn-primary btn-circle btn-sm" id="btnAgregarEnfermedad" title="Nueva Enfermedad">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-enfermedades">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre Enfermedad</th>
                                <th style="width:120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="enfermedades-list">
                            <!-- Se llena dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
let enfermedades = [];
const pageSize = 7;
let currentPage = 1;

async function cargarEnfermedades() {
    try {
        // Cambia la ruta a absoluta desde la raíz del proyecto
        const resp = await fetch('/sisgeasic/backend/enfermedades/enfermedades_backend.php');
        const data = await resp.json();
        if (data.success) {
            enfermedades = data.data;
        } else {
            enfermedades = [];
            Swal.fire('Error', data.message || 'No se pudieron cargar las enfermedades.', 'error');
        }
    } catch (e) {
        enfermedades = [];
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    }
    renderEnfermedades();
}

function renderEnfermedades() {
    const tableBody = document.getElementById('enfermedades-list');
    tableBody.innerHTML = '';
    const total = enfermedades.length;
    const totalPages = Math.ceil(total / pageSize);
    const start = (currentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, total);

    if (total > 0) {
        for (let i = start; i < end; i++) {
            const enfermedad = enfermedades[i];
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="text-align:left;">${enfermedad.nombre || 'N/A'}</td>
                <td>
                    <div class="acciones-btns">
                        <button class="btn btn-warning btn-circle btn-sm" title="Editar" onclick="editarEnfermedad(${enfermedad.id_enfermedad})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-circle btn-sm" title="Eliminar" onclick="eliminarEnfermedad(${enfermedad.id_enfermedad})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        }
    } else {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="2" class="text-center">No hay enfermedades registradas.</td>`;
        tableBody.appendChild(row);
    }
    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    let pagination = document.getElementById('enfermedades-pagination');
    if (!pagination) {
        pagination = document.createElement('nav');
        pagination.id = 'enfermedades-pagination';
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
    const totalPages = Math.ceil(enfermedades.length / pageSize);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderEnfermedades();
}

document.getElementById('btnAgregarEnfermedad').addEventListener('click', () => {
    Swal.fire({
        title: 'Agregar Enfermedad',
        html: `
            <input type="text" id="nombre" class="swal2-input" placeholder="Nombre de la enfermedad" required>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const nombre = document.getElementById('nombre').value.trim();
            if (!nombre) {
                Swal.showValidationMessage('Debe ingresar un nombre.');
                return false;
            }
            return { nombre };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/sisgeasic/backend/enfermedades/agregar_enfermedad.php', {
                method: 'POST',
                body: new URLSearchParams({
                    nombre: result.value.nombre
                })
            })
            .then(async r => {
                let data;
                try {
                    data = await r.json();
                } catch (e) {
                    const text = await r.text();
                    console.error('Respuesta no JSON:', text);
                    Swal.fire('Error', 'Respuesta inesperada del servidor: ' + text, 'error');
                    return;
                }
                if (data.success) {
                    Swal.fire('Éxito', 'Enfermedad agregada.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo agregar.', 'error');
                }
            })
            .catch(error => {
                console.error('Error en fetch:', error);
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            });
        }
    });
});

function editarEnfermedad(id) {
    const enfermedad = enfermedades.find(e => e.id_enfermedad == id);
    Swal.fire({
        title: 'Editar Enfermedad',
        html: `
            <input type="text" id="nombre" class="swal2-input" value="${enfermedad.nombre}" required>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const nombre = document.getElementById('nombre').value.trim();
            if (!nombre) {
                Swal.showValidationMessage('Debe ingresar un nombre.');
                return false;
            }
            return { nombre };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/sisgeasic/backend/enfermedades/editar_enfermedad.php', {
                method: 'POST',
                body: new URLSearchParams({
                    id_enfermedad: id,
                    nombre: result.value.nombre
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
                    Swal.fire('Éxito', 'Enfermedad actualizada.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo actualizar.', 'error');
                }
            });
        }
    });
}

function eliminarEnfermedad(id) {
    Swal.fire({
        title: '¿Eliminar enfermedad?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/sisgeasic/backend/enfermedades/eliminar_enfermedad.php', {
                method: 'POST',
                body: new URLSearchParams({
                    id_enfermedad: id
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
                    Swal.fire('Eliminado', 'Enfermedad eliminada.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo eliminar.', 'error');
                }
            });
        }
    });
}

cargarEnfermedades();
</script>
    
</body>
</html>