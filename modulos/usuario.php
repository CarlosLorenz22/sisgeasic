<?php
require_once __DIR__ . '/../backend/conexion.php';
// Consulta de médicos
$medicos = [];
$result = pg_query($conn, "SELECT id_medico, pr_nombre, sdo_nombre, pr_apellido, sdo_apellido FROM medico");
while ($row = pg_fetch_assoc($result)) {
    $medicos[] = $row;
}
?>
<!-- Gestion de usuarios -->
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Gestión de Usuarios</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
                            <button class="btn btn-primary btn-circle btn-sm" id="btnAgregarUsuario" title="Agregar Usuario">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3" style="max-width: 400px;">
                                <input type="text" id="searchInputUsuarios" class="form-control" placeholder="Buscar usuario">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tablaUsuarios" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <!-- <th>ID</th> --> <!-- Ocultamos la columna ID -->
                                            <th>Usuario</th>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Rol</th>
                                            <th>Sesión Activa</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usuarios-list">
                                        <!-- Se llena por JS -->
                                    </tbody>
                                </table>
                            </div>
                            <nav aria-label="Paginación">
                                <ul class="pagination justify-content-center" id="paginationUsuarios"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <script>
                    // --- Datos iniciales ---
                    let usuarios = [];
                    let roles = [];
                    let medicos = <?php echo json_encode($medicos); ?>;
                    let filteredUsuarios = [];
                    let currentPage = 1;
                    const rowsPerPage = 6;

                    // Cargar usuarios y roles por AJAX
                    async function cargarDatos() {
                        // Cargar usuarios
                        const resUsuarios = await fetch('backend/usuarios/usuarios_backend.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({accion: 'listar'})
                        });
                        const dataUsuarios = await resUsuarios.json();
                        usuarios = dataUsuarios.usuarios || [];
                        filteredUsuarios = [...usuarios];

                        // Cargar roles con manejo de error
                        let dataRoles = {};
                        try {
                            const resRoles = await fetch('backend/usuarios/roles_backend.php', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({accion: 'listar'})
                            });
                            if (!resRoles.ok) throw new Error('Error al cargar roles');
                            dataRoles = await resRoles.json();
                            roles = dataRoles.roles || [];
                        } catch (e) {
                            roles = [];
                            document.getElementById('usuarios-list').innerHTML = `<tr><td colspan="7" class="text-center text-danger">No se pudieron cargar los roles. Verifica el backend.</td></tr>`;
                            return;
                        }

                        renderUsuariosTable(currentPage);
                        renderUsuariosPagination();
                    }

                    function renderUsuariosTable(page) {
                        const start = (page - 1) * rowsPerPage;
                        const end = start + rowsPerPage;
                        const visibles = filteredUsuarios.slice(start, end);
                        const tbody = document.getElementById('usuarios-list');
                        tbody.innerHTML = '';
                        if (visibles.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="6" class="text-center">No hay usuarios registrados</td></tr>`;
                            return;
                        }
                        visibles.forEach(usuario => {
                            const rol = roles.find(r => r.id_rol == usuario.id_rol);
                            tbody.innerHTML += `
                                <tr>
                                    <!--<td>${usuario.id_usuario}</td>-->
                                    <td>${usuario.nombre_usuario}</td>
                                    <td>${usuario.nombre}</td>
                                    <td>${usuario.correo_electronico}</td>
                                    <td>${rol ? rol.nombre_rol : usuario.id_rol}</td>
                                    <td>${usuario.sesion_activa === 't' ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-danger">No</span>'}</td>
                                    <td>
                                        <button class="btn btn-info btn-circle btn-sm mx-1" title="Ver" onclick='verUsuario(${JSON.stringify(usuario)})'><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-warning btn-circle btn-sm mx-1" title="Editar" onclick='editarUsuario(${JSON.stringify(usuario)})'><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-circle btn-sm mx-1" title="Eliminar" onclick='eliminarUsuario(${usuario.id_usuario})'><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    function renderUsuariosPagination() {
                        const totalPages = Math.ceil(filteredUsuarios.length / rowsPerPage);
                        const pag = document.getElementById('paginationUsuarios');
                        pag.innerHTML = '';
                        if (totalPages > 1) {
                            const prevLi = document.createElement('li');
                            prevLi.className = `page-item${currentPage === 1 ? ' disabled' : ''}`;
                            prevLi.innerHTML = `<a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a>`;
                            if (currentPage > 1) prevLi.onclick = () => { currentPage--; renderUsuariosTable(currentPage); renderUsuariosPagination(); };
                            pag.appendChild(prevLi);
                            for (let i = 1; i <= totalPages; i++) {
                                const li = document.createElement('li');
                                li.className = `page-item${i === currentPage ? ' active' : ''}`;
                                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                                if (i !== currentPage) li.onclick = () => { currentPage = i; renderUsuariosTable(currentPage); renderUsuariosPagination(); };
                                pag.appendChild(li);
                            }
                            const nextLi = document.createElement('li');
                            nextLi.className = `page-item${currentPage === totalPages ? ' disabled' : ''}`;
                            nextLi.innerHTML = `<a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>`;
                            if (currentPage < totalPages) nextLi.onclick = () => { currentPage++; renderUsuariosTable(currentPage); renderUsuariosPagination(); };
                            pag.appendChild(nextLi);
                        }
                    }

                    document.getElementById('searchInputUsuarios').addEventListener('input', e => {
                        const q = e.target.value.toLowerCase();
                        filteredUsuarios = usuarios.filter(u =>
                            (u.nombre_usuario && u.nombre_usuario.toLowerCase().includes(q)) ||
                            (u.nombre && u.nombre.toLowerCase().includes(q)) ||
                            (u.correo_electronico && u.correo_electronico.toLowerCase().includes(q))
                        );
                        currentPage = 1;
                        renderUsuariosTable(currentPage);
                        renderUsuariosPagination();
                    });

                    // Llama a cargarDatos al inicio y deshabilita el botón hasta que termine
                    document.getElementById('btnAgregarUsuario').disabled = true;
                    cargarDatos().then(() => {
                        document.getElementById('btnAgregarUsuario').disabled = false;
                    });

                    // --- CRUD JS ---
                    document.getElementById('btnAgregarUsuario').onclick = () => {
                        Swal.fire({
                            title: 'Agregar Usuario',
                            html: `
                                <form id="formAgregarUsuario">
                                    <input class="form-control mb-2" placeholder="Usuario" id="nuevoUsuario" required>
                                    <input class="form-control mb-2" placeholder="Nombre completo" id="nuevoNombre" required>
                                    <input class="form-control mb-2" placeholder="Correo" id="nuevoCorreo" type="email">
                                    <input class="form-control mb-2" placeholder="Contraseña" id="nuevoPass" type="password" required>
                                    <select class="form-control mb-2" id="nuevoRol" required>
                                        <option value="">Seleccione rol</option>
                                        ${roles.map(r => `<option value="${r.id_rol}">${r.nombre_rol}</option>`).join('')}
                                    </select>
                                    <div id="divMedicoSelect" style="display:none;">
                                        <input class="form-control mb-2" id="buscarMedicoInput" placeholder="Buscar médico...">
                                        <select class="form-control mb-2" id="nuevoMedico">
                                            <option value="">Seleccione médico</option>
                                        </select>
                                    </div>
                                </form>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Guardar',
                            cancelButtonText: 'Cancelar',
                            didOpen: () => {
                                // Función para obtener el nombre completo del médico
                                function getNombreCompleto(m) {
                                    return [m.pr_nombre, m.sdo_nombre, m.pr_apellido, m.sdo_apellido]
                                        .filter(Boolean).join(' ');
                                }

                                // Renderiza el select de médicos según filtro
                                function renderMedicosSelect(filtro = '') {
                                    const selectMedico = document.getElementById('nuevoMedico');
                                    selectMedico.innerHTML = '<option value="">Seleccione médico</option>';
                                    let encontrados = 0;
                                    medicos.forEach(m => {
                                        // Asegúrate que los campos existen y no son undefined
                                        const nombreCompleto = [
                                            m.pr_nombre || '',
                                            m.sdo_nombre || '',
                                            m.pr_apellido || '',
                                            m.sdo_apellido || ''
                                        ].join(' ').replace(/\s+/g, ' ').trim().toLowerCase();
                                        if (nombreCompleto && nombreCompleto.includes(filtro)) {
                                            const opt = document.createElement('option');
                                            opt.value = m.id_medico;
                                            opt.textContent = nombreCompleto;
                                            selectMedico.appendChild(opt);
                                            encontrados++;
                                        }
                                    });
                                    if (medicos.length === 0) {
                                        const opt = document.createElement('option');
                                        opt.value = "";
                                        opt.textContent = "No hay médicos registrados";
                                        selectMedico.appendChild(opt);
                                    } else if (encontrados === 0) {
                                        const opt = document.createElement('option');
                                        opt.value = "";
                                        opt.textContent = "No se encontró ningún médico";
                                        selectMedico.appendChild(opt);
                                    }
                                }

                                // Mostrar el select de médico si el rol es "Médico" o "Doctor"
                                document.getElementById('nuevoRol').addEventListener('change', function() {
                                    const rolSeleccionado = this.options[this.selectedIndex].text.toLowerCase();
                                    if (rolSeleccionado.includes('medico') || rolSeleccionado.includes('doctor')) {
                                        document.getElementById('divMedicoSelect').style.display = '';
                                        // Limpiar buscador y select cada vez que se muestra
                                        document.getElementById('buscarMedicoInput').value = '';
                                        document.getElementById('nuevoMedico').value = '';
                                        renderMedicosSelect(''); // Renderiza todos los médicos al mostrar el campo
                                    } else {
                                        document.getElementById('divMedicoSelect').style.display = 'none';
                                        document.getElementById('nuevoMedico').value = '';
                                        document.getElementById('buscarMedicoInput').value = '';
                                    }
                                });

                                // Buscador de médicos
                                const buscarMedicoInput = document.getElementById('buscarMedicoInput');
                                if (buscarMedicoInput) {
                                    buscarMedicoInput.addEventListener('input', function() {
                                        renderMedicosSelect(this.value.toLowerCase());
                                    });
                                }
                            },
                            preConfirm: () => {
                                const usuario = document.getElementById('nuevoUsuario').value.trim();
                                const nombre = document.getElementById('nuevoNombre').value.trim();
                                const correo = document.getElementById('nuevoCorreo').value.trim();
                                const contrasena = document.getElementById('nuevoPass').value;
                                const id_rol = document.getElementById('nuevoRol').value;
                                const rolSeleccionado = document.getElementById('nuevoRol').options[document.getElementById('nuevoRol').selectedIndex].text.toLowerCase();
                                let id_medico = null;
                                if (rolSeleccionado.includes('medico') || rolSeleccionado.includes('doctor')) {
                                    id_medico = document.getElementById('nuevoMedico').value;
                                    if (!id_medico) {
                                        Swal.showValidationMessage('Debe seleccionar un médico');
                                        return false;
                                    }
                                }
                                if (!usuario || !nombre || !contrasena || !id_rol) {
                                    Swal.showValidationMessage('Todos los campos excepto correo son obligatorios');
                                    return false;
                                }
                                const data = {
                                    accion: 'agregar',
                                    nombre_usuario: usuario,
                                    nombre: nombre,
                                    correo_electronico: correo,
                                    contrasena: contrasena,
                                    id_rol: id_rol,
                                    id_medico: id_medico
                                };
                                return fetch('backend/usuarios/agregar_usuario.php', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/json'},
                                    body: JSON.stringify(data)
                                }).then(r => r.json()).then(res => {
                                    if (res.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Usuario agregado',
                                            text: 'El usuario se agregó correctamente',
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            location.reload();
                                        });
                                        // Para navegadores que no esperan el .then de SweetAlert2:
                                        setTimeout(() => location.reload(), 1600);
                                    } else {
                                        throw new Error(res.error || 'No se pudo agregar');
                                    }
                                }).catch(e => Swal.showValidationMessage(e.message));
                            }
                        });
                    };

                    function editarUsuario(usuario) {
                        Swal.fire({
                            title: 'Editar Usuario',
                            html: `
                                <form id="formEditarUsuario">
                                    <input class="form-control mb-2" value="${usuario.nombre_usuario}" id="editUsuario" required>
                                    <input class="form-control mb-2" value="${usuario.nombre}" id="editNombre" required>
                                    <input class="form-control mb-2" value="${usuario.correo_electronico}" id="editCorreo" type="email" required>
                                    <input class="form-control mb-2" placeholder="Nueva contraseña (opcional)" id="editPass" type="password">
                                    <select class="form-control mb-2" id="editRol" required>
                                        ${roles.map(r => `<option value="${r.id_rol}" ${r.id_rol == usuario.id_rol ? 'selected' : ''}>${r.nombre_rol}</option>`).join('')}
                                    </select>
                                    <label><input type="checkbox" id="editSesionActiva" ${usuario.sesion_activa === 't' ? 'checked' : ''}> Sesión activa</label>
                                </form>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Guardar',
                            cancelButtonText: 'Cancelar',
                            preConfirm: () => {
                                const data = {
                                    accion: 'editar',
                                    id_usuario: usuario.id_usuario,
                                    nombre_usuario: document.getElementById('editUsuario').value,
                                    nombre: document.getElementById('editNombre').value,
                                    correo_electronico: document.getElementById('editCorreo').value,
                                    contrasena: document.getElementById('editPass').value,
                                    id_rol: document.getElementById('editRol').value,
                                    sesion_activa: document.getElementById('editSesionActiva').checked ? 1 : 0
                                };
                                return fetch('backend/usuarios/usuarios_backend.php', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/json'},
                                    body: JSON.stringify(data)
                                }).then(r => r.json()).then(res => {
                                    if (res.success) location.reload();
                                    else throw new Error(res.error || 'No se pudo editar');
                                }).catch(e => Swal.showValidationMessage(e.message));
                            }
                        });
                    }

                    function eliminarUsuario(id) {
                        Swal.fire({
                            title: '¿Eliminar usuario?',
                            text: 'Esta acción no se puede deshacer.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then(res => {
                            if (res.isConfirmed) {
                                fetch('backend/usuarios/usuarios_backend.php', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/json'},
                                    body: JSON.stringify({accion: 'eliminar', id_usuario: id})
                                }).then(r => r.json()).then(res => {
                                    if (res.success) location.reload();
                                    else Swal.fire('Error', res.error || 'No se pudo eliminar', 'error');
                                });
                            }
                        });
                    }

                    function verUsuario(usuario) {
                        const rol = roles.find(r => r.id_rol == usuario.id_rol);
                        Swal.fire({
                            title: 'Detalles del Usuario',
                            html: `
                                <p><b>ID:</b> ${usuario.id_usuario}</p>
                                <p><b>Usuario:</b> ${usuario.nombre_usuario}</p>
                                <p><b>Nombre:</b> ${usuario.nombre}</p>
                                <p><b>Correo:</b> ${usuario.correo_electronico}</p>
                                <p><b>Rol:</b> ${rol ? rol.nombre_rol : usuario.id_rol}</p>
                                <p><b>Sesión activa:</b> ${usuario.sesion_activa === 't' ? 'Sí' : 'No'}</p>
                            `,
                            icon: 'info'
                        });
                    }
                </script>
            </div>
        </div>
    </div>
    </div>
            </div>
        </div>
    </div>
    </div>
