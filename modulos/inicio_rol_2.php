<?php
// Prueba de conexión directa
include_once __DIR__ . '/../backend/conexion.php';
if (!$conn) {
    die('<div style="color:red;">Error de conexión a la base de datos.</div>');
}

// Incluye el modelo
@include_once __DIR__ ."/../models/inicio_rol2_model.php";
if (!isset($consultas_medico)) $consultas_medico = 0;
if (!isset($citas_medico)) $citas_medico = 0;
if (!isset($pacientes_registrados)) $pacientes_registrados = 0;

// Depuración: muestra los valores de las variables
echo "<!-- consultas_medico: $consultas_medico | citas_medico: $citas_medico | pacientes_registrados: $pacientes_registrados -->";
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Bienvenido, Médico</h1>
    <div class="row">
        <!-- Acceso rápido a lista de consultas -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="?modulo=lista_consultas" class="card border-left-primary shadow h-100 py-2 text-decoration-none">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Consultas</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Ver mis consultas
                        <span class="badge badge-primary ml-2"><?php echo $consultas_medico; ?></span>
                    </div>
                </div>
            </a>
        </div>
        <!-- Acceso rápido a pacientes -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="?modulo=pacientes" class="card border-left-success shadow h-100 py-2 text-decoration-none">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pacientes</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Ver pacientes
                        <span class="badge badge-success ml-2"><?php echo $pacientes_registrados; ?></span>
                    </div>
                </div>
            </a>
        </div>
        <!-- Acceso rápido a gestión de citas -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="?modulo=gestion_citas" class="card border-left-info shadow h-100 py-2 text-decoration-none">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Citas</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Gestionar citas
                        <span class="badge badge-info ml-2"><?php echo $citas_medico; ?></span>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <!-- Puedes agregar más accesos rápidos o información relevante aquí -->
</div>
