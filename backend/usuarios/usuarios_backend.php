<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php';

// 2. Recibe y decodifica el JSON
$input = json_decode(file_get_contents('php://input'), true);
$accion = isset($input['accion']) ? $input['accion'] : '';

// 3. Acciones CRUD
if ($accion === 'agregar') {
    $usuario = $input['nombre_usuario'];
    $nombre = $input['nombre'];
    $correo = $input['correo_electronico'];
    $pass = password_hash($input['contrasena'], PASSWORD_DEFAULT);
    $id_rol = $input['id_rol'];
    $sesion = $input['sesion_activa'] ? 't' : 'f';
    $id_medico = isset($input['id_medico']) && $input['id_medico'] !== "" ? $input['id_medico'] : null;

    if ($id_medico !== null) {
        $sql = "INSERT INTO usuario (nombre_usuario, nombre, correo_electronico, contrasena, id_rol, sesion_activa, id_medico)
                VALUES ($1, $2, $3, $4, $5, $6, $7)";
        $params = [$usuario, $nombre, $correo, $pass, $id_rol, $sesion, $id_medico];
    } else {
        $sql = "INSERT INTO usuario (nombre_usuario, nombre, correo_electronico, contrasena, id_rol, sesion_activa)
                VALUES ($1, $2, $3, $4, $5, $6)";
        $params = [$usuario, $nombre, $correo, $pass, $id_rol, $sesion];
    }

    $result = pg_query_params($conn, $sql, $params);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
    }
    exit;
}

if ($accion === 'editar') {
    $id = $input['id_usuario'];
    $usuario = $input['nombre_usuario'];
    $nombre = $input['nombre'];
    $correo = $input['correo_electronico'];
    $id_rol = $input['id_rol'];
    $sesion = $input['sesion_activa'] ? 't' : 'f';

    // Construir el SQL dinámicamente
    $sql = "UPDATE usuario SET nombre_usuario=$1, nombre=$2, correo_electronico=$3, id_rol=$4, sesion_activa=$5";
    $params = [$usuario, $nombre, $correo, $id_rol, $sesion];

    if (!empty($input['contrasena'])) {
        $pass = password_hash($input['contrasena'], PASSWORD_DEFAULT);
        $sql .= ", contraseña=$6";
        $params[] = $pass;
        $sql .= " WHERE id_usuario=$7";
        $params[] = $id;
    } else {
        $sql .= " WHERE id_usuario=$6";
        $params[] = $id;
    }

    $result = pg_query_params($conn, $sql, $params);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
    }
    exit;
}

if ($accion === 'eliminar') {
    $id = $input['id_usuario'];
    $sql = "DELETE FROM usuario WHERE id_usuario=$1";
    $result = pg_query_params($conn, $sql, [$id]);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
    }
    exit;
}

if ($accion === 'listar') {
    $sql = "SELECT * FROM usuario";
    $result = pg_query($conn, $sql);
    $usuarios = [];
    while ($row = pg_fetch_assoc($result)) {
        $usuarios[] = $row;
    }
    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
    exit;
}

// Respuesta por defecto si la acción no es válida
echo json_encode(['success' => false, 'error' => 'Acción no válida']);
echo json_encode(['success' => false, 'error' => 'Acción no válida']);
