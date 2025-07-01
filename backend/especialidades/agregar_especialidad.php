<?php
// Conexión directa a PostgreSQL
$host = 'localhost';
$db   = 'bd_asic';
$user = 'postgres';
$pass = '30429913';
$port = '5432';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión: ' . $e->getMessage()
    ]);
    exit;
}

header('Content-Type: application/json');

$nombre = isset($_POST['nombre_especialidad']) ? trim($_POST['nombre_especialidad']) : '';
$color = isset($_POST['color']) ? trim($_POST['color']) : '';
$hora_inicio = isset($_POST['hora_inicio']) && $_POST['hora_inicio'] !== '' ? trim($_POST['hora_inicio']) : null;
$hora_fin = isset($_POST['hora_fin']) && $_POST['hora_fin'] !== '' ? trim($_POST['hora_fin']) : null;

if ($nombre === '' || $color === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Nombre y color son obligatorios.'
    ]);
    exit;
}

// Validar formato de hora (HH:MM)
if (
    ($hora_inicio !== null && !preg_match('/^\d{2}:\d{2}$/', $hora_inicio)) ||
    ($hora_fin !== null && !preg_match('/^\d{2}:\d{2}$/', $hora_fin))
) {
    echo json_encode([
        'success' => false,
        'message' => 'La hora de inicio y fin deben estar en formato HH:MM (ejemplo: 07:00).'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO especialidad (nombre_especialidad, color, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $color, $hora_inicio, $hora_fin]);
    echo json_encode([
        'success' => true,
        'message' => 'Especialidad agregada correctamente.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar especialidad: ' . $e->getMessage()
    ]);
}
