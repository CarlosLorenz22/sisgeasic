<?php
require_once '../conexion_pdo.php';
header('Content-Type: application/json');

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_medico'])) {
    echo json_encode(['success' => false, 'error' => 'ID no recibido']);
    exit;
}

try {
    $sql = "DELETE FROM medico WHERE id_medico=?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$data['id_medico']])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
