<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conexion_pdo.php'; // Usar PDO

if (!isset($conn) || !$conn) {
    die("Error de conexión a la base de datos");
}

try {
    $sql = "SELECT 
                m.id_medico,
                m.pr_nombre,
                m.sdo_nombre,
                m.pr_apellido,
                m.sdo_apellido,
                m.id_especialidad,
                e.nombre_especialidad,
                m.horario_trabajo,
                m.hora_inicio,
                m.hora_fin
            FROM medico m
            JOIN especialidad e ON m.id_especialidad = e.id_especialidad
            ORDER BY m.id_medico ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($medicos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>