<?php
include_once __DIR__ . '/../models/inicio_rol3_model.php';
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Bienvenido, Personal Administrativo</h1>
    <div class="row">
        <!-- Acceso rápido a pacientes -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="?modulo=pacientes" class="card border-left-primary shadow h-100 py-2 text-decoration-none">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pacientes</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Ver y gestionar pacientes</div>
                    <div class="mt-2 text-primary"><b><?php echo $totalPacientes; ?></b> registrados</div>
                </div>
            </a>
        </div>
        <!-- Acceso rápido a citas -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="?modulo=gestion_citas" class="card border-left-success shadow h-100 py-2 text-decoration-none">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Citas</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Gestionar citas</div>
                    <div class="mt-2 text-success"><b><?php echo $totalCitas; ?></b> próximas</div>
                </div>
            </a>
        </div>
        <!-- Acceso rápido a consultas -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="?modulo=consultas" class="card border-left-info shadow h-100 py-2 text-decoration-none">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Consultas</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Ver y gestionar consultas</div>
                    <div class="mt-2 text-info"><b><?php echo $totalConsultas; ?></b> próximas</div>
                </div>
            </a>
        </div>
    </div>