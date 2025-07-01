<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (
    empty($data['titulo']) ||
    empty($data['cedula']) ||
    empty($data['id_medico']) ||
    empty($data['id_especialidad']) ||
    empty($data['fecha_inicio']) ||
    empty($data['fecha_fin'])
) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión']);
    exit;
}

$sql = "INSERT INTO cita (titulo, cedula, id_medico, id_especialidad, fecha_inicio, fecha_fin, color)
        VALUES ($1, $2, $3, $4, $5, $6, (
            SELECT color FROM especialidad WHERE id_especialidad = $4 LIMIT 1
        ))";
$params = [
    $data['titulo'],
    $data['cedula'],
    $data['id_medico'],
    $data['id_especialidad'],
    $data['fecha_inicio'],
    $data['fecha_fin']
];
$result = pg_query_params($conn, $sql, $params);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se pudo guardar la cita']);
}
pg_close($conn);
