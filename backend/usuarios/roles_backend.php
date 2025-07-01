<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

// Verifica que la conexión exista y sea válida
if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'error' => 'No se pudo conectar a la base de datos']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (isset($input['accion']) && $input['accion'] === 'listar') {
    $sql = "SELECT * FROM rol_usuario"; // <-- Cambia aquí el nombre de la tabla
    $result = pg_query($conn, $sql);
    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Error en la consulta de roles: ' . pg_last_error($conn)]);
        exit;
    }
    $roles = [];
    while ($row = pg_fetch_assoc($result)) {
        $roles[] = $row;
    }
    echo json_encode(['success' => true, 'roles' => $roles]);
    exit;
}
echo json_encode(['success' => false, 'error' => 'Acción no válida']);
