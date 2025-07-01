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
    echo json_encode([]);
    exit;
}

$query = "SELECT id_enfermedad, nombre FROM enfermedad ORDER BY nombre ASC";
$result = pg_query($conn, $query);

$enfermedades = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $enfermedades[] = $row;
    }
}

echo json_encode($enfermedades);
