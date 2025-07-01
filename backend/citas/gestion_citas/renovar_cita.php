<?php
// Conexión directa a PostgreSQL
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    die("No se pudo conectar a PostgreSQL: " . pg_last_error());
}

header('Content-Type: text/plain; charset=utf-8');

// Mostrar errores de PHP (solo para depuración, luego quítalo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
    $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';

    if ($id > 0 && $fecha_inicio && $fecha_fin) {
        // Corrige el nombre de la columna y el valor de status
        $sql = 'UPDATE "cita" SET fecha_inicio = $1, fecha_fin = $2, status = $3 WHERE id = $4';
        $result = pg_query_params($conn, $sql, array($fecha_inicio, $fecha_fin, 'pendiente', $id));
        if ($result) {
            echo "Cita actualizada correctamente";
        } else {
            echo "Error al actualizar la cita: " . pg_last_error($conn);
        }
    } else {
        echo "Datos incompletos para renovar la cita. id: $id, fecha_inicio: $fecha_inicio, fecha_fin: $fecha_fin";
    }
} else {
    echo "Método no permitido.";
}
?>
?>
