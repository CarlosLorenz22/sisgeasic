<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$accion = $_POST['accion'] ?? '';
if ($accion !== 'editar') {
    echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
    exit;
}

$cedula = $_POST['cedula'] ?? '';
$pr_nombre = $_POST['pr_nombre'] ?? '';
$sdo_nombre = $_POST['sdo_nombre'] ?? '';
$pr_apellido = $_POST['pr_apellido'] ?? '';
$sdo_apellido = $_POST['sdo_apellido'] ?? '';
$genero = $_POST['genero'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$correo = $_POST['correo'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$id_estado = $_POST['id_estado'] ?? null;
$id_municipio = $_POST['id_municipio'] ?? null;
$id_parroquia = $_POST['id_parroquia'] ?? null;
$peso = $_POST['peso'] ?? null;
$talla = $_POST['talla'] ?? null;

if (!$cedula) {
    echo json_encode(['ok' => false, 'error' => 'Cédula requerida']);
    exit;
}

$sql = "UPDATE paciente SET pr_nombre=$1, sdo_nombre=$2, pr_apellido=$3, sdo_apellido=$4, genero=$5, fecha_nacimiento=$6, correo=$7, telefono=$8, direccion=$9, id_estado=$10, id_municipio=$11, id_parroquia=$12, peso=$13, talla=$14 WHERE cedula=$15";
$params = [
    $pr_nombre, $sdo_nombre, $pr_apellido, $sdo_apellido, $genero, $fecha_nacimiento, $correo, $telefono, $direccion,
    $id_estado, $id_municipio, $id_parroquia, $peso, $talla, $cedula
];
$result = pg_query_params($conn, $sql, $params);

if ($result) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Error al actualizar: ' . pg_last_error($conn)]);
}
