<?php
session_start();
$id_rol = $_SESSION['id_rol'] ?? null;
?>

<!------------------------------------- Barra lateral de navegación --------------------------------------->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Logo y nombre del sistema -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/sisgeasic/dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="/sisgeasic/assets/img/logoedit.png" alt="logo" class="logo" width="70px" height="auto">
                </div>
                <div class="sidebar-brand-text mx-3">SisgeAsic</div>
            </a>
            <!------------------------------------------ Separador --------------------------------------------->
            <hr class="sidebar-divider my-0">
            <!-------------------------------------- Enlace al Dashboard --------------------------------------->
            <li class="nav-item active">
                <a class="nav-link" href="/sisgeasic/dashboard.php?modulo=inicio">
                    <i class="fa-solid fa-house-medical"></i>
                    <span>Panel</span>
                </a>
            </li>
            <!----------------------------------------- Separador ------------------------------------------------->
            <hr class="sidebar-divider">
            <!------------------------------------- Encabezado de módulos ----------------------------------------->
            <div class="sidebar-heading">Módulos</div>
            <!------------------------------------- Módulo de Consultas ------------------------------------------->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseConsultas" aria-expanded="false" aria-controls="collapseConsultas">
                    <i class="fa-solid fa-stethoscope"></i>
                    <span>Consultas</span>
                </a>
                <div id="collapseConsultas" class="collapse" aria-labelledby="headingConsultas">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="/sisgeasic/dashboard.php?modulo=lista_consultas">Lista de Consultas</a>
                    </div>
                </div>
            </li>
            <!---------------------------------------- Módulo de Pacientes ---------------------------------------->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePacientes" aria-expanded="false" aria-controls="collapsePacientes">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    <span>Pacientes</span>
                </a>
                <div id="collapsePacientes" class="collapse" aria-labelledby="headingPacientes">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="/sisgeasic/dashboard.php?modulo=pacientes">Lista de pacientes</a>
                        <a class="collapse-item" href="/sisgeasic/dashboard.php?modulo=agregar_paciente">Agregar paciente</a>
                        <!--<a class="collapse-item" href="/sisgeasic/modulos/historias_medicas.php">Historias Médicas</a>-->
                        <div class="collapse-divider"></div>
                        <!--<h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item" href="/sisgeasic/dashboard.php?modulo=historia_medica_general">Historia Medica</a> -->
                    </div>
                </div>
            </li>
            <!------------------------------------------- Módulo de Citas ----------------------------------------->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCitas" aria-expanded="true" aria-controls="collapseCitas">
                    <i class="fa-solid fa-notes-medical"></i>
                    <span>Citas</span>
                </a>
                <div id="collapseCitas" class="collapse" aria-labelledby="headingCitas">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="/sisgeasic/dashboard.php?modulo=gestion_citas">Gestión de Citas</a>
                        <a class="collapse-item" href="/sisgeasic/dashboard.php?modulo=citas">Calendario de Citas</a>
                    </div>
                </div>
            </li>
            <!---------------------------------------------- Separador -------------------------------------------->
            <hr class="sidebar-divider">
            <!-- Opciones de Administrador -->
            <?php if ($id_rol == 1): ?>
            <div class="sidebar-heading">Opciones de Administrador</div>
            <!-- Módulo de Usuarios -->
            <li class="nav-item">
                <a class="nav-link" href="/sisgeasic/dashboard.php?modulo=usuario">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </a>
            </li>
             <!-- Módulo de Médicos: solo visible para administrador -->
            <?php if ($id_rol == 1): ?>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fa fa-user-md" aria-hidden="true"></i>
                    <span>Medicos</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="/sisgeasic/dashboard.php?modulo=medico">Lista de Medicos</a>
                    </div>
                </div>
            </li>
            <?php endif; ?>
            <!----------------------------------------- Módulo de Enfermedades ------------------------------------>
            <li class="nav-item">
                <a class="nav-link" href="/sisgeasic/dashboard.php?modulo=enfermedades">
                    <i class="fa-solid fa-virus"></i>
                    <span>Enfermedades</span></a>
            </li>
            <!-------------------------------------- Módulo de Especialidades ------------------------------------->
            <li class="nav-item">
                <a class="nav-link" href="/sisgeasic/dashboard.php?modulo=especialidades">
                    <i class="fa-solid fa-notes-medical"></i>
                    <span>Especialidades</span></a>
            </li>
            <!---------------------------------------- Módulo de Reportes ----------------------------------------->
            <li class="nav-item">
                <a class="nav-link" href="/sisgeasic/dashboard.php?modulo=reportes">
                    <i class="fa-solid fa-file-medical"></i>
                    <span>Reportes</span></a>
            </li>
            <!------------------------------------------- Separador ----------------------------------------------->
            <!--------------------------------------- Módulo de Estadísticas -------------------------------------->
            <li class="nav-item">
                <a class="nav-link" href="/sisgeasic/dashboard.php?modulo=estadistica">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Estadisticas</span></a>
            </li>
            <?php endif; ?>
            <!--<hr class="sidebar-divider d-none d-md-block"> -->
            <!-------------------------------- Botón para colapsar la barra lateral ------------------------------->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
