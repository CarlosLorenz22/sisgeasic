<?php
header('Content-Type: application/json');

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro id']);
    exit;
}

$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$id = pg_escape_string($conn, $_GET['id']);

// Consulta SOLO la cita, sin JOIN
$cita_query = "SELECT * FROM cita WHERE id = '{$id}' LIMIT 1";
$cita_result = pg_query($conn, $cita_query);
$cita_row = $cita_result ? pg_fetch_assoc($cita_result) : null;

// Consulta con JOIN (como antes)
$query = "
    SELECT 
        c.id, 
        c.titulo, 
        c.fecha_inicio, 
        c.fecha_fin, 
        c.color,
        c.cedula,
        c.id_medico,
        c.id_especialidad,
        TRIM(
            COALESCE(p.pr_nombre,'') || ' ' ||
            COALESCE(p.sdo_nombre,'') || ' ' ||
            COALESCE(p.pr_apellido,'') || ' ' ||
            COALESCE(p.sdo_apellido,'')
        ) AS paciente,
        TRIM(
            COALESCE(m.pr_nombre,'') || ' ' ||
            COALESCE(m.sdo_nombre,'') || ' ' ||
            COALESCE(m.pr_apellido,'') || ' ' ||
            COALESCE(m.sdo_apellido,'')
        ) AS medico,
        e.nombre_especialidad AS especialidad
    FROM cita c
    LEFT JOIN paciente p ON c.cedula = p.cedula
    LEFT JOIN medico m ON c.id_medico = m.id_medico
    LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
    WHERE c.id = '{$id}'
    LIMIT 1
";
$result = pg_query($conn, $query);
$row = $result ? pg_fetch_assoc($result) : null;

if ($row) {
    echo json_encode([
        'id'           => $row['id'],
        'titulo'       => $row['titulo'],
        'fecha_inicio' => $row['fecha_inicio'],
        'fecha_fin'    => $row['fecha_fin'],
        'color'        => $row['color'],
        'paciente'     => trim($row['paciente']),
        'medico'       => trim($row['medico']),
        'especialidad' => $row['especialidad'],
        'cedula'       => $row['cedula'],
        'id_medico'    => $row['id_medico'],
        'id_especialidad' => $row['id_especialidad']
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Cita no encontrada']);
}
pg_close($conn);