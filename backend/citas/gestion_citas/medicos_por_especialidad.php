<?php

$host = "localhost";
$user = "postgres";
$pass = "30429913"; // Cambia si tienes contraseña
$db = "bd_asic"; // Cambia por el nombre real de tu base de datos

$conn = pg_connect("host=$host dbname=$db user=$user password=$pass");
if (!$conn) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$id_especialidad = isset($_GET['id_especialidad']) ? intval($_GET['id_especialidad']) : 0;
$medicos = [];

if ($id_especialidad > 0) {
    $sql = "SELECT id_medico, CONCAT_WS(' ', pr_nombre, sdo_nombre, pr_apellido, sdo_apellido) AS nombre_medico FROM medico WHERE id_especialidad = $1";
    $result = pg_query_params($conn, $sql, [$id_especialidad]);
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $medicos[] = $row;
        }
    } else {
        echo json_encode(['error' => 'Error en la consulta: ' . pg_last_error($conn)]);
        pg_close($conn);
        exit;
    }
}

header('Content-Type: application/json');
echo json_encode($medicos);
pg_close($conn);
