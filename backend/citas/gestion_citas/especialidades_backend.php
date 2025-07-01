<?php
header('Content-Type: application/json');
$host = "localhost";
$port = "5432";
$dbname = "bd_asic";
$user = "postgres";
$password = "30429913";
$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conexion) { echo json_encode([]); exit; }
$result = pg_query($conexion, "SELECT nombre_especialidad FROM especialidad ORDER BY nombre_especialidad");
echo json_encode(pg_fetch_all($result) ?: []);
