<?php
// Conexión directa a la base de datos PostgreSQL (ajusta los datos según tu entorno)
$host = "localhost";
$user = "postgres";
$pass = "30429913"; // Cambia si tienes contraseña
$db = "bd_asic"; // Cambia por el nombre real de tu base de datos

$conn = pg_connect("host=$host dbname=$db user=$user password=$pass");
if (!$conn) {
    die("Error de conexión: " . pg_last_error());
}

$id = $_POST['id'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$id_especialidad = $_POST['id_especialidad'] ?? '';
$id_medico = $_POST['id_medico'] ?? '';

// Depuración temporal: imprime los valores recibidos
error_log("id: $id, titulo: $titulo, fecha_inicio: $fecha_inicio, fecha_fin: $fecha_fin, id_especialidad: $id_especialidad, id_medico: $id_medico");

if (
    $id && $titulo && $fecha_inicio && $fecha_fin &&
    $id_especialidad && $id_medico &&
    is_numeric($id_especialidad) && is_numeric($id_medico) &&
    $id_especialidad !== 'undefined' && $id_medico !== 'undefined'
) {
    // Usa el nombre correcto de la tabla: 'cita'
    $sql = "UPDATE cita SET titulo=$1, fecha_inicio=$2, fecha_fin=$3, id_especialidad=$4, id_medico=$5 WHERE id=$6";
    $result = pg_query_params($conn, $sql, array($titulo, $fecha_inicio, $fecha_fin, $id_especialidad, $id_medico, $id));
    if ($result) {
        echo "Cita actualizada correctamente";
    } else {
        echo "Error al actualizar la cita: " . pg_last_error($conn);
    }
} else {
    // También muestra los valores en la respuesta para depuración
    echo "Datos incompletos o inválidos: ";
    echo "id=$id, titulo=$titulo, fecha_inicio=$fecha_inicio, fecha_fin=$fecha_fin, id_especialidad=$id_especialidad, id_medico=$id_medico";
}
pg_close($conn);