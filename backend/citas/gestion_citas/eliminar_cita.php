<?php
// Conexión directa a PostgreSQL
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'No se pudo conectar a PostgreSQL: ' . pg_last_error()]);
    exit;
}
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id > 0) {
        $sql = 'DELETE FROM "cita" WHERE id = $1';
        $result = pg_query_params($conn, $sql, array($id));
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
