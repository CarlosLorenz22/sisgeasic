<?php
// Iniciar sesión
session_start();

// Conexión a la base de datos
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . pg_last_error());
}

// Generar CAPTCHA solo si no existe en la sesión
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
}

// Manejar la validaci  ón de credenciales mediante AJAX
if (isset($_POST['ajax']) && $_POST['ajax'] === 'validate') {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];

    // Consulta para verificar las credenciales
    $sql = "SELECT * FROM usuario WHERE nombre_usuario = $1 AND contraseña = $2";
    $result = pg_query_params($conn, $sql, array($nombre_usuario, $contrasena));

    if (pg_num_rows($result) > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Manejar el inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['ajax'])) {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $captcha = $_POST['captcha'];

    // Validar CAPTCHA
    if ($_SESSION['captcha'] !== $captcha) {
        $error = "El código CAPTCHA es incorrecto.";
    } else {
        // Limpiar el CAPTCHA después de validarlo correctamente
        unset($_SESSION['captcha']);

        // Consulta para verificar las credenciales y obtener el id_rol directamente de usuario
        $sql = "SELECT * FROM usuario WHERE nombre_usuario = $1 AND contraseña = $2";
        $result = pg_query_params($conn, $sql, array($nombre_usuario, $contrasena));

        if ($result && pg_num_rows($result) > 0) {
            // Credenciales correctas
            $row = pg_fetch_assoc($result);
            $_SESSION['usuario'] = $nombre_usuario;
            $_SESSION['id_rol'] = $row['id_rol'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['correo_electronico'] = $row['correo_electronico'];

            // Depuración: imprime el id_usuario obtenido
            if (!isset($row['id_usuario'])) {
                die("<div style='color:red;'>Error: El campo id_usuario no existe en la tabla usuario o no se está retornando en la consulta.</div>");
            }
            // echo "<!-- id_usuario del usuario: " . $row['id_usuario'] . " -->";

            // Si es médico, buscar y guardar el id_medico en la sesión
            if ($row['id_rol'] == 2) {
                $id_usuario = $row['id_usuario'];
                // Depuración: imprime el id_usuario que se usará en la consulta
                // echo "<!-- id_usuario usado para buscar medico: $id_usuario -->";
                $sql_medico = "SELECT id_medico FROM medico WHERE id_usuario = $1";
                $result_medico = pg_query_params($conn, $sql_medico, array($id_usuario));
                if ($result_medico && pg_num_rows($result_medico) > 0) {
                    $row_medico = pg_fetch_assoc($result_medico);
                    $_SESSION['id_medico'] = $row_medico['id_medico'];
                    // Depuración: imprime el id_medico asignado
                    // echo "<!-- id_medico asignado: " . $_SESSION['id_medico'] . " -->";
                } else {
                    $_SESSION['id_medico'] = 0;
                    // echo "<!-- No se encontró id_medico para id_usuario: $id_usuario -->";
                }
            }

            // Actualizar la columna sesion_activa a true
            $sql_update_session = "UPDATE usuario SET sesion_activa = true WHERE nombre_usuario = $1";
            pg_query_params($conn, $sql_update_session, array($nombre_usuario));

            header("Location: ../dashboard.php?modulo=inicio"); // Redirigir al dashboard médico
            exit();
        } else {
            // Credenciales incorrectas
            $error = "Nombre de usuario o contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SisgeAsic - Iniciar Sesión</title>

    <!-- Fuentes personalizadas para esta plantilla -->
    <script src="https://kit.fontawesome.com/7a4cab1fbd.js" crossorigin="anonymous"></script>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="/sisgeasic/assets/fontawesome/css/all.min.css">    
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/img/logo2.png">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Estilos personalizados para esta plantilla -->
    <link href="../assets/css/sb-admin-2.css" rel="stylesheet">

    <style>
        .icon-transition {
            transition: transform 0.5s ease, color 0.5s ease;
        }
        .icon-unlocked {
            transform: rotate(20deg);
            color:rgb(41, 204, 0); /* Verde más resaltante */
        }
        .icon-correct {
            color:rgb(41, 204, 0); /* Verde más resaltante */
        }
        .captcha-image {
            font-family: 'Arial', sans-serif;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 3px;
            background-color: #f2f2f2;
            padding: 10px;
            display: inline-block;
            border-radius: 5px;
        }
    </style>

</head>

<body class="bg-gradient-primary d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="container">
        <!-- Fila externa -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <!-- Fila anidada dentro del cuerpo de la tarjeta -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image" style="background-image: url(centromedico2.jpg); background-size: cover; background-position: center;"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <img src="../assets/img/logo1.png" alt="Logo" style="width: 120px; height: auto; margin-bottom: 15px;">
                                        <h1 class="h4 text-gray-900 mb-4">¡Bienvenido a SisgeAsic!</h1>
                                        <?php if (isset($error)): ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?php echo $error; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <form class="user" method="POST" action="">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i id="user-icon" class="fa-solid fa-user icon-transition"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-user" id="username" name="nombre_usuario"
                                                    placeholder="Introduce tu nombre de usuario..." required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i id="lock-icon" class="fa-solid fa-lock icon-transition"></i>
                                                    </span>
                                                </div>
                                                <input type="password" class="form-control form-control-user" id="password" name="contrasena"
                                                    placeholder="Contraseña" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i id="toggle-password" class="fa-solid fa-eye icon-transition" style="cursor: pointer;"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <!----- Generador de Capcha ------>
                                        <div class="form-group">
                                            <div class="captcha-container text-center">
                                                <span class="captcha-image"><?php echo $_SESSION['captcha']; ?></span>
                                                <button type="button" class="btn btn-light btn-sm" onclick="location.reload();">
                                                    <i class="fa-solid fa-rotate"></i>
                                                </button>
                                            </div>
                                            <input type="text" class="form-control form-control-user mt-2" name="captcha" placeholder="Escribe el código de seguridad..." required>
                                        </div>
                                        <!-------------------------------->
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Iniciar Sesión
                                        </button>
                                    </form>
                                    <div class="text-center">
                                        <a class="small" href="#">¿Olvidaste tu contraseña?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="#">¡Crea una cuenta!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- JavaScript principal de Bootstrap -->
    <script src="/sisgeasic/assets/vendor/jquery/jquery.min.js"></script>
    <script src="/sisgeasic/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Complemento principal de JavaScript -->
    <script src="/sisgeasic/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Scripts personalizados para todas las páginas -->
    <script src="/sisgeasic/assets/js/sb-admin-2.min.js"></script>
    <script>
        function validateCredentials() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const userIcon = document.getElementById('user-icon');
            const lockIcon = document.getElementById('lock-icon');

            if (username && password) {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        ajax: 'validate',
                        nombre_usuario: username,
                        contrasena: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        userIcon.classList.add('icon-correct');
                        lockIcon.classList.remove('fa-lock');
                        lockIcon.classList.add('fa-unlock', 'icon-unlocked');
                    } else {
                        userIcon.classList.remove('icon-correct');
                        lockIcon.classList.remove('fa-unlock', 'icon-unlocked');
                        lockIcon.classList.add('fa-lock');
                    }
                });
            } else {
                // Restaurar íconos a su estado inicial si los campos están vacíos
                userIcon.classList.remove('icon-correct');
                lockIcon.classList.remove('fa-unlock', 'icon-unlocked');
                lockIcon.classList.add('fa-lock');
            }
        }

        // Mostrar/ocultar contraseña con animación
        const passwordInput = document.getElementById('password');
        const togglePasswordIcon = document.getElementById('toggle-password');

        togglePasswordIcon.addEventListener('click', function () {
            const isPasswordVisible = passwordInput.type === 'text';
            passwordInput.type = isPasswordVisible ? 'password' : 'text';

            // Cambiar el ícono y animación
            togglePasswordIcon.classList.toggle('fa-eye', isPasswordVisible);
            togglePasswordIcon.classList.toggle('fa-eye-slash', !isPasswordVisible);
        });

        document.getElementById('username').addEventListener('input', validateCredentials);
        document.getElementById('password').addEventListener('input', validateCredentials);
    </script>

</body>

</html>