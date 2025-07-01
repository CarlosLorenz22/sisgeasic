<?php
include_once("../templates/head.php");
include_once("../templates/sidebar.php");
// Registrar errores en un archivo
ini_set('log_errors', 1);
ini_set('error_log', '/opt/lampp/htdocs/SisgeAsic/logs/php_errors.log');

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar variables
$nombre_completo = $cedula = $edad = $genero = $telefono = $direccion = "";
$peso = $talla = ""; // Agregar variables para peso y talla
$fecha_elaboracion = date("Y-m-d"); // Fecha actual

// Verificar si se recibe el parámetro 'cedula'
if (isset($_GET['cedula'])) {
    $cedula = $_GET['cedula'];

    // Conexión a la base de datos directamente
    $conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
    if (!$conn) {
        die("Error al conectar con la base de datos: " . pg_last_error());
    }

    // Consultar los datos del paciente (agregar peso y talla)
    $query = "SELECT cedula, CONCAT(pr_apellido, ' ', pr_nombre) AS nombre_completo, genero, fecha_nacimiento, telefono, direccion, peso, talla 
              FROM paciente WHERE cedula = $1";
    $result = pg_query_params($conn, $query, array($cedula));

    if (!$result) {
        die("Error en la consulta: " . pg_last_error());
    }

    $paciente = pg_fetch_assoc($result);
    if ($paciente) {
        $nombre_completo = $paciente['nombre_completo'];
        $cedula = $paciente['cedula'];
        $genero = $paciente['genero'];
        $telefono = $paciente['telefono'];
        $direccion = $paciente['direccion'];
        $peso = $paciente['peso'];     // Asignar peso
        $talla = $paciente['talla'];   // Asignar talla

        // Calcular la edad a partir de la fecha de nacimiento
        $fecha_nacimiento = new DateTime($paciente['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nacimiento)->y;
    }

    pg_free_result($result);

    // Consultar los datos de la historia médica si existen
    $query_historia = "SELECT * FROM historia_medica WHERE id_paciente = $1";
    $result_historia = pg_query_params($conn, $query_historia, array($cedula));

    if (!$result_historia) {
        die("Error en la consulta de historia médica: " . pg_last_error());
    }

    if (pg_num_rows($result_historia) > 0) {
        $historia = pg_fetch_assoc($result_historia);
        $centro_asistencial = $historia['centro_asistencial'];
        $fecha_elaboracion = $historia['fecha_elaboracion'];
        $historia_numero = $historia['historia_numero']; // Nueva columna
        $motivo_consulta = $historia['motivo_consulta'];
        $patologia = $historia['patologia'];
        $antecedentes_personales = $historia['antecedentes_personales'];
        $antecedentes_familiares = $historia['antecedentes_familiares'];
        $cabeza = $historia['cabeza'];
        $ojos_oidos_nariz_garganta = $historia['ojos_oidos_nariz_garganta'];
        $respiratorio = $historia['respiratorio'];
        $corazon = $historia['corazon'];
        $gastrointestinal = $historia['gastrointestinal'];
        $genitourinario = $historia['genitourinario'];
        $elaborado_por = $historia['elaborado_por'];
        $cargo = $historia['cargo'];
    } else {
        // Inicializar variables vacías si no hay datos
        $centro_asistencial = $historia_numero = $motivo_consulta = $patologia = "";
        $antecedentes_personales = $antecedentes_familiares = $cabeza = $ojos_oidos_nariz_garganta = "";
        $respiratorio = $corazon = $gastrointestinal = $genitourinario = $elaborado_por = $cargo = "";
    }

    pg_free_result($result_historia);

    // Consultar los médicos desde la base de datos
    $query_medicos = "SELECT id_medico, CONCAT(pr_apellido, ' ', sdo_apellido, ' ', pr_nombre, ' ', sdo_nombre) AS nombre_completo FROM medico";
    $result_medicos = pg_query($conn, $query_medicos);

    if (!$result_medicos) {
        die("Error al consultar los médicos: " . pg_last_error());
    }

    $medicos = [];
    while ($row = pg_fetch_assoc($result_medicos)) {
        $medicos[] = $row;
    }

    pg_free_result($result_medicos);
    pg_close($conn);
} else {
    // Redirigir al listado de pacientes si no se recibe el parámetro 'cedula'
    header("Location: paciente.php");
    exit();
}
?>


<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php include_once("../templates/topbar.php"); ?>

        <!-- Contenedor principal -->
        <div class="container-fluid">
            <!-- Encabezado con logo -->
            <div class="d-flex align-items-center mb-4 flex-wrap">
                <img src="/sisgeasic/assets/img/logo2.png" alt="Logo" class="img-fluid" style="width: 80px; height: auto; margin-right: 20px;">
                <div>
                    <h6 class="mb-0">REPÚBLICA BOLIVARIANA DE VENEZUELA</h6>
                    <h6 class="mb-0">MINISTERIO DEL PODER POPULAR PARA EL TRABAJO Y SEGURIDAD SOCIAL</h6>
                    <h6 class="mb-0">INSTITUTO VENEZOLANO DE LOS SEGUROS SOCIALES</h6>
                    <h6 class="mb-0 font-weight-bold">DIRECCIÓN GENERAL DE SALUD</h6>
                </div>
            </div>
            <!-- Contenido específico de Historia Médica -->
            <h1 class="h3 mb-4 text-gray-800">Historia Médica General</h1>
            <form action="/sisgeasic/backend/paciente/historia_medica_general/procesar_historia_medica.php" method="POST">
                <!-- Información del paciente -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <td colspan="2"><strong>Centro Asistencial:</strong> <input type="text" name="centro_asistencial" class="form-control" placeholder="Area de Salud Integral Comunitaria Maria Genoveva Guerrero Ramos" value="<?php echo $centro_asistencial; ?>"></td>
                            <td><strong>Fecha de Elaboración:</strong> <input type="date" name="fecha_elaboracion" class="form-control" value="<?php echo $fecha_elaboracion; ?>" readonly></td>
                        </tr>
                        <tr>
                            <td><strong>Apellido y Nombre del Paciente:</strong> 
                                <input type="text" name="nombre_paciente" class="form-control" value="<?php echo $nombre_completo; ?>" readonly>
                            </td>
                            <td><strong>Historia N°:</strong> <input type="text" name="historia_numero" class="form-control" value="<?php echo $historia_numero; ?>"></td>
                            <td><strong>Cédula de Identidad N°:</strong> 
                                <input type="text" name="cedula" class="form-control" value="<?php echo $cedula; ?>" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Edad:</strong> 
                                <input type="number" name="edad" class="form-control" value="<?php echo $edad; ?>" readonly>
                            </td>
                            <td><strong>Género:</strong>
                                <div>
                                    <label><input type="radio" name="genero" value="M" <?php echo ($genero === 'M') ? 'checked' : ''; ?> disabled> Masculino</label>
                                    <label><input type="radio" name="genero" value="F" <?php echo ($genero === 'F') ? 'checked' : ''; ?> disabled> Femenino</label>
                                </div>
                            </td>
                            <td><strong>Teléfono:</strong> 
                                <input type="text" name="telefono" class="form-control" value="<?php echo $telefono; ?>" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dirección:</strong> 
                                <textarea name="direccion" class="form-control" readonly><?php echo $direccion; ?></textarea>
                            </td>
                            <td><strong>Peso (kg):</strong>
                                <input type="text" name="peso" class="form-control" value="<?php echo $peso; ?>" readonly>
                            </td>
                            <td><strong>Talla (cm):</strong>
                                <input type="text" name="talla" class="form-control" value="<?php echo $talla; ?>" readonly>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Motivo de consulta -->
                <div class="form-group">
                    <label><strong>Motivo de Consulta:</strong></label>
                    <textarea name="motivo_consulta" class="form-control" rows="3"><?php echo $motivo_consulta; ?></textarea>
                </div>

                <!-- Enfermedad actual -->
                <div class="form-group">
                    <label><strong>Patologia:</strong></label>
                    <textarea name="patologia" class="form-control" rows="3"><?php echo $patologia; ?></textarea>
                </div>

                <!-- Antecedentes personales -->
                <div class="form-group">
                    <label><strong>Antecedentes Personales:</strong></label>
                    <textarea name="antecedentes_personales" class="form-control" rows="3"><?php echo $antecedentes_personales; ?></textarea>
                </div>

                <!-- Antecedentes familiares -->
                <div class="form-group">
                    <label><strong>Antecedentes Familiares:</strong></label>
                    <textarea name="antecedentes_familiares" class="form-control" rows="3"><?php echo $antecedentes_familiares; ?></textarea>
                </div>

                <!-- Examen funcional -->
                <h4 class="mt-4">Examen Funcional</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Cabeza:</strong></td>
                            <td><textarea name="cabeza" class="form-control" rows="2"><?php echo $cabeza; ?></textarea></td>
                        </tr>
                        <tr>
                            <td><strong>Ojos-Oídos-Nariz-Garganta:</strong></td>
                            <td><textarea name="ojos_oidos_nariz_garganta" class="form-control" rows="2"><?php echo $ojos_oidos_nariz_garganta; ?></textarea></td>
                        </tr>
                        <tr>
                            <td><strong>Respiratorio:</strong></td>
                            <td><textarea name="respiratorio" class="form-control" rows="2"><?php echo $respiratorio; ?></textarea></td>
                        </tr>
                        <tr>
                            <td><strong>Corazón:</strong></td>
                            <td><textarea name="corazon" class="form-control" rows="2"><?php echo $corazon; ?></textarea></td>
                        </tr>
                        <tr>
                            <td><strong>Gastro-Intestinal:</strong></td>
                            <td><textarea name="gastrointestinal" class="form-control" rows="2"><?php echo $gastrointestinal; ?></textarea></td>
                        </tr>
                        <tr>
                            <td><strong>Genito-Urinario:</strong></td>
                            <td><textarea name="genitourinario" class="form-control" rows="2"><?php echo $genitourinario; ?></textarea></td>
                        </tr>
                    </table>
                </div>

                <!-- Firma -->
                <div class="form-group">
                    <label for="elaborado_por"><strong>Elaborado por:</strong></label>
                    <input type="text" id="buscar_elaborado_por" class="form-control mb-2" placeholder="Médico...">
                    <select name="elaborado_por" id="elaborado_por" class="form-control">
                        <option value="">Seleccione un médico</option>
                        <?php foreach ($medicos as $medico): ?>
                            <option value="<?php echo $medico['nombre_completo']; ?>" <?php echo ($elaborado_por === $medico['nombre_completo']) ? 'selected' : ''; ?>>
                                <?php echo $medico['nombre_completo']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <script>
                    // Filtrar médicos en el campo "Elaborado por"
                    document.getElementById('buscar_elaborado_por').addEventListener('input', function () {
                        const filtro = this.value.toLowerCase();
                        const opciones = document.querySelectorAll('#elaborado_por option');
                        opciones.forEach(opcion => {
                            const texto = opcion.textContent.toLowerCase();
                            opcion.style.display = texto.includes(filtro) ? '' : 'none';
                        });
                    });
                </script>
                <div class="form-group">
                    <label><strong>Cargo:</strong></label>
                    <input type="text" name="cargo" class="form-control" value="<?php echo $cargo; ?>">
                </div>
                <!-- Botón de envío -->
                <button type="submit" class="btn btn-primary">Guardar Historia Médica</button>
            </form>
            <!-- Botón para generar y descargar PDF alineado a la derecha -->
            <div class="text-right mt-2">
                <form id="form_pdf" action="/sisgeasic/backend/paciente/historia_medica_general/generar_historia_pdf.php" method="POST" target="_blank" style="display:inline;">
                    <input type="hidden" name="centro_asistencial" value="<?php echo htmlspecialchars($centro_asistencial); ?>">
                    <input type="hidden" name="fecha_elaboracion" value="<?php echo htmlspecialchars($fecha_elaboracion); ?>">
                    <input type="hidden" name="nombre_paciente" value="<?php echo htmlspecialchars($nombre_completo); ?>">
                    <input type="hidden" name="historia_numero" value="<?php echo htmlspecialchars($historia_numero); ?>">
                    <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>">
                    <input type="hidden" name="edad" value="<?php echo htmlspecialchars($edad); ?>">
                    <input type="hidden" name="genero" value="<?php echo htmlspecialchars($genero); ?>">
                    <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">
                    <input type="hidden" name="direccion" value="<?php echo htmlspecialchars($direccion); ?>">
                    <input type="hidden" name="peso" value="<?php echo htmlspecialchars($peso); ?>">
                    <input type="hidden" name="talla" value="<?php echo htmlspecialchars($talla); ?>">
                    <input type="hidden" name="motivo_consulta" value="<?php echo htmlspecialchars($motivo_consulta); ?>">
                    <input type="hidden" name="patologia" value="<?php echo htmlspecialchars($patologia); ?>">
                    <input type="hidden" name="antecedentes_personales" value="<?php echo htmlspecialchars($antecedentes_personales); ?>">
                    <input type="hidden" name="antecedentes_familiares" value="<?php echo htmlspecialchars($antecedentes_familiares); ?>">
                    <input type="hidden" name="cabeza" value="<?php echo htmlspecialchars($cabeza); ?>">
                    <input type="hidden" name="ojos_oidos_nariz_garganta" value="<?php echo htmlspecialchars($ojos_oidos_nariz_garganta); ?>">
                    <input type="hidden" name="respiratorio" value="<?php echo htmlspecialchars($respiratorio); ?>">
                    <input type="hidden" name="corazon" value="<?php echo htmlspecialchars($corazon); ?>">
                    <input type="hidden" name="gastrointestinal" value="<?php echo htmlspecialchars($gastrointestinal); ?>">
                    <input type="hidden" name="genitourinario" value="<?php echo htmlspecialchars($genitourinario); ?>">
                    <input type="hidden" name="elaborado_por" value="<?php echo htmlspecialchars($elaborado_por); ?>">
                    <input type="hidden" name="cargo" value="<?php echo htmlspecialchars($cargo); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Ver/Descargar PDF
                    </button>
                </form>
            </div>
            <!-- Fin botón PDF -->
        </div>
        <!-- Fin container-fluid -->

    </div> <!-- Fin #content -->
</div> <!-- Fin #content-wrapper -->

<?php include_once("../templates/scripts.php"); ?>
</body>
</html>