<?php
// Conexión a la base de datos
$host = 'localhost';
$port = '5432';
$dbname = 'bd_asic';
$user = 'postgres';
$password = '30429913';

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

// Obtener la cédula de la consulta
$cedula = $_GET['cedula'] ?? null;

if (!$cedula) {
    die(json_encode(['error' => 'Cédula no proporcionada']));
}

// Consulta para obtener los detalles de la consulta
$query = "SELECT c.cedula, 
                 CONCAT(p.pr_nombre, ' ', p.sdo_nombre, ' ', p.pr_apellido, ' ', p.sdo_apellido) AS nombre_paciente, 
                 CONCAT(m.pr_nombre, ' ', m.sdo_nombre, ' ', m.pr_apellido, ' ', m.sdo_apellido) AS nombre_medico, 
                 e.nombre_especialidad, 
                 c.fecha_consulta, 
                 c.motivo, 
                 c.primera_vez -- Dejar el valor original sin conversión
          FROM consulta c
          JOIN paciente p ON c.cedula = p.cedula
          JOIN medico m ON c.id_medico = m.id_medico
          JOIN especialidad e ON c.id_especialidad = e.id_especialidad
          WHERE c.cedula = $1";

$result = pg_query_params($conn, $query, [$cedula]);

if (!$result) {
    die(json_encode(['error' => 'Error al ejecutar la consulta']));
}

$consulta = pg_fetch_assoc($result);

if (!$consulta) {
    die(json_encode(['error' => 'No se encontraron detalles para la consulta']));
}

// Convertir el valor de primera_vez explícitamente a booleano en PHP
$consulta['primera_vez'] = filter_var($consulta['primera_vez'], FILTER_VALIDATE_BOOLEAN);

// Si la consulta no es por primera vez, buscar datos adicionales en la tabla historial_consultas
if (!$consulta['primera_vez']) { // Verificar correctamente si no es la primera vez
    $historial_query = "SELECT observaciones, diagnostico, tratamiento, fecha_consulta AS consulta_anterior 
                        FROM historial_consultas 
                        WHERE cedula = $1 
                        ORDER BY fecha_consulta DESC LIMIT 1";

    $historial_result = pg_query_params($conn, $historial_query, [$cedula]);

    if ($historial_result) {
        $historial = pg_fetch_assoc($historial_result);
        if ($historial) {
            $consulta = array_merge($consulta, $historial);
        }
    }
}

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($consulta);
