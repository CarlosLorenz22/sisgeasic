<?php
header('Content-Type: application/json');

try {
    $conn = new PDO("pgsql:host=localhost;dbname=bd_asic", "postgres", "30429913");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $municipio_id = isset($_GET['municipio_id']) ? intval($_GET['municipio_id']) : 0;
    $stmt = $conn->prepare("SELECT id_parroquia, nombre FROM parroquia WHERE municipio_id = ? ORDER BY nombre");
    $stmt->execute([$municipio_id]);
    $parroquias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['parroquias' => $parroquias]);
} catch (PDOException $e) {
    echo json_encode(['parroquias' => [], 'error' => $e->getMessage()]);
}
