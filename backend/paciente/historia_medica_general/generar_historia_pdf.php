<?php
require_once __DIR__ . '/../../../vendor/autoload.php'; // Ajusta la ruta si es necesario

use Dompdf\Dompdf;
use Dompdf\Options;

// Recoge los datos enviados por POST
function get_post($key) {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : '';
}

$centro_asistencial = get_post('centro_asistencial');
$fecha_elaboracion = get_post('fecha_elaboracion');
$nombre_paciente = get_post('nombre_paciente');
$historia_numero = get_post('historia_numero');
$cedula = get_post('cedula');
$edad = get_post('edad');
$genero = get_post('genero');
$telefono = get_post('telefono');
$direccion = get_post('direccion');
$peso = get_post('peso');
$talla = get_post('talla');
$motivo_consulta = get_post('motivo_consulta');
$patologia = get_post('patologia');
$antecedentes_personales = get_post('antecedentes_personales');
$antecedentes_familiares = get_post('antecedentes_familiares');
$cabeza = get_post('cabeza');
$ojos_oidos_nariz_garganta = get_post('ojos_oidos_nariz_garganta');
$respiratorio = get_post('respiratorio');
$corazon = get_post('corazon');
$gastrointestinal = get_post('gastrointestinal');
$genitourinario = get_post('genitourinario');
$elaborado_por = get_post('elaborado_por');
$cargo = get_post('cargo');

// Antes de definir $html, convierte el logo a base64:
$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/sisgeasic/assets/img/logo2.png';
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_data = file_get_contents($logo_path);
    $logo_base64 = 'data:image/png;base64,' . base64_encode($logo_data);
}

// HTML para el PDF (estilo similar al formulario web)
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Médica General</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 13px; color: #212529; background: #fff; }
        .container-fluid { width: 100%; padding: 0 10px; }
        .mb-4 { margin-bottom: 1.5rem; }
        .img-fluid { width: 80px; height: auto; display: block; margin-top: -10px; }
        .font-weight-bold { font-weight: bold; }
        h1, h4 { margin: 0.5em 0; }
        h1 { font-size: 1.5em; }
        h4 { font-size: 1.1em; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table-bordered td, .table-bordered th { border: 1px solid #dee2e6; padding: 6px; }
        .table-bordered { border: 1px solid #dee2e6; }
        .form-group { margin-bottom: 1rem; }
        .section-title { font-weight: bold; background: #f8f9fa; padding: 4px 8px; border-radius: 3px; margin-top: 10px; }
        textarea, input[type="text"], input[type="number"], input[type="date"] { width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 4px; font-size: 13px; }
        .firma { margin-top: 40px; }
        .membrete-tabla { width: 100%; }
        .membrete-logo { width: 90px; vertical-align: top; }
        .membrete-texto { text-align: left; vertical-align: top; padding-left: 10px; }
        .membrete-texto h6 { margin: 0; font-size: 12px; }
        .membrete-texto .font-weight-bold { font-size: 13px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <table class="mb-4 membrete-tabla">
            <tr>
                <td class="membrete-logo">
                    <img src="' . $logo_base64 . '" alt="Logo" class="img-fluid" style="margin-top:-25px;">
                </td>
                <td class="membrete-texto">
                    <h6>REPÚBLICA BOLIVARIANA DE VENEZUELA</h6>
                    <h6>MINISTERIO DEL PODER POPULAR PARA EL TRABAJO Y SEGURIDAD SOCIAL</h6>
                    <h6>INSTITUTO VENEZOLANO DE LOS SEGUROS SOCIALES</h6>
                    <h6 class="font-weight-bold">DIRECCIÓN GENERAL DE SALUD</h6>
                </td>
            </tr>
        </table>
        <h1 class="h3 mb-4 text-gray-800" style="text-align:center;">Historia Médica General</h1>
        <table class="table-bordered">
            <tr>
                <td colspan="2"><strong>Centro Asistencial:</strong> ' . $centro_asistencial . '</td>
                <td><strong>Fecha de Elaboración:</strong> ' . $fecha_elaboracion . '</td>
            </tr>
            <tr>
                <td><strong>Apellido y Nombre del Paciente:</strong> ' . $nombre_paciente . '</td>
                <td><strong>Historia N°:</strong> ' . $historia_numero . '</td>
                <td><strong>Cédula de Identidad N°:</strong> ' . $cedula . '</td>
            </tr>
            <tr>
                <td><strong>Edad:</strong> ' . $edad . '</td>
                <td><strong>Género:</strong> ' . ($genero == 'M' ? 'Masculino' : ($genero == 'F' ? 'Femenino' : '')) . '</td>
                <td><strong>Teléfono:</strong> ' . $telefono . '</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong> ' . $direccion . '</td>
                <td><strong>Peso (kg):</strong> ' . $peso . '</td>
                <td><strong>Talla (cm):</strong> ' . $talla . '</td>
            </tr>
        </table>
        <div class="form-group">
            <div class="section-title">Motivo de Consulta:</div>
            <div>' . nl2br($motivo_consulta) . '</div>
        </div>
        <div class="form-group">
            <div class="section-title">Patología:</div>
            <div>' . nl2br($patologia) . '</div>
        </div>
        <div class="form-group">
            <div class="section-title">Antecedentes Personales:</div>
            <div>' . nl2br($antecedentes_personales) . '</div>
        </div>
        <div class="form-group">
            <div class="section-title">Antecedentes Familiares:</div>
            <div>' . nl2br($antecedentes_familiares) . '</div>
        </div>
        <h4 class="mt-4">Examen Funcional</h4>
        <table class="table-bordered">
            <tr>
                <td><strong>Cabeza:</strong></td>
                <td>' . nl2br($cabeza) . '</td>
            </tr>
            <tr>
                <td><strong>Ojos-Oídos-Nariz-Garganta:</strong></td>
                <td>' . nl2br($ojos_oidos_nariz_garganta) . '</td>
            </tr>
            <tr>
                <td><strong>Respiratorio:</strong></td>
                <td>' . nl2br($respiratorio) . '</td>
            </tr>
            <tr>
                <td><strong>Corazón:</strong></td>
                <td>' . nl2br($corazon) . '</td>
            </tr>
            <tr>
                <td><strong>Gastro-Intestinal:</strong></td>
                <td>' . nl2br($gastrointestinal) . '</td>
            </tr>
            <tr>
                <td><strong>Genito-Urinario:</strong></td>
                <td>' . nl2br($genitourinario) . '</td>
            </tr>
        </table>
        <div class="firma">
            <strong>Elaborado por:</strong> ' . $elaborado_por . '<br>
            <strong>Cargo:</strong> ' . $cargo . '
        </div>
    </div>
</body>
</html>
';

// Opciones de Dompdf
$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true); // Para cargar imágenes externas

$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar el PDF
$dompdf->stream('historia_medica_' . $cedula . '.pdf', ['Attachment' => false]);
exit;
