<?php
header('Content-Type: application/json');

$id_especialidad = isset($_GET['id_especialidad']) ? intval($_GET['id_especialidad']) : 0;

$conn = pg_connect("host=localhost port=5432 dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión: ' . pg_last_error()]);
    exit;
}

$res = pg_query($conn, "SELECT id_medico, pr_nombre, sdo_nombre, pr_apellido, sdo_apellido FROM medico WHERE id_especialidad = $id_especialidad ORDER BY pr_nombre, pr_apellido");
if (!$res) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta: ' . pg_last_error($conn)]);
    exit;
}

$medicos = pg_fetch_all($res);
echo json_encode($medicos ?: []);
