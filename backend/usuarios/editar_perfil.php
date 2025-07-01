<?php
session_start();
header('Content-Type: application/json');

// Conexión a la base de datos
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener el usuario actual (puedes usar el id o nombre_usuario de la sesión)
$nombre_usuario_actual = $_SESSION['usuario'] ?? null;
if (!$nombre_usuario_actual) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

// Recoger datos del formulario
$nombre = $_POST['nombre'] ?? '';
$nombre_usuario = $_POST['nombre_usuario'] ?? '';
$correo = $_POST['correo_electronico'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

// Procesar la foto si se subió
$foto_url = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['foto']['tmp_name'];
    $nombre_archivo = uniqid('perfil_') . '_' . basename($_FILES['foto']['name']);
    $ruta_destino = "../assets/img/perfiles/" . $nombre_archivo;
    if (move_uploaded_file($tmp_name, $ruta_destino)) {
        $foto_url = "/sisgeasic/assets/img/perfiles/" . $nombre_archivo;
    }
}

// Construir la consulta de actualización
$campos = [
    'nombre' => $nombre,
    'nombre_usuario' => $nombre_usuario,
    'correo_electronico' => $correo
];
if (!empty($contrasena)) {
    $campos['contraseña'] = $contrasena;
}
if ($foto_url) {
    $campos['foto'] = $foto_url;
}

$set = [];
$params = [];
$i = 1;
foreach ($campos as $col => $val) {
    $set[] = "$col = \$$i";
    $params[] = $val;
    $i++;
}
$params[] = $nombre_usuario_actual;

// Actualizar datos
$sql = "UPDATE usuario SET " . implode(', ', $set) . " WHERE nombre_usuario = \$$i";
$result = pg_query_params($conn, $sql, $params);

if ($result) {
    // Actualizar variables de sesión si cambió el usuario logueado
    $_SESSION['nombre'] = $nombre;
    $_SESSION['nombre_usuario'] = $nombre_usuario;
    $_SESSION['correo_electronico'] = $correo;
    if ($foto_url) {
        $_SESSION['foto'] = $foto_url;
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil']);
}
?>
