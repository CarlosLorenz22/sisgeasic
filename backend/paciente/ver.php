<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/../conexion.php'; // Debe definir $conn con pg_connect

$cedula = $_GET['cedula'] ?? '';
$resp = ['ok' => false];

if (!$conn) {
    $resp['error'] = 'No se pudo establecer la conexión con la base de datos.';
} elseif (!$cedula) {
    $resp['error'] = 'Cédula no especificada';
} else {
    $sql = "SELECT p.*, 
                e.nombre AS nombre_estado, 
                m.nombre AS nombre_municipio, 
                pa.nombre AS nombre_parroquia
            FROM paciente p
            LEFT JOIN estado e ON p.id_estado = e.id_estado
            LEFT JOIN municipio m ON p.id_municipio = m.id_municipio
            LEFT JOIN parroquia pa ON p.id_parroquia = pa.id_parroquia
            WHERE p.cedula = $1";
    $result = pg_query_params($conn, $sql, [$cedula]);
    if ($result && $row = pg_fetch_assoc($result)) {
        $resp['ok'] = true;
        $resp['paciente'] = $row;
    } else {
        $resp['error'] = "Paciente no encontrado";
    }
}

echo json_encode($resp);
