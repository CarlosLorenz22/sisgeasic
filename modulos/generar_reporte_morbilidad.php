<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Conexión a la base de datos PostgreSQL
$host = 'localhost';
$user = 'postgres'; // Cambia por tu usuario
$pass = '30429913'; // Cambia por tu contraseña
$db = 'bd_asic'; // Cambia por el nombre real
$port = '5432';

$conn = pg_connect("host=$host dbname=$db user=$user password=$pass port=$port");
if (!$conn) {
    die('Error de conexión a PostgreSQL');
}

// Consulta para obtener los datos requeridos
$sql = "
SELECT 
    (p.pr_nombre || ' ' || p.sdo_nombre || ' ' || p.pr_apellido || ' ' || p.sdo_apellido) AS nombre_apellido,
    EXTRACT(YEAR FROM age(p.fecha_nacimiento)) AS edad,
    p.genero,
    p.cedula,
    p.telefono,
    p.direccion,
    COALESCE(pa.nombre, '') AS parroquia,
    c.fecha_consulta,
    c.diagnostico,
    CASE WHEN c.primera_vez IS TRUE THEN 'Primera vez' ELSE 'Sucesiva' END AS consulta,
    '' AS en_periodo_de,
    '' AS si_no,
    '' AS semanas_gestacion,
    'Consulta' AS tipo_atencion,
    COALESCE(e.id_especialidad::text, '') AS especialidad
FROM consulta c
INNER JOIN paciente p ON c.cedula = p.cedula
LEFT JOIN parroquia pa ON p.id_parroquia = pa.id_parroquia
LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
ORDER BY c.fecha_consulta DESC
";

// Ejecutar consulta
$result = pg_query($conn, $sql);

if (!$result) {
    die('Error en la consulta: ' . pg_last_error($conn));
}
if (pg_num_rows($result) == 0) {
    die('La consulta no devolvió resultados. Verifica que existan datos en las tablas y que las relaciones sean correctas.');
}

// Encabezados
$datos = [
    ['NOMBRE Y APELLIDO', 'EDAD', 'SEXO', 'CEDULA', 'TELEFONO', 'DIRECCION', 'PARROQUIA', 'FECHA', 'DIAGNOSTICO', 'CONSULTA', 'EN PERIODO DE', 'SI/NO', 'SEMANAS DE GESTACION', 'TIPO DE ATENCION', 'ESPECIALIDAD']
];

// Agregar filas de la base de datos
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $datos[] = [
            $row['nombre_apellido'],
            $row['edad'],
            $row['genero'],
            $row['cedula'],
            $row['telefono'],
            $row['direccion'],
            $row['parroquia'],
            $row['fecha_consulta'],
            $row['diagnostico'],
            $row['consulta'],
            $row['en_periodo_de'],
            $row['si_no'],
            $row['semanas_gestacion'],
            $row['tipo_atencion'],
            $row['especialidad']
        ];
    }
}
pg_close($conn);

// Calcular estadísticas
$total = count($datos) - 1;
$porSexo = ['M' => 0, 'F' => 0];
$porEspecialidad = [];
for ($i = 1; $i <= $total; $i++) {
    $sexo = $datos[$i][2];
    $esp = $datos[$i][14];
    $porSexo[$sexo] = ($porSexo[$sexo] ?? 0) + 1;
    $porEspecialidad[$esp] = ($porEspecialidad[$esp] ?? 0) + 1;
}

// Crear hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Función para convertir número de columna a letra (A, B, C, ..., Z, AA, AB, ...)
function colLetra($num) {
    $letra = '';
    while ($num > 0) {
        $num--;
        $letra = chr(65 + ($num % 26)) . $letra;
        $num = intval($num / 26);
    }
    return $letra;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Título grande centrado
$sheet->mergeCells('A1:O1');
$sheet->setCellValue('A1', 'MONTALBAN');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(36)->getColor()->setRGB('000000');
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

// Encabezados
$row = 2;
$col = 1;
foreach ($datos[0] as $valor) {
    $columnaLetra = colLetra($col);
    $sheet->setCellValue($columnaLetra . $row, $valor);
    // Fondo negro, letra blanca, negrita
    $sheet->getStyle($columnaLetra . $row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle($columnaLetra . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('222222');
    $col++;
}

// Filtros en encabezados
$sheet->setAutoFilter('A2:O2');

// Datos
for ($i = 1; $i < count($datos); $i++) {
    $col = 1;
    $rowDatos = $i + 2;
    foreach ($datos[$i] as $valor) {
        $columnaLetra = colLetra($col);
        $sheet->setCellValue($columnaLetra . $rowDatos, $valor);
        // Fondo negro, letra roja
        $sheet->getStyle($columnaLetra . $rowDatos)->getFont()->getColor()->setRGB('FF3333');
        $sheet->getStyle($columnaLetra . $rowDatos)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('111111');
        $col++;
    }
}

// Bordes para toda la tabla
$lastRow = count($datos) + 1;
$lastCol = colLetra(count($datos[0]));
$sheet->getStyle("A2:{$lastCol}{$lastRow}")
    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FFFFFF'));

// Ajustar ancho de columnas
for ($c = 1; $c <= count($datos[0]); $c++) {
    $sheet->getColumnDimension(colLetra($c))->setAutoSize(true);
}

// Estadísticas
$row += 2;
$sheet->setCellValue("A$row", "Estadísticas");
$row++;
$sheet->setCellValue("A$row", "Pacientes atendidos por especialidad:");
foreach ($porEspecialidad as $esp => $cant) {
    $row++;
    $sheet->setCellValue("B$row", $esp);
    $sheet->setCellValue("C$row", $cant);
}
$row += 2;
$sheet->setCellValue("A$row", "Porcentaje por sexo:");
if ($total > 0) {
    foreach ($porSexo as $sexo => $cant) {
        $row++;
        $sheet->setCellValue("B$row", $sexo);
        $sheet->setCellValue("C$row", round(($cant / $total) * 100, 2) . '%');
    }
}
$row += 2;
$sheet->setCellValue("A$row", "Porcentaje de consultas atendidas por especialidad:");
if ($total > 0) {
    foreach ($porEspecialidad as $esp => $cant) {
        $row++;
        $sheet->setCellValue("B$row", $esp);
        $sheet->setCellValue("C$row", round(($cant / $total) * 100, 2) . '%');
    }
}

// Descargar archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_morbilidad_general.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
