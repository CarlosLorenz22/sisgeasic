<?php
include_once __DIR__ . '/../backend/conexion.php';

// Contar pacientes
$queryPacientes = "SELECT COUNT(*) as total FROM paciente";
$resultPacientes = pg_query($conn, $queryPacientes);
$totalPacientes = pg_fetch_assoc($resultPacientes)['total'];

// Contar citas futuras o de hoy
$queryCitas = "SELECT COUNT(*) as total FROM cita WHERE fecha_inicio >= CURRENT_DATE";
$resultCitas = pg_query($conn, $queryCitas);
$totalCitas = pg_fetch_assoc($resultCitas)['total'];

// Contar consultas futuras o de hoy
$queryConsultas = "SELECT COUNT(*) as total FROM consulta WHERE fecha_consulta >= CURRENT_DATE";
$resultConsultas = pg_query($conn, $queryConsultas);
$totalConsultas = pg_fetch_assoc($resultConsultas)['total'];
?>
