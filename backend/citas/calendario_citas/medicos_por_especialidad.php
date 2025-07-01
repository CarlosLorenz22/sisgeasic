<?php
header('Content-Type: application/json');

if (empty($_GET['id_especialidad'])) {
    echo json_encode([]);
    exit;
}

$id_especialidad = $_GET['id_especialidad'];
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id_medico, pr_nombre, sdo_nombre, pr_apellido, sdo_apellido FROM medico WHERE id_especialidad = $1 ORDER BY pr_apellido, pr_nombre";
$result = pg_query_params($conn, $sql, [$id_especialidad]);
$medicos = [];
while ($row = pg_fetch_assoc($result)) {
    $nombre = trim($row['pr_nombre'] . ' ' . $row['sdo_nombre'] . ' ' . $row['pr_apellido'] . ' ' . $row['sdo_apellido']);
    $medicos[] = [
        'id_medico' => $row['id_medico'],
        'nombre' => $nombre
    ];
}
echo json_encode($medicos);
pg_close($conn);
