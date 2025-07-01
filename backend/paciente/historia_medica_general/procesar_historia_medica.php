<?php
// Registrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    die("Error al conectar con la base de datos: " . pg_last_error());
}

// Obtener los datos del formulario
$cedula = $_POST['cedula'];
$centro_asistencial = $_POST['centro_asistencial'];
$fecha_elaboracion = $_POST['fecha_elaboracion'];
$historia_numero = $_POST['historia_numero'];
$motivo_consulta = $_POST['motivo_consulta'];
$patologia = $_POST['patologia'];
$antecedentes_personales = $_POST['antecedentes_personales'];
$antecedentes_familiares = $_POST['antecedentes_familiares'];
$cabeza = $_POST['cabeza'];
$ojos_oidos_nariz_garganta = $_POST['ojos_oidos_nariz_garganta'];
$respiratorio = $_POST['respiratorio'];
$corazon = $_POST['corazon'];
$gastrointestinal = $_POST['gastrointestinal'];
$genitourinario = $_POST['genitourinario'];
$elaborado_por = $_POST['elaborado_por'] ?? null; // Permitir que sea opcional
$cargo = $_POST['cargo'] ?? null; // Permitir que sea opcional

// Obtener el id_medico basado en el nombre completo del médico (elaborado_por), si se proporciona
$id_medico = null;
if (!empty($elaborado_por)) {
    $query_medico = "SELECT id_medico FROM medico WHERE CONCAT(pr_apellido, ' ', sdo_apellido, ' ', pr_nombre, ' ', sdo_nombre) = $1";
    $result_medico = pg_query_params($conn, $query_medico, array($elaborado_por));

    if (!$result_medico) {
        die("Error al consultar el médico: " . pg_last_error());
    }

    if (pg_num_rows($result_medico) > 0) {
        $id_medico = pg_fetch_result($result_medico, 0, 'id_medico');
    } else {
        die("Error: No se encontró un médico con el nombre '$elaborado_por'.");
    }
}

// Verificar si ya existe una historia médica para este paciente
$query = "SELECT id FROM historia_medica WHERE id_paciente = $1";
$result = pg_query_params($conn, $query, array($cedula));

if (!$result) {
    die("Error en la consulta de verificación: " . pg_last_error());
}

if (pg_num_rows($result) > 0) {
    // Actualizar los datos existentes
    $query = "UPDATE historia_medica SET 
        centro_asistencial = $1, fecha_elaboracion = $2, historia_numero = $3, motivo_consulta = $4, patologia = $5, 
        antecedentes_personales = $6, antecedentes_familiares = $7, cabeza = $8, ojos_oidos_nariz_garganta = $9, 
        respiratorio = $10, corazon = $11, gastrointestinal = $12, genitourinario = $13, elaborado_por = $14, 
        cargo = $15, id_medico = $16 WHERE id_paciente = $17";
    $params = array(
        $centro_asistencial, $fecha_elaboracion, $historia_numero, $motivo_consulta, $patologia,
        $antecedentes_personales, $antecedentes_familiares, $cabeza, $ojos_oidos_nariz_garganta,
        $respiratorio, $corazon, $gastrointestinal, $genitourinario, $elaborado_por, $cargo, $id_medico, $cedula
    );
} else {
    // Insertar nuevos datos
    $query = "INSERT INTO historia_medica (
        id_paciente, centro_asistencial, fecha_elaboracion, historia_numero, motivo_consulta, patologia, 
        antecedentes_personales, antecedentes_familiares, cabeza, ojos_oidos_nariz_garganta, respiratorio, 
        corazon, gastrointestinal, genitourinario, elaborado_por, cargo, id_medico
    ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17)";
    $params = array(
        $cedula, $centro_asistencial, $fecha_elaboracion, $historia_numero, $motivo_consulta, $patologia,
        $antecedentes_personales, $antecedentes_familiares, $cabeza, $ojos_oidos_nariz_garganta,
        $respiratorio, $corazon, $gastrointestinal, $genitourinario, $elaborado_por, $cargo, $id_medico
    );
}

// Ejecutar la consulta
$result = pg_query_params($conn, $query, $params);

if (!$result) {
    die("Error al guardar los datos: " . pg_last_error());
}

// Determinar la acción realizada
$accion = (pg_num_rows($result) > 0) ? 'editada' : 'guardada';

// Cerrar la conexión a la base de datos
pg_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Historia Médica</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'La historia médica ha sido <?php echo $accion; ?> correctamente.',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = '/sisgeasic/dashboard.php?modulo=pacientes'; // Redirige al listado de pacientes
        });
    </script>
</body>
</html>
