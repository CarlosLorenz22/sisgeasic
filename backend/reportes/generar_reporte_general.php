<?php
file_put_contents('/tmp/debug_exportar_pacientes.log', "Script ejecutado: ".date('c')."\n", FILE_APPEND);
ob_clean(); // Limpia cualquier salida previa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos PostgreSQL
$host = 'localhost';
$user = 'postgres';
$pass = '30429913';
$db = 'bd_asic';
$port = '5432';

$conn = pg_connect("host=$host dbname=$db user=$user password=$pass port=$port");
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error de conexión a PostgreSQL';
    exit;
}

// Consulta específica para los datos de pacientes
$sql = "
SELECT 
    (p.pr_nombre || ' ' || COALESCE(p.sdo_nombre, '') || ' ' || p.pr_apellido || ' ' || COALESCE(p.sdo_apellido, '')) AS nombre_apellido,
    EXTRACT(YEAR FROM age(p.fecha_nacimiento)) AS edad,
    p.genero AS sexo,
    p.cedula,
    p.telefono,
    p.direccion,
    COALESCE(pa.nombre, '') AS parroquia,
    c.fecha_consulta,
    CASE WHEN c.primera_vez IS TRUE THEN 'SI' WHEN c.primera_vez IS FALSE THEN 'NO' ELSE '' END AS primera_vez,
    COALESCE(e.nombre_especialidad, '') AS especialidad,  -- Usa el nombre correcto de la columna
    h.diagnostico
FROM paciente p
LEFT JOIN parroquia pa ON p.id_parroquia = pa.id_parroquia
LEFT JOIN consulta c ON p.cedula = c.cedula
LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
LEFT JOIN historial_consultas h ON c.id_consulta = h.id_consulta
ORDER BY p.pr_apellido, p.pr_nombre, c.fecha_consulta DESC
";

// Ejecutar consulta
$result = pg_query($conn, $sql);

if (!$result) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error en la consulta: ' . pg_last_error($conn);
    exit;
}
if (pg_num_rows($result) == 0) {
    header('HTTP/1.1 404 Not Found');
    echo 'No se encontraron pacientes en la base de datos.';
    exit;
}

// Encabezados del CSV
$datos = [
    ['DATA GENERAL DE PACIENTES ATENDIDOS EN LA ASIC MONTALBAN', '', '', '', '', '', '', '', '', '', '', ''],
    ['NOMBRE Y APELLIDO', 'EDAD', 'SEXO', 'CEDULA', 'TELEFONO', 'DIRECCION', 'PARROQUIA', 'FECHA DE CONSULTA', 'PRIMERA VEZ', 'ESPECIALIDAD', 'DIAGNOSTICO']
];

// Agregar filas de pacientes
while ($row = pg_fetch_assoc($result)) {
    $datos[] = [
        $row['nombre_apellido'],
        $row['edad'],
        $row['sexo'],
        $row['cedula'],
        $row['telefono'],
        $row['direccion'],
        $row['parroquia'],
        $row['fecha_consulta'] ?? '',
        $row['primera_vez'] ?? '',
        $row['especialidad'] ?? '',
        $row['diagnostico'] ?? ''
    ];
}
pg_close($conn);

// Calcular estadísticas básicas
$total = count($datos) - 2; // Restar título y encabezados
$porSexo = ['M' => 0, 'F' => 0];
for ($i = 2; $i < count($datos); $i++) {
    $sexo = $datos[$i][2];
    if (isset($porSexo[$sexo])) {
        $porSexo[$sexo]++;
    }
}

// Agregar estadísticas al final
$datos[] = [''];
$datos[] = ['ESTADÍSTICAS'];
$datos[] = ['Total de pacientes:', $total];
$datos[] = ['Por sexo:'];
foreach ($porSexo as $sexo => $cant) {
    $datos[] = ['', $sexo, $cant, round(($cant / $total) * 100, 2) . '%'];
}

// Generar CSV
function generateCSV($data) {
    $output = fopen('php://output', 'w');
    // BOM para UTF-8 y especificar separador
    fwrite($output, "\xEF\xBB\xBFsep=,\n");
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
}

// Configurar cabeceras para descarga
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="reporte_clinica.csv"');
header('Cache-Control: max-age=0');

// Generar el CSV
generateCSV($datos);
exit;