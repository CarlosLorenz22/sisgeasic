<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Corrige la ruta de conexión
include_once __DIR__ . '/../conexion.php';

$estado_id = $_GET['estado_id'] ?? '';
$municipios = [];

if ($estado_id && isset($conn)) {
    $res = pg_query_params($conn, "SELECT id_municipio, nombre FROM municipio WHERE estado_id = $1 ORDER BY nombre", [$estado_id]);
    if ($res) {
        while ($row = pg_fetch_assoc($res)) {
            $municipios[] = $row;
        }
    }
}

echo json_encode($municipios);
?>
