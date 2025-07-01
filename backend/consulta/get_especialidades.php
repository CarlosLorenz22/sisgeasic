<?php

$conn = pg_connect("host=localhost port=5432 dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    http_response_code(500);
    echo json_encode([]);
    exit;
}

$res = pg_query($conn, "SELECT id_especialidad, nombre_especialidad FROM especialidad ORDER BY nombre_especialidad");
$especialidades = pg_fetch_all($res);
echo json_encode($especialidades ?: []);
