<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../conexion.php';

$cedula = $_GET['cedula'] ?? '';
$citas = [];

if ($cedula && $conn) {
    // Citas vigentes
    $sql1 = "SELECT c.id, c.titulo, c.fecha_inicio, c.fecha_fin, 
                    m.pr_nombre, m.sdo_nombre, m.pr_apellido, m.sdo_apellido,
                    e.nombre_especialidad, c.status, 'vigente' as tipo
             FROM cita c
             LEFT JOIN medico m ON c.id_medico = m.id_medico
             LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
             WHERE c.cedula = $1";
    $result1 = pg_query_params($conn, $sql1, [$cedula]);
    while ($row = pg_fetch_assoc($result1)) {
        $nombre_medico = trim($row['pr_nombre'] . ' ' . ($row['sdo_nombre'] ?? '') . ' ' . $row['pr_apellido'] . ' ' . ($row['sdo_apellido'] ?? ''));
        $citas[] = [
            'fecha' => $row['fecha_inicio'],
            'hora' => date('H:i', strtotime($row['fecha_inicio'])),
            'medico' => $nombre_medico,
            'especialidad' => $row['nombre_especialidad'],
            'estado' => $row['status'] === 'atendida' ? 'Atendida' : 'Vigente',
            'tipo' => $row['tipo']
        ];
    }
    // Citas atendidas
    $sql2 = "SELECT c.id, c.titulo, c.fecha_inicio, c.fecha_fin, 
                    m.pr_nombre, m.sdo_nombre, m.pr_apellido, m.sdo_apellido,
                    e.nombre_especialidad, c.status, 'atendida' as tipo
             FROM cita_atendida c
             LEFT JOIN medico m ON c.id_medico = m.id_medico
             LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
             WHERE c.cedula = $1";
    $result2 = pg_query_params($conn, $sql2, [$cedula]);
    while ($row = pg_fetch_assoc($result2)) {
        $nombre_medico = trim($row['pr_nombre'] . ' ' . ($row['sdo_nombre'] ?? '') . ' ' . $row['pr_apellido'] . ' ' . ($row['sdo_apellido'] ?? ''));
        $citas[] = [
            'fecha' => $row['fecha_inicio'],
            'hora' => date('H:i', strtotime($row['fecha_inicio'])),
            'medico' => $nombre_medico,
            'especialidad' => $row['nombre_especialidad'],
            'estado' => 'Atendida',
            'tipo' => $row['tipo']
        ];
    }
}

echo json_encode(['ok' => true, 'citas' => $citas]);
