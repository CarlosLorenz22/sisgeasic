<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gestión de Pacientes</h1>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Pacientes</h6>
            <button class="btn btn-primary btn-sm" onclick="window.location.href='dashboard.php?modulo=agregar_paciente'">
    <i class="fas fa-user-plus"></i> Agregar Paciente
</button>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex flex-wrap align-items-center">
                <div class="input-group" style="max-width: 400px;">
                    <input type="text" id="busqueda" class="form-control" placeholder="Buscar por cualquier dato del paciente">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="tablaPacientes" width="100%" cellspacing="0" style="background:#fff;">
                    <thead class="thead-light">
                        <tr>
                            <th>Cédula</th>
                            <th>Primer Nombre</th>
                            <th>Primer Apellido</th>
                            <th>Edad</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí se cargan los pacientes por JS -->
                    </tbody>
                </table>
            </div>
            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center" id="paginacion">
                    <!-- Paginación dinámica -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Agrega jQuery antes de tu script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function calcularEdad(fecha_nacimiento) {
    if (!fecha_nacimiento) return '';
    const hoy = new Date();
    const fechaNac = new Date(fecha_nacimiento);
    let edad = hoy.getFullYear() - fechaNac.getFullYear();
    const m = hoy.getMonth() - fechaNac.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
    }
    return edad;
}

