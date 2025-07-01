<?php
header('Content-Type: application/json');

// ...conexión a la base de datos...
$host = 'localhost';
$port = '5432';
$dbname = 'bd_asic';
$user = 'postgres';
$password = '30429913';

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Recibir datos POST
$cedula = $_POST['cedula'] ?? null;
$fecha_consulta = $_POST['fecha_consulta'] ?? null;
$observaciones = $_POST['observaciones'] ?? null;
$diagnostico = $_POST['diagnostico'] ?? null;
$tratamiento = $_POST['tratamiento'] ?? null;
$id_enfermedad = $_POST['id_enfermedad'] ?? null;
$id_consulta = $_POST['id_consulta'] ?? null;

// Validar campos obligatorios
if (!$id_consulta || !$cedula || !$fecha_consulta || !$observaciones || !$diagnostico || !$tratamiento) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
    exit;
}

// Insertar en historial_consultas
$query = "INSERT INTO historial_consultas (id_consulta, cedula, fecha_consulta, observaciones, diagnostico, tratamiento, id_enfermedad)
          VALUES ($1, $2, $3, $4, $5, $6, $7)";
$params = [
    $id_consulta,
    $cedula,
    $fecha_consulta,
    $observaciones,
    $diagnostico,
    $tratamiento,
    $id_enfermedad ?: null // Puede ser null si no se selecciona enfermedad
];

$result = pg_query_params($conn, $query, $params);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar en el historial']);
}
