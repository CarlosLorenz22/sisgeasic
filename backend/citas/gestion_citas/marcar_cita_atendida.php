<?php
header('Content-Type: application/json');
$host = "localhost";
$port = "5432";
$dbname = "bd_asic";
$user = "postgres";
$password = "30429913";
$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conexion) { echo json_encode(['success'=>false,'error'=>'No se pudo conectar a la base de datos']); exit; }
$id = $_POST['id'] ?? null;
if (!$id) { echo json_encode(['success'=>false,'error'=>'ID inválido']); exit; }
pg_query($conexion, "BEGIN");
$insert = pg_query($conexion, "
    INSERT INTO cita_atendida (id, titulo, fecha_inicio, fecha_fin, color, id_medico, cedula, id_especialidad, status, atendida_en)
    SELECT id, titulo, fecha_inicio, fecha_fin, color, id_medico, cedula, id_especialidad, 'atendida', NOW()
    FROM cita WHERE id = $id
");
if ($insert) {
    $delete = pg_query($conexion, "DELETE FROM cita WHERE id = $id");
    if ($delete) {
        pg_query($conexion, "COMMIT");
        echo json_encode(['success'=>true]);
        exit;
    }
}
pg_query($conexion, "ROLLBACK");
echo json_encode(['success'=>false,'error'=>'No se pudo mover la cita a atendidas']);
