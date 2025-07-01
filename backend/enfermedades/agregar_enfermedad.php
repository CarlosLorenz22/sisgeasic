<?php
header('Content-Type: application/json');

// Conexión directa a PostgreSQL
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
    exit;
}

// Validar y obtener el nombre
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if ($nombre === '') {
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
    exit;
}

try {
    // Insertar la enfermedad
    $sql = "INSERT INTO enfermedad (nombre) VALUES ($1)";
    $result = pg_query_params($conn, $sql, [$nombre]);
    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }
    echo json_encode(['success' => true, 'message' => 'Enfermedad agregada correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al agregar enfermedad: ' . $e->getMessage()]);
}
