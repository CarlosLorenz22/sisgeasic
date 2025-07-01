<?php
header('Content-Type: application/json');

$host = 'localhost';
$port = '5432';
$dbname = 'bd_asic';
$user = 'postgres';
$password = '30429913';

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Recibe la cédula por JSON
$input = json_decode(file_get_contents('php://input'), true);
$cedula = $input['cedula'] ?? null;

if (!$cedula) {
    echo json_encode(['success' => false, 'error' => 'Cédula no proporcionada']);
    exit;
}

// Buscar el id_consulta más reciente para esa cédula
$sql_id = "SELECT id_consulta FROM consulta WHERE cedula = $1 ORDER BY fecha_consulta DESC LIMIT 1";
$res_id = pg_query_params($conn, $sql_id, [$cedula]);
$row = pg_fetch_assoc($res_id);

if (!$row || !isset($row['id_consulta'])) {
    echo json_encode(['success' => false, 'error' => 'No se encontró la consulta para eliminar']);
    exit;
}

$id_consulta = $row['id_consulta'];

// Eliminar la consulta por id_consulta
$sql = "DELETE FROM consulta WHERE id_consulta = $1";
$result = pg_query_params($conn, $sql, [$id_consulta]);

if ($result && pg_affected_rows($result) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se pudo eliminar la consulta']);
}
