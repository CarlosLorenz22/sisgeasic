<?php
@include_once __DIR__ . '/../models/dashboard_model.php';

// Valores por defecto si no están definidos
if (!isset($usuarios_en_linea)) $usuarios_en_linea = 0;
if (!isset($medicos_registrados)) $medicos_registrados = 0;
if (!isset($citas_programadas)) $citas_programadas = 0;
if (!isset($pacientes_registrados)) $pacientes_registrados = 0;
?>

<!-- Contenedor principal -->
<div class="container-fluid">
    <!-- Fila de tarjetas -->
    <div class="row">
        <!-- Tarjeta 1 -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Usuarios en línea
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $usuarios_en_linea; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2 -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Médicos registrados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $medicos_registrados; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3 -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Citas programadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $citas_programadas; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4 -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pacientes registrados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pacientes_registrados; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>