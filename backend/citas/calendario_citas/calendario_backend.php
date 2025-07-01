<?php
header('Content-Type: application/json');

// Ajusta los datos de conexión según tu entorno
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$where = '';
if (!empty($_GET['especialidad'])) {
    $id_esp = pg_escape_string($conn, $_GET['especialidad']);
    $where = "WHERE id_especialidad = '{$id_esp}'";
}

$citas = [];
$query = "SELECT id, titulo, fecha_inicio, fecha_fin, color FROM cita $where";
$result = pg_query($conn, $query);
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $citas[] = [
            'id'    => $row['id'],
            'title' => $row['titulo'],
            'start' => $row['fecha_inicio'],
            'end'   => $row['fecha_fin'],
            'color' => $row['color']
        ];
    }
}
// Devuelve siempre un JSON válido
echo json_encode($citas);
pg_close($conn);