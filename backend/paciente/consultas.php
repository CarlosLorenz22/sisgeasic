<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../conexion.php';

$cedula = $_GET['cedula'] ?? '';
$consultas = [];

if ($cedula && $conn) {
    $sql = "SELECT id, fecha, medico, motivo, diagnostico, tratamiento
            FROM historial_consultas
            WHERE cedula = $1
            ORDER BY fecha DESC";
    $result = pg_query_params($conn, $sql, [$cedula]);
    while ($row = pg_fetch_assoc($result)) {
        $consultas[] = [
            'fecha' => $row['fecha'],
            'medico' => $row['medico'],
            'motivo' => $row['motivo'],
            'diagnostico' => $row['diagnostico'],
            'tratamiento' => $row['tratamiento']
        ];
    }
}

echo json_encode(['ok' => true, 'consultas' => $consultas]);
