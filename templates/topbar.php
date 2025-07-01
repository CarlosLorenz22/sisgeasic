<!-- Barra superior -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Botón para colapsar la barra lateral -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <!-- Menú de usuario -->
    <ul class="navbar-nav ml-auto">
        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?php echo isset($nombre_usuario_topbar) ? htmlspecialchars($nombre_usuario_topbar) : 'Usuario'; ?>
                </span>
                <img class="img-profile rounded-circle" src="/sisgeasic/assets/img/undraw_profile.svg" id="user-avatar">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                 aria-labelledby="userDropdown">
                <!-- Opciones del usuario -->
                <a class="dropdown-item" href="#" id="btn-editar-perfil">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Perfil
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Configuración
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Actividad
                </a>
                <div class="dropdown-divider"></div>
                <!-- Cambia el href para activar el modal -->
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Cerrar Sesión
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- Modal para cerrar sesión -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">¿Listo para salir?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">Seleccione "Cerrar Sesión" a continuación para finalizar su sesión actual.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <!-- Ahora redirige correctamente al logout -->
                <a class="btn btn-primary" href="/sisgeasic/logout.php?logout=true">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btn-editar-perfil').addEventListener('click', function(e) {
        e.preventDefault();

        // Puedes obtener estos valores de la sesión vía PHP si lo deseas
        const nombre_usuario = "<?php echo isset($_SESSION['nombre_usuario']) ? htmlspecialchars($_SESSION['nombre_usuario']) : ''; ?>";
        const correo = "<?php echo isset($_SESSION['correo_electronico']) ? htmlspecialchars($_SESSION['correo_electronico']) : ''; ?>";
        const nombre = "<?php echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : ''; ?>";

        Swal.fire({
            title: 'Editar Perfil',
            html: `
                <form id="form-editar-perfil" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Foto de perfil</label><br>
                        <input type="file" name="foto" accept="image/*" class="form-control" style="padding:3px;">
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="${nombre}">
                    </div>
                    <div class="form-group">
                        <label>Nombre de usuario</label>
                        <input type="text" name="nombre_usuario" class="form-control" value="${nombre_usuario}">
                    </div>
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="correo_electronico" class="form-control" value="${correo}">
                    </div>
                    <div class="form-group">
                        <label>Nueva contraseña</label>
                        <div style="position:relative;">
                            <input type="password" name="contrasena" class="form-control" id="swal-password" placeholder="Dejar en blanco para no cambiar">
                            <span id="swal-toggle-password" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;">
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            preConfirm: () => {
                const form = document.getElementById('form-editar-perfil');
                const formData = new FormData(form);

                return fetch('/sisgeasic/backend/usuarios/editar_perfil.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Error al actualizar el perfil');
                    }
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(error.message);
                });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value.success) {
                Swal.fire('¡Actualizado!', 'Tu perfil ha sido actualizado.', 'success').then(() => {
                    location.reload();
                });
            }
        });
    });

    // Mostrar/ocultar contraseña en el modal SweetAlert2
    document.addEventListener('click', function(e) {
        if (e.target.closest('#swal-toggle-password')) {
            const input = document.getElementById('swal-password');
            const icon = e.target.closest('#swal-toggle-password').querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    });
});
</script>