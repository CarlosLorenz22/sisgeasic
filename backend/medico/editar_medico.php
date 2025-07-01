<?php
require_once '../conexion_pdo.php';
header('Content-Type: application/json');

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_medico'])) {
    echo json_encode(['success' => false, 'error' => 'Datos no recibidos']);
    exit;
}

try {
    $sql = "UPDATE medico SET pr_nombre=?, sdo_nombre=?, pr_apellido=?, sdo_apellido=?, id_especialidad=?, horario_trabajo=?, hora_inicio=?, hora_fin=?
            WHERE id_medico=?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([
        $data['pr_nombre'],
        $data['sdo_nombre'],
        $data['pr_apellido'],
        $data['sdo_apellido'],
        $data['id_especialidad'],
        $data['horario_trabajo'],
        $data['hora_inicio'],
        $data['hora_fin'],
        $data['id_medico']
    ])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al actualizar']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