function mostrarModalAgregar() {
    Swal.fire({
        title: 'Agregar Paciente',
        html: `
            <form id="formAgregarSwal">
                <input name="cedula" class="form-control mb-2" placeholder="Cédula" required>
                <input name="pr_nombre" class="form-control mb-2" placeholder="Primer Nombre" required>
                <input name="pr_apellido" class="form-control mb-2" placeholder="Primer Apellido" required>
                <input name="fecha_nacimiento" type="date" class="form-control mb-2" placeholder="Fecha de Nacimiento" required>
                <input name="telefono" class="form-control mb-2" placeholder="Teléfono">
                <input name="direccion" class="form-control mb-2" placeholder="Dirección">
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const form = Swal.getPopup().querySelector('#formAgregarSwal');
            const formData = new FormData(form);
            formData.append('accion', 'agregar');
            return fetch('/sisgeasic/backend/pacientes_backend.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(resp => {
                if (!resp.ok) throw new Error(resp.error || 'Error al agregar');
                return resp;
            })
            .catch(error => {
                Swal.showValidationMessage(error.message);
            });
        }
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire('¡Guardado!', 'Paciente agregado correctamente.', 'success');
            cargarPacientes();
        }
    });
}

function mostrarCargando(texto = 'Cargando...') {
    Swal.fire({
        title: texto,
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
}
function cerrarCargando() { Swal.close(); }

// Cambia: solo mostrar animación al cargar página o paginación, NO en búsqueda
function cargarPacientes(busqueda = '', page = 1, mostrarCarga = false) {
    if (mostrarCarga) mostrarCargando();
    fetch(`/sisgeasic/backend/pacientes_backend.php?accion=listar&busqueda=${encodeURIComponent(busqueda)}&page=${page}`)
        .then(r => r.json())
        .then(data => {
            if (mostrarCarga) cerrarCargando();
            const tbody = document.querySelector('#tablaPacientes tbody');
            tbody.innerHTML = '';
            if (!data.pacientes || data.pacientes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No hay pacientes registrados</td></tr>`;
                document.getElementById('paginacion').innerHTML = '';
                return;
            }
            data.pacientes.forEach(p => {
                tbody.innerHTML += `
                    <tr>
                        <td>${p.cedula || ''}</td>
                        <td>${p.pr_nombre || ''}</td>
                        <td>${p.pr_apellido || ''}</td>
                        <td>${calcularEdad(p.fecha_nacimiento)}</td>
                        <td>${p.telefono || ''}</td>
                        <td class="direccion">${p.direccion || ''}</td>
                        <td class="text-center">
                            <button class="btn btn-info btn-circle btn-sm mx-1" title="Ver" onclick="window.location.href='dashboard.php?modulo=ver_paciente&cedula=${encodeURIComponent(p.cedula)}'"><i class="fas fa-info-circle"></i></button>
                            <button class="btn btn-success btn-circle btn-sm mx-1" title="Historia Médica" onclick="window.location.href='modulos/historia_medica_general.php?cedula=${encodeURIComponent(p.cedula)}'"><i class="fas fa-notes-medical"></i></button>
                            <button class="btn btn-danger btn-circle btn-sm mx-1" title="Eliminar" onclick="eliminarPaciente('${p.cedula}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
            // paginación with arrows
            let pagHtml = '';
            const total = data.total_paginas;
            const current = data.page;
            // Previous button
            pagHtml += `<li class="page-item${current === 1 ? ' disabled' : ''}">
                <a class="page-link" href="#" aria-label="Anterior" onclick="if(${current}>1){cargarPacientes('${busqueda}',${current-1}, false);}return false;">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>`;
            // Page buttons
            for(let i=1; i<=total; i++) {
                pagHtml += `<li class="page-item${i==current?' active':''}">
                    <a class="page-link" href="#" onclick="cargarPacientes('${busqueda}',${i}, false);return false;">${i}</a>
                </li>`;
            }
            // Next button
            pagHtml += `<li class="page-item${current === total ? ' disabled' : ''}">
                <a class="page-link" href="#" aria-label="Siguiente" onclick="if(${current}<${total}){cargarPacientes('${busqueda}',${current+1}, false);}return false;">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>`;
            document.getElementById('paginacion').innerHTML = pagHtml;
        })
        .catch(err => {
            if (mostrarCarga) cerrarCargando();
            const tbody = document.querySelector('#tablaPacientes tbody');
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error al cargar pacientes</td></tr>`;
        });
}

// Solo mostrar animación al cargar la página o cambiar de página
$('#busqueda').on('input', function() {
    cargarPacientes(this.value, 1, false); // No mostrar animación al buscar
});

function editarPaciente(p) {
    Swal.fire({
        title: 'Editar Paciente',
        html: `
            <form id="formEditarSwal">
                <input name="cedula" class="form-control mb-2" value="${p.cedula || ''}" readonly>
                <input name="pr_nombre" class="form-control mb-2" value="${p.pr_nombre || ''}" placeholder="Primer Nombre" required>
                <input name="pr_apellido" class="form-control mb-2" value="${p.pr_apellido || ''}" placeholder="Primer Apellido" required>
                <input name="fecha_nacimiento" type="date" class="form-control mb-2" value="${p.fecha_nacimiento || ''}" required>
                <input name="telefono" class="form-control mb-2" value="${p.telefono || ''}" placeholder="Teléfono">
                <input name="direccion" class="form-control mb-2" value="${p.direccion || ''}" placeholder="Dirección">
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar Cambios',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const form = Swal.getPopup().querySelector('#formEditarSwal');
            const formData = new FormData(form);
            formData.append('accion', 'editar');
            return fetch('/sisgeasic/backend/pacientes_backend.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(resp => {
                if (!resp.ok) throw new Error(resp.error || 'Error al editar');
                return resp;
            })
            .catch(error => {
                Swal.showValidationMessage(error.message);
            });
        }
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire('¡Actualizado!', 'Paciente editado correctamente.', 'success');
            cargarPacientes();
        }
    });
}

function eliminarPaciente(cedula) {
    Swal.fire({
        title: '¿Eliminar paciente?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarCargando('Eliminando...');
            fetch('/sisgeasic/backend/pacientes_backend.php', {
                method: 'POST',
                body: new URLSearchParams({accion:'eliminar', cedula})
            })
            .then (r => r.json())
            .then(resp => {
                cerrarCargando();
                if(resp.ok) {
                    Swal.fire('¡Eliminado!', 'Paciente eliminado correctamente.', 'success');
                    cargarPacientes();
                } else {
                    Swal.fire('Error', resp.error || 'No se pudo eliminar', 'error');
                }
            });
        };
    });
}
// Elimina la animación de carga automática al entrar al CRUD
window.onload = () => cargarPacientes('', 1, false); // No mostrar animación al cargar la página
</script>
