<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../conexion.php';

$municipio_id = $_GET['municipio_id'] ?? '';
$parroquias = [];

if ($municipio_id && isset($conn)) {
    $res = pg_query_params($conn, "SELECT id_parroquia, nombre FROM parroquia WHERE municipio_id = $1 ORDER BY nombre", [$municipio_id]);
    if ($res) {
        while ($row = pg_fetch_assoc($res)) {
            $parroquias[] = $row;
        }
    }
}

echo json_encode($parroquias);
?>
