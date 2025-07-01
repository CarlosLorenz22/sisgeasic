<?php
// Ejemplo de conexión, ajusta según tu sistema
include_once __DIR__ . '/../backend/conexion.php'; // Asegúrate de que $conn esté definido y conectado a PostgreSQL

// Usuarios en línea (usuarios con sesion_activa = true)
$res = pg_query($conn, "SELECT COUNT(*) FROM usuario WHERE sesion_activa = true");
$usuarios_en_linea = $res ? intval(pg_fetch_result($res, 0, 0)) : 0;

// Médicos registrados
$res = pg_query($conn, "SELECT COUNT(*) FROM medico");
$medicos_registrados = $res ? intval(pg_fetch_result($res, 0, 0)) : 0;

// Citas programadas (citas con fecha_inicio hoy o en el futuro)
$res = pg_query($conn, "SELECT COUNT(*) FROM cita WHERE fecha_inicio >= CURRENT_DATE");
$citas_programadas = $res ? intval(pg_fetch_result($res, 0, 0)) : 0;

// Pacientes registrados
$res = pg_query($conn, "SELECT COUNT(*) FROM paciente");
$pacientes_registrados = $res ? intval(pg_fetch_result($res, 0, 0)) : 0;
?>