<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../backend/conexion.php'; // $conn es conexión PostgreSQL

$id_medico = $_SESSION['id_medico'] ?? 0;

// Depuración: muestra el id_medico obtenido de la sesión
echo "<!-- id_medico en modelo: $id_medico -->";

if (!$id_medico) {
    echo "<div style='color:red;'>No se encontró id_medico en la sesión. Verifica el login.</div>";
}

// Consultas asociadas al médico
$res = pg_query($conn, "SELECT COUNT(*) FROM consulta WHERE id_medico = $id_medico");
$consultas_medico = $res ? intval(pg_fetch_result($res, 0, 0)) : 0;

// Citas asociadas al médico
$res = pg_query($conn, "SELECT COUNT(*) FROM cita WHERE id_medico = $id_medico");
$citas_medico = $res ? intval(pg_fetch_result($res, 0, 0)) : 0;

// Pacientes registrados (todos)
$res = pg_query($conn, "SELECT COUNT(*) FROM paciente");
$pacientes_registrados = $res ? intval(pg_fetch_result($res, 0, 0)) : 0;
?>