<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Limpia cualquier salida previa
ob_clean();

include_once __DIR__ . '/../backend/conexion.php'; // Debe definir $conn con pg_connect

if (!isset($conn) || !$conn) {
    echo json_encode(['ok'=>false, 'error'=>'No se pudo establecer la conexión con la base de datos.']);
    exit;
}

$accion = $_REQUEST['accion'] ?? '';

// Prueba rápida para ver si el archivo responde
// echo json_encode(['ok'=>true, 'msg'=>'Backend activo', 'accion'=>$accion]); exit;

if ($accion === 'listar') {
    $busqueda = $_GET['busqueda'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    $limit = 6;
    $offset = ($page - 1) * $limit;

    $like = '%' . $busqueda . '%';
    $where = "CONCAT(cedula, ' ', pr_nombre, ' ', pr_apellido, ' ', telefono, ' ', direccion) ILIKE $1";
    $sql_count = "SELECT COUNT(*) as total FROM paciente WHERE $where";
    $result = pg_query_params($conn, $sql_count, [$like]);
    if (!$result) {
        echo json_encode(['ok'=>false, 'error'=>'Error en COUNT: ' . pg_last_error($conn)]);
        exit;
    }
    $total = pg_fetch_assoc($result)['total'];
    $total_paginas = ceil($total / $limit);

    $sql = "SELECT cedula, pr_nombre, sdo_nombre, pr_apellido, sdo_apellido, genero, fecha_nacimiento, correo, telefono, direccion, peso, talla 
            FROM paciente 
            WHERE $where 
            ORDER BY cedula 
            LIMIT $2 OFFSET $3";
    $result = pg_query_params($conn, $sql, [$like, $limit, $offset]);
    if (!$result) {
        echo json_encode(['ok'=>false, 'error'=>'Error en SELECT: ' . pg_last_error($conn)]);
        exit;
    }
    $pacientes = [];
    while ($row = pg_fetch_assoc($result)) $pacientes[] = $row;

    echo json_encode([
        'ok' => true,
        'pacientes' => $pacientes,
        'total_paginas' => $total_paginas,
        'page' => $page
    ]);
    exit;
}

if ($accion === 'agregar') {
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
    $peso = $_POST['peso'] !== '' ? $_POST['peso'] : null;
    $talla = $_POST['talla'] !== '' ? $_POST['talla'] : null;

    $sql = "INSERT INTO paciente (cedula, pr_nombre, sdo_nombre, pr_apellido, sdo_apellido, genero, fecha_nacimiento, correo, telefono, direccion, peso, talla)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)";
    $params = [$cedula, $pr_nombre, $sdo_nombre, $pr_apellido, $sdo_apellido, $genero, $fecha_nacimiento, $correo, $telefono, $direccion, $peso, $talla];
    $result = pg_query_params($conn, $sql, $params);
    if (!$result) {
        echo json_encode(['ok'=>false, 'error'=>'Error en INSERT: ' . pg_last_error($conn)]);
        exit;
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($accion === 'eliminar') {
    $cedula = $_POST['cedula'] ?? '';
    $sql = "DELETE FROM paciente WHERE cedula = $1";
    $result = pg_query_params($conn, $sql, [$cedula]);
    if (!$result) {
        echo json_encode(['ok'=>false, 'error'=>'Error en DELETE: ' . pg_last_error($conn)]);
        exit;
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($accion === 'ver') {
    $cedula = $_GET['cedula'] ?? '';
    $resp = ['ok' => false];
    if ($cedula) {
        $sql = "SELECT * FROM paciente WHERE cedula = $1";
        $result = pg_query_params($conn, $sql, [$cedula]);
        if ($result && $row = pg_fetch_assoc($result)) {
            $resp['ok'] = true;
            $resp['paciente'] = $row;
        } else {
            $resp['error'] = "Paciente no encontrado";
        }
    } else {
        $resp['error'] = "Cédula no especificada";
    }
    echo json_encode($resp);
    exit;
}

echo json_encode(['ok'=>false, 'error'=>'Acción no válida']);
