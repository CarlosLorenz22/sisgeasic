<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

include_once '../../config/db.php';

// Verifica la conexión
if (!$conn) {
    error_log('Error de conexión a la base de datos: ' . pg_last_error());
    echo json_encode(['error' => 'No se pudo conectar a la base de datos.']);
    exit;
}

// Consulta para obtener las consultas
$query = "SELECT c.id_consulta, c.cedula, c.id_medico, c.id_especialidad, c.fecha_consulta, c.motivo, c.primera_vez 
          FROM consulta c 
          ORDER BY c.fecha_consulta DESC";

$result = pg_query($conn, $query);

if ($result) {
    $consultas = pg_fetch_all($result);
    echo json_encode($consultas ?? []);
} else {
    error_log('Error al ejecutar la consulta: ' . pg_last_error());
    echo json_encode(['error' => 'No se pudieron obtener las consultas.']);
}
?>
