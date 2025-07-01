<?php
header('Content-Type: application/json');

try {
    $conn = new PDO("pgsql:host=localhost;dbname=bd_asic", "postgres", "30429913");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $estado_id = isset($_GET['estado_id']) ? intval($_GET['estado_id']) : null;
    $municipio_id = isset($_GET['municipio_id']) ? intval($_GET['municipio_id']) : null;
    $parroquia_id = isset($_GET['parroquia_id']) ? intval($_GET['parroquia_id']) : null;

    // Filtros dinámicos
    $where = [];
    $params = [];

    if ($estado_id) {
        $where[] = "pac.id_estado = ?";
        $params[] = $estado_id;
    }
    if ($municipio_id) {
        $where[] = "pac.id_municipio = ?";
        $params[] = $municipio_id;
    }
    if ($parroquia_id) {
        $where[] = "pac.id_parroquia = ?";
        $params[] = $parroquia_id;
    }
    $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

    // Subconsulta: pacientes atendidos (por historial o cita)
    $subquery = "
        SELECT DISTINCT cedula FROM (
            SELECT cedula FROM historial_consultas
            UNION
            SELECT cedula FROM cita_atendida
        ) atendidos
    ";

    // Pacientes atendidos por parroquia
    $sql = "
        SELECT pa.id_parroquia, pa.nombre, COUNT(DISTINCT pac.cedula) AS total
        FROM parroquia pa
        INNER JOIN paciente pac ON pa.id_parroquia = pac.id_parroquia
        WHERE pac.cedula IN ($subquery)
        " . ($where ? "AND " . implode(" AND ", $where) : "") . "
        GROUP BY pa.id_parroquia, pa.nombre
        ORDER BY pa.nombre
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $parroquias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pacientes atendidos por género (funciona con cualquier filtro)
    $sexo = ['masculino' => 0, 'femenino' => 0];
    $where_genero = $where;
    $params_genero = $params;

    $sqlSexo = "
        SELECT 
            CASE 
                WHEN LOWER(TRIM(pac.genero)) = 'masculino' THEN 'masculino'
                WHEN LOWER(TRIM(pac.genero)) = 'femenino' THEN 'femenino'
                ELSE 'otro'
            END as genero,
            COUNT(DISTINCT pac.cedula) as total
        FROM paciente pac
        WHERE pac.cedula IN ($subquery)
        " . ($where_genero ? "AND " . implode(" AND ", $where_genero) : "") . "
        GROUP BY genero
    ";
    $stmtSexo = $conn->prepare($sqlSexo);
    $stmtSexo->execute($params_genero);
    while ($row = $stmtSexo->fetch(PDO::FETCH_ASSOC)) {
        if ($row['genero'] === 'masculino') $sexo['masculino'] = intval($row['total']);
        if ($row['genero'] === 'femenino') $sexo['femenino'] = intval($row['total']);
        // Si quieres mostrar "otro", puedes agregarlo aquí
    }

    echo json_encode([
        'parroquias' => $parroquias,
        'sexo' => $sexo
    ]);
} catch (PDOException $e) {
    echo json_encode(['parroquias' => [], 'sexo' => [], 'error' => $e->getMessage()]);
}
