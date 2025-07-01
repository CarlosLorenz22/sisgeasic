<?php
header('Content-Type: application/json');

function mes_espanol($mes_num) {
    $meses = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
    ];
    return $meses[$mes_num] ?? $mes_num;
}

try {
    $conn = new PDO("pgsql:host=localhost;dbname=bd_asic", "postgres", "30429913");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';

    // Definir rango de fechas y formato de agrupación
    $where = '';
    $groupFormat = '';
    $params = [];

    if ($filtro === 'mes') {
        $groupFormat = "YYYY-MM";
        $where = "WHERE DATE_TRUNC('month', fecha_consulta) = DATE_TRUNC('month', CURRENT_DATE)";
    } elseif ($filtro === 'semana') {
        $groupFormat = "IYYY-IW";
        $where = "WHERE DATE_TRUNC('week', fecha_consulta) = DATE_TRUNC('week', CURRENT_DATE)";
    } elseif ($filtro === 'dia') {
        $groupFormat = "YYYY-MM-DD";
        $where = "WHERE fecha_consulta::date = CURRENT_DATE";
    } else {
        $groupFormat = "YYYY-MM";
        $where = "";
    }

    // Obtener los labels (meses, semana, día)
    $meses = [];
    $sqlMeses = "SELECT DISTINCT TO_CHAR(fecha_consulta, '$groupFormat') AS periodo FROM historial_consultas $where ORDER BY periodo";
    $stmt = $conn->query($sqlMeses);
    $meses_raw = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $meses_raw[] = $row['periodo'];
    }

    // Para el filtro "todos", mostrar siempre "Mes Año"
    if ($filtro === 'todos') {
        $meses = [];
        foreach ($meses_raw as $periodo) {
            list($anio, $mes) = explode('-', $periodo);
            $nombre_mes = mes_espanol($mes);
            $meses[] = "$nombre_mes $anio";
        }
    } elseif ($filtro === 'mes') {
        // Ejemplo: "Junio 2024"
        $meses = [];
        foreach ($meses_raw as $periodo) {
            list($anio, $mes) = explode('-', $periodo);
            $meses[] = mes_espanol($mes) . " $anio";
        }
    } else {
        // Semana y día: mostrar el label tal cual
        $meses = $meses_raw;
    }

    // Obtener todas las enfermedades presentes en historial_consultas
    $enfermedades = [];
    $stmt = $conn->query("
        SELECT e.id_enfermedad, e.nombre
        FROM enfermedad e
        WHERE e.id_enfermedad IN (SELECT DISTINCT id_enfermedad FROM historial_consultas)
        ORDER BY e.nombre
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $enfermedades[] = [
            'id' => $row['id_enfermedad'],
            'nombre' => $row['nombre'],
            'color' => sprintf('hsl(%d,70%%,60%%)', rand(0,359))
        ];
    }

    // Para cada enfermedad, obtener los casos por periodo
    $datasets = [];
    foreach ($enfermedades as $enf) {
        $data = [];
        foreach ($meses_raw as $periodo) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM historial_consultas
                WHERE id_enfermedad = ? AND TO_CHAR(fecha_consulta, '$groupFormat') = ?
                " . ($where ? str_replace('WHERE', 'AND', $where) : '') . "
            ");
            $stmt->execute([$enf['id'], $periodo]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $data[] = intval($row['total']);
        }
        $datasets[] = [
            'nombre' => $enf['nombre'],
            'casos' => $data,
            'color' => $enf['color']
        ];
    }

    echo json_encode([
        'meses' => $meses,
        'enfermedades' => $datasets
    ]);
} catch (PDOException $e) {
    echo json_encode(['meses' => [], 'enfermedades' => [], 'error' => $e->getMessage()]);

} catch (PDOException $e) {
    echo json_encode(['meses' => [], 'enfermedades' => [], 'error' => $e->getMessage()]);
}
