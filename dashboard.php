<?php
session_start(); // Esto debe ser lo primero

// Debug temporal
//echo "<pre>DEBUG: "; var_dump($_SESSION); echo "</pre>";

// Verifica el rol antes de cargar cualquier template
$id_rol = $_SESSION['id_rol'] ?? null;

// Definir módulos permitidos por rol
$modulos_por_rol = [
    1 => [ // Administrador
        'inicio', 'lista_consultas', 'pacientes', 'agregar_paciente', 'historia_medica_general',
        'medico', 'gestion_citas', 'citas', 'usuario', 'ver_paciente', 'enfermedades',
        'especialidades', 'reportes', 'generar_reporte_morbilidad', 'estadistica'
    ],
    2 => [ // Médico
        'inicio', 'lista_consultas', 'pacientes', 'agregar_paciente', 'historia_medica_general', 'gestion_citas', 'citas', 'ver_paciente'
    ],
    3 => [ // Personal administrativo
        'inicio', 'lista_consultas', 'pacientes', 'agregar_paciente', 'gestion_citas', 'citas', 'ver_paciente'
    ]
    // Agrega más roles si es necesario
];

// Si el rol no está definido, no permitir acceso NI cargar el menú
if (!$id_rol || !isset($modulos_por_rol[$id_rol])) {
    echo "<div class='alert alert-danger' style='margin:2rem;'>Acceso no autorizado</div>";
    exit;
}

// Obtener el nombre real del usuario desde la sesión
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';

require 'templates/head.php';
require 'templates/sidebar.php';

// Lista de módulos permitidos para el rol actual
$modulos_permitidos = $modulos_por_rol[$id_rol];
?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php
        // Pasa el nombre real del usuario a la plantilla topbar
        $nombre_usuario_topbar = $nombre_usuario;
        require 'templates/topbar.php';
        $modulo = $_GET['modulo'] ?? 'inicio';

        // Personalizar la pantalla de inicio según el rol
        if ($modulo === 'inicio') {
            $ruta_inicio = "modulos/inicio_rol_$id_rol.php";
            if (file_exists($ruta_inicio)) {
                include $ruta_inicio;
            } else {
                include "modulos/inicio.php"; // Fallback
            }
        } elseif (in_array($modulo, $modulos_permitidos)) {
            $ruta = "modulos/$modulo.php";
            if (file_exists($ruta)) {
                include $ruta;
            } else {
                echo "<div class='alert alert-danger'>Módulo no encontrado</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Módulo no permitido</div>";
        }
        ?>
    </div>
</div>
<?php
require 'templates/scripts.php';
?>