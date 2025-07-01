<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conexion_pdo.php'; // Usar PDO

if (!isset($conn) || !$conn) {
    die("Error de conexión a la base de datos");
}

try {
    $sql = "SELECT id_especialidad, nombre_especialidad FROM especialidad ORDER BY nombre_especialidad ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($especialidades);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
