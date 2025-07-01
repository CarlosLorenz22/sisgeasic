<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión directa a PostgreSQL
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
    exit;
}

$sql = "SELECT id_especialidad, nombre_especialidad, color, hora_inicio, hora_fin FROM especialidad";
$res = pg_query($conn, $sql);
$data = [];
while ($row = pg_fetch_assoc($res)) {
    $row['horario'] = '7:00am - 1:00pm'; // Horario fijo
    $data[] = $row;
}
echo json_encode(['success' => true, 'data' => $data]);
