<?php
header('Content-Type: application/json');

$conn = pg_connect("host=localhost port=5432 dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión']);
    exit;
}

// Recoge los datos del formulario
$cedula = $_POST['cedula'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$id_especialidad = $_POST['id_especialidad'] ?? '';
$id_medico = $_POST['id_medico'] ?? '';
$fecha_consulta = $_POST['fecha_consulta'] ?? '';

if (!$cedula || !$motivo || !$id_especialidad || !$id_medico || !$fecha_consulta) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Inserta la consulta
$sql = "INSERT INTO consulta (cedula, motivo, id_especialidad, id_medico, fecha_consulta, primera_vez)
        VALUES ($1, $2, $3, $4, $5, true)";
$result = pg_query_params($conn, $sql, [$cedula, $motivo, $id_especialidad, $id_medico, $fecha_consulta]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
}
