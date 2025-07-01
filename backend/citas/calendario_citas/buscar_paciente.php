<?php
header('Content-Type: application/json');

if (empty($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q']);
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode([]);
    exit;
}

// Buscar por cédula exacta o nombre aproximado
if (is_numeric($q)) {
    $sql = "SELECT pr_nombre, sdo_nombre, pr_apellido, sdo_apellido FROM paciente WHERE cedula = $1 LIMIT 1";
    $result = pg_query_params($conn, $sql, [$q]);
} else {
    $sql = "SELECT pr_nombre, sdo_nombre, pr_apellido, sdo_apellido FROM paciente WHERE 
        pr_nombre ILIKE $1 OR sdo_nombre ILIKE $1 OR pr_apellido ILIKE $1 OR sdo_apellido ILIKE $1 LIMIT 1";
    $result = pg_query_params($conn, $sql, ["%$q%"]);
}

if ($result && $row = pg_fetch_assoc($result)) {
    $nombre = trim($row['pr_nombre'] . ' ' . $row['sdo_nombre'] . ' ' . $row['pr_apellido'] . ' ' . $row['sdo_apellido']);
    echo json_encode(['nombre' => $nombre]);
} else {
    echo json_encode([]);
}
pg_close($conn);
