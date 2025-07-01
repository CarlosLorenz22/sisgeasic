<?php
header('Content-Type: application/json');

// Conexión directa a PostgreSQL
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
    exit;
}

try {
    $sql = "SELECT id_enfermedad, nombre, cedula, id_cita, id_consulta FROM enfermedad";
    $result = pg_query($conn, $sql);
    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }
    $enfermedades = [];
    while ($row = pg_fetch_assoc($result)) {
        $enfermedades[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $enfermedades]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener enfermedades: ' . $e->getMessage()]);
}
