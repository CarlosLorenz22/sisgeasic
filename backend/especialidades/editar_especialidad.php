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

$id = $_POST['id_especialidad'] ?? '';
$nombre = $_POST['nombre_especialidad'] ?? null;
$color = $_POST['color'] ?? null;
$hora_inicio = $_POST['hora_inicio'] ?? null;
$hora_fin = $_POST['hora_fin'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

$campos = [];
$params = [];
$i = 1;

if ($nombre !== null) {
    $campos[] = "nombre_especialidad = $" . $i++;
    $params[] = $nombre;
}
if ($color !== null) {
    $campos[] = "color = $" . $i++;
    $params[] = $color;
}
if ($hora_inicio !== null) {
    $campos[] = "hora_inicio = $" . $i++;
    $params[] = $hora_inicio;
}
if ($hora_fin !== null) {
    $campos[] = "hora_fin = $" . $i++;
    $params[] = $hora_fin;
}

if (empty($campos)) {
    echo json_encode(['success' => false, 'message' => 'Nada que actualizar']);
    exit;
}

$params[] = $id;
$sql = "UPDATE especialidad SET " . implode(', ', $campos) . " WHERE id_especialidad = $" . $i;
$res = pg_query_params($conn, $sql, $params);

if ($res) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar especialidad']);
}
