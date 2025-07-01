<?php
session_start();
if (isset($_SESSION['nombre_usuario'])) {
    // Conexión a la base de datos
    $conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
    if ($conn) {
        $sql_update = "UPDATE usuario SET sesion_activa = false WHERE nombre_usuario = $1";
        pg_query_params($conn, $sql_update, array($_SESSION['nombre_usuario']));
    }
}
session_unset();
session_destroy();
echo '<script>window.location.href="/sisgeasic/login/login.php";</script>';
exit();
?>
