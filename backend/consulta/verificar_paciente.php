<?php
// Conexión a la base de datos
$host = 'localhost';
$port = '5432';
$dbname = 'bd_asic';
$user = 'postgres';
$password = '30429913';

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

// Obtener la cédula del paciente
$cedula = $_GET['cedula'] ?? null;

if (!$cedula) {
    die(json_encode(['error' => 'Cédula no proporcionada']));
}

// Verificar existencia del paciente
$query = "SELECT cedula FROM paciente WHERE cedula = $1";
$result = pg_query_params($conn, $query, [$cedula]);

if (!$result) {
    die(json_encode(['error' => 'Error al ejecutar la consulta']));
}

$existe = pg_num_rows($result) > 0;

header('Content-Type: application/json');
echo json_encode(['existe' => $existe]);
