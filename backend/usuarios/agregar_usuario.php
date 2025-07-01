<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

$input = json_decode(file_get_contents('php://input'), true);

if (
    isset($input['accion']) && $input['accion'] === 'agregar' &&
    !empty($input['nombre_usuario']) &&
    !empty($input['nombre']) &&
    !empty($input['contrasena']) &&
    !empty($input['id_rol'])
) {
    $nombre_usuario = pg_escape_string($conn, $input['nombre_usuario']);
    $nombre = pg_escape_string($conn, $input['nombre']);
    $correo = isset($input['correo_electronico']) ? pg_escape_string($conn, $input['correo_electronico']) : '';
    $contrasena = pg_escape_string($conn, $input['contrasena']);
    $id_rol = intval($input['id_rol']);
    $id_medico = isset($input['id_medico']) && $input['id_medico'] !== "" ? intval($input['id_medico']) : null;

    if ($id_medico !== null) {
        $sql = "INSERT INTO usuario (nombre_usuario, nombre, correo_electronico, contraseña, id_rol, id_medico)
                VALUES ('$nombre_usuario', '$nombre', " . ($correo ? "'$correo'" : "NULL") . ", '$contrasena', $id_rol, $id_medico) RETURNING id_usuario";
    } else {
        $sql = "INSERT INTO usuario (nombre_usuario, nombre, correo_electronico, contraseña, id_rol)
                VALUES ('$nombre_usuario', '$nombre', " . ($correo ? "'$correo'" : "NULL") . ", '$contrasena', $id_rol) RETURNING id_usuario";
    }
    $result = pg_query($conn, $sql);

    if ($result) {
        $row = pg_fetch_assoc($result);
        $id_usuario = $row['id_usuario'];

        // Si es médico, actualiza la tabla medico para asociar el usuario
        if ($id_medico !== null) {
            $sql_update = "UPDATE medico SET id_usuario = $1 WHERE id_medico = $2";
            pg_query_params($conn, $sql_update, [$id_usuario, $id_medico]);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al agregar usuario: ' . pg_last_error($conn)]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Datos incompletos o acción no válida']);
