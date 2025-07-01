<?php
header('Content-Type: application/json');

try {
    $conn = new PDO("pgsql:host=localhost;dbname=bd_asic", "postgres", "30429913");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $estado_id = isset($_GET['estado_id']) ? intval($_GET['estado_id']) : 0;
    $stmt = $conn->prepare("SELECT id_municipio, nombre FROM municipio WHERE estado_id = ? ORDER BY nombre");
    $stmt->execute([$estado_id]);
    $municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['municipios' => $municipios]);
} catch (PDOException $e) {
    echo json_encode(['municipios' => [], 'error' => $e->getMessage()]);
}
