<?php
header('Content-Type: application/json');

// Conexión directa a PostgreSQL
$host = 'localhost';
$port = '5432';
$db = 'sisgeasic'; // Cambia por el nombre de tu base de datos
$user = 'postgres'; // Cambia por tu usuario de postgres
$pass = ''; // Cambia por tu contraseña

$connStr = "host=$host port=$port dbname=$db user=$user password=$pass";
$conn = pg_connect($connStr);

if (!$conn) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$accion = isset($input['accion']) ? $input['accion'] : '';

if ($accion === 'listar') {
    $sql = "SELECT id_medico, 
                   TRIM(
                       pr_nombre || 
                       CASE WHEN sdo_nombre IS NOT NULL AND sdo_nombre <> '' THEN ' ' || sdo_nombre ELSE '' END || 
                       ' ' || pr_apellido || 
                       CASE WHEN sdo_apellido IS NOT NULL AND sdo_apellido <> '' THEN ' ' || sdo_apellido ELSE '' END
                   ) AS nombre
            FROM medico";
    $result = pg_query($conn, $sql);
    $medicos = [];
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $medicos[] = $row;
        }
    }
    echo json_encode(['medicos' => $medicos]);
    pg_close($conn);
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
pg_close($conn);
