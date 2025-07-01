<?php
header('Content-Type: application/json');
session_start();

// Cambia estos datos por los de tu base de datos real
$host = "localhost";
$port = "5432";
$dbname = "bd_asic";
$user = "postgres";
$password = "30429913";

$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conexion) {
    echo json_encode(['success' => false, 'error' => 'No se pudo conectar a la base de datos']);
    exit;
}

// Obtener rol y id_medico de la sesión
$id_rol = $_SESSION['id_rol'] ?? 0;
$id_medico = $_SESSION['id_medico'] ?? 0;

// Filtrar por médico si es médico
$where = [];
if ($id_rol == 2 && $id_medico) {
    $where[] = "c.id_medico = $id_medico";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT 
        c.id AS id_cita,
        -- Nombre completo del paciente
        CONCAT_WS(' ', p.pr_nombre, p.sdo_nombre, p.pr_apellido, p.sdo_apellido) AS nombre_paciente,
        -- Nombre completo del médico
        CONCAT_WS(' ', m.pr_nombre, m.sdo_nombre, m.pr_apellido, m.sdo_apellido) AS nombre_medico,
        e.nombre_especialidad AS especialidad,
        c.titulo,
        c.fecha_inicio,
        c.fecha_fin,
        c.color,
        c.id_medico,
        c.cedula,
        c.id_especialidad,
        c.status
    FROM cita c
    LEFT JOIN paciente p ON c.cedula = p.cedula
    LEFT JOIN medico m ON c.id_medico = m.id_medico
    LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
    $where_sql
    ORDER BY c.fecha_inicio DESC
";

$result = pg_query($conexion, $query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al consultar las citas',
        'pg_error' => pg_last_error($conexion)
    ]);
    exit;
}

$citas = pg_fetch_all($result);
echo json_encode($citas ?: []);
