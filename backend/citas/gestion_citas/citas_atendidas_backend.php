<?php
header('Content-Type: application/json');
$host = "localhost";
$port = "5432";
$dbname = "bd_asic";
$user = "postgres";
$password = "30429913";
$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conexion) { echo json_encode([]); exit; }
$query = "
    SELECT 
        c.id AS id_cita,
        CONCAT_WS(' ', p.pr_nombre, p.sdo_nombre, p.pr_apellido, p.sdo_apellido) AS nombre_paciente,
        CONCAT_WS(' ', m.pr_nombre, m.sdo_nombre, m.pr_apellido, m.sdo_apellido) AS nombre_medico,
        e.nombre_especialidad AS especialidad,
        c.titulo,
        c.fecha_inicio,
        c.fecha_fin,
        c.color,
        c.id_medico,
        c.cedula,
        c.id_especialidad,
        c.status,
        c.atendida_en
    FROM cita_atendida c
    LEFT JOIN paciente p ON c.cedula = p.cedula
    LEFT JOIN medico m ON c.id_medico = m.id_medico
    LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
    ORDER BY c.fecha_inicio DESC
";
$result = pg_query($conexion, $query);
echo json_encode(pg_fetch_all($result) ?: []);
