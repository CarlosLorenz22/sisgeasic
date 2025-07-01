<?php
header('Content-Type: application/json');

try {
    $conn = new PDO("pgsql:host=localhost;dbname=bd_asic", "postgres", "30429913");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SELECT id_estado, nombre FROM estado ORDER BY nombre");
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['estados' => $estados]);
} catch (PDOException $e) {
    echo json_encode(['estados' => [], 'error' => $e->getMessage()]);
}
