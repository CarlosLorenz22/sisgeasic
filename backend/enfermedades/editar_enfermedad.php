<?php
header('Content-Type: application/json');

// Conexión directa a PostgreSQL
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
    exit;
}

$id = isset($_POST['id_enfermedad']) ? intval($_POST['id_enfermedad']) : 0;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if ($id <= 0 || $nombre === '') {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $sql = "UPDATE enfermedad SET nombre = $1 WHERE id_enfermedad = $2";
    $result = pg_query_params($conn, $sql, [$nombre, $id]);
    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }
    echo json_encode(['success' => true, 'message' => 'Enfermedad actualizada correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar enfermedad: ' . $e->getMessage()]);
}
