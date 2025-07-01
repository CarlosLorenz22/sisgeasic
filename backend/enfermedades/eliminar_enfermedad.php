<?php
header('Content-Type: application/json');

// Conexión directa a PostgreSQL
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
    exit;
}

$id = isset($_POST['id_enfermedad']) ? intval($_POST['id_enfermedad']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $sql = "DELETE FROM enfermedad WHERE id_enfermedad = $1";
    $result = pg_query_params($conn, $sql, [$id]);
    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }
    echo json_encode(['success' => true, 'message' => 'Enfermedad eliminada correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar enfermedad: ' . $e->getMessage()]);
}
