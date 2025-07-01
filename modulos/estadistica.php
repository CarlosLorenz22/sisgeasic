<!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Estadísticas</h1>
                    <p class="mb-4">Visualización de estadísticas de pacientes atendidos por parroquia y por sexo.</p>

                    <!-- Selector de tipo de estadística -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="tipoEstadisticaSelect">Tipo de Estadística:</label>
                            <select id="tipoEstadisticaSelect" class="form-control">
                                <option value="pacientes">Pacientes</option>
                                <option value="enfermedades">Enfermedades por mes</option>
                                <option value="consultas">Consultas diarias por especialidad</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filtro por Estado, Municipio y Parroquia -->
                    <div class="row mb-4" id="filtrosUbicacion">
                        <div class="col-md-4">
                            <label for="estadoSelect">Seleccione un Estado:</label>
                            <select id="estadoSelect" class="form-control">
                                <option value="">Seleccione un estado</option>
                                <!-- Opciones dinámicas cargadas desde el servidor -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="municipioSelect">Seleccione un Municipio:</label>
                            <select id="municipioSelect" class="form-control" disabled>
                                <option value="">Seleccione un municipio</option>
                                <!-- Opciones dinámicas cargadas desde el servidor -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="parroquiaSelect">Seleccione una Parroquia:</label>
                            <select id="parroquiaSelect" class="form-control" disabled>
                                <option value="">Seleccione una parroquia</option>
                                <!-- Opciones dinámicas cargadas desde el servidor -->
                            </select>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row" id="estadisticaPacientes">
                        <!-- Pacientes por Parroquia -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Pacientes Atendidos por Parroquia</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="patientsByParishChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pacientes por Sexo -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Pacientes por Sexo</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie">
                                        <!-- Chart.js se usa aquí -->
                                        <canvas id="patientsByGenderChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas de Enfermedades por mes -->
                    <div class="row d-none" id="estadisticaEnfermedades">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Enfermedades por Mes</h6>
                                    <div class="btn-group mt-2" role="group" aria-label="Filtros">
                                        <button type="button" class="btn btn-primary btn-sm" id="btnTodosMeses">Todos los meses</button>
                                        <button type="button" class="btn btn-secondary btn-sm" id="btnMesActual">Mes actual</button>
                                        <button type="button" class="btn btn-secondary btn-sm" id="btnSemanaActual">Semana actual</button>
                                        <button type="button" class="btn btn-secondary btn-sm" id="btnDiaActual">Día actual</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="enfermedadesPorMesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas de Consultas diarias por especialidad -->
                    <div class="row d-none" id="estadisticaConsultas">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Consultas Diarias por Especialidad</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="consultasPorEspecialidadChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

                <!-- Agrega esto antes de tu <script> de estadísticas -->
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    let pacientesParroquiaChart = null;
                    let pacientesGeneroChart = null;
                    let enfermedadesMesChart = null;
                    let consultasEspecialidadChart = null;
                    let filtroEnfermedad = 'todos';

                    document.addEventListener('DOMContentLoaded', function () {
                        const estadoSelect = document.getElementById('estadoSelect');
                        const municipioSelect = document.getElementById('municipioSelect');
                        const parroquiaSelect = document.getElementById('parroquiaSelect');
                        const tipoEstadisticaSelect = document.getElementById('tipoEstadisticaSelect');

                        // Mostrar/ocultar secciones según el tipo de estadística
                        tipoEstadisticaSelect.addEventListener('change', function () {
                            const tipo = this.value;
                            document.getElementById('estadisticaPacientes').classList.toggle('d-none', tipo !== 'pacientes');
                            document.getElementById('filtrosUbicacion').classList.toggle('d-none', tipo !== 'pacientes');
                            document.getElementById('estadisticaEnfermedades').classList.toggle('d-none', tipo !== 'enfermedades');
                            document.getElementById('estadisticaConsultas').classList.toggle('d-none', tipo !== 'consultas');

                            if (tipo === 'enfermedades') {
                                cargarEstadisticasEnfermedades();
                            } else if (tipo === 'consultas') {
                                cargarEstadisticasConsultas();
                            }
                        });

                        // Cargar estados al iniciar la página
                        fetch('/sisgeasic/backend/estadisticas/cargar_estados.php')
                            .then(response => response.json())
                            .then(data => {
                                data.estados.forEach(estado => {
                                    const option = document.createElement('option');
                                    option.value = estado.id_estado;
                                    option.textContent = estado.nombre;
                                    estadoSelect.appendChild(option);
                                });
                            });

                        // Cargar municipios al seleccionar un estado
                        estadoSelect.addEventListener('change', function () {
                            const estadoId = this.value;
                            municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                            parroquiaSelect.innerHTML = '<option value="">Seleccione una parroquia</option>';
                            municipioSelect.disabled = !estadoId;
                            parroquiaSelect.disabled = true;

                            if (estadoId) {
                                fetch(`/sisgeasic/backend/estadisticas/cargar_municipios.php?estado_id=${estadoId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        data.municipios.forEach(municipio => {
                                            const option = document.createElement('option');
                                            option.value = municipio.id_municipio;
                                            option.textContent = municipio.nombre;
                                            municipioSelect.appendChild(option);
                                        });
                                    });
                            }
                        });

                        // Cargar parroquias al seleccionar un municipio
                        municipioSelect.addEventListener('change', function () {
                            const municipioId = this.value;
                            parroquiaSelect.innerHTML = '<option value="">Seleccione una parroquia</option>';
                            parroquiaSelect.disabled = !municipioId;

                            if (municipioId) {
                                fetch(`/sisgeasic/backend/estadisticas/cargar_parroquias.php?municipio_id=${municipioId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        data.parroquias.forEach(parroquia => {
                                            const option = document.createElement('option');
                                            option.value = parroquia.id_parroquia;
                                            option.textContent = parroquia.nombre;
                                            parroquiaSelect.appendChild(option);
                                        });
                                    });
                            }
                        });

                        // Actualizar estadísticas al seleccionar una parroquia
                        parroquiaSelect.addEventListener('change', function () {
                            const parroquiaId = this.value;
                            const estadoId = estadoSelect.value;
                            const municipioId = municipioSelect.value;

                            if (parroquiaId) {
                                fetch(`/sisgeasic/backend/estadisticas/estadistica_backend.php?estado_id=${estadoId}&municipio_id=${municipioId}&parroquia_id=${parroquiaId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        // Actualizar gráfico de pacientes por parroquia
                                        const parishCtx = document.getElementById('patientsByParishChart').getContext('2d');
                                        if (pacientesParroquiaChart) pacientesParroquiaChart.destroy();
                                        pacientesParroquiaChart = new Chart(parishCtx, {
                                            type: 'bar',
                                            data: {
                                                labels: data.parroquias.map(item => item.nombre),
                                                datasets: [{
                                                    label: 'Pacientes Atendidos',
                                                    data: data.parroquias.map(item => item.total),
                                                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                                    borderColor: 'rgba(54, 162, 235, 1)',
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true
                                                    }
                                                }
                                            }
                                        });

                                        // Gráfico de pacientes por sexo usando Chart.js
                                        const genderCtx = document.getElementById('patientsByGenderChart').getContext('2d');
                                        if (pacientesGeneroChart) pacientesGeneroChart.destroy();

                                        // Siempre mostrar ambas barras, aunque una sea cero o ambas sean cero
                                        let masculino = 0;
                                        let femenino = 0;
                                        if (data.sexo) {
                                            // Si alguno viene como null o undefined, ponerlo en 0
                                            masculino = Number(data.sexo.masculino ?? 0);
                                            femenino = Number(data.sexo.femenino ?? 0);
                                        }

                                        // Si ambos son cero pero hay pacientes atendidos, mostrar ambos en cero (no "Desconocido")
                                        pacientesGeneroChart = new Chart(genderCtx, {
                                            type: 'bar',
                                            data: {
                                                labels: ['Masculino', 'Femenino'],
                                                datasets: [{
                                                    label: 'Pacientes',
                                                    data: [masculino, femenino],
                                                    backgroundColor: [
                                                        'rgba(75, 192, 192, 0.6)',
                                                        'rgba(255, 99, 132, 0.6)'
                                                    ],
                                                    borderColor: [
                                                        'rgba(75, 192, 192, 1)',
                                                        'rgba(255, 99, 132, 1)'
                                                    ],
                                                    borderWidth: 1,
                                                    maxBarThickness: 80
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                scales: {
                                                    y: { beginAtZero: true }
                                                }
                                            }
                                        });
                                    });
                            }
                        });

                        // Función para cargar estadísticas de enfermedades por mes
                        function cargarEstadisticasEnfermedades() {
                            fetch(`/sisgeasic/backend/estadisticas/estadisticas_enfermedades_mes.php?filtro=${filtroEnfermedad}`)
                                .then(response => response.json())
                                .then(data => {
                                    const ctx = document.getElementById('enfermedadesPorMesChart').getContext('2d');
                                    if (enfermedadesMesChart) enfermedadesMesChart.destroy();
                                    enfermedadesMesChart = new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: data.meses,
                                            datasets: data.enfermedades.map(enf => ({
                                                label: enf.nombre,
                                                data: enf.casos,
                                                backgroundColor: enf.color,
                                                borderColor: enf.color,
                                                borderWidth: 1
                                            }))
                                        },
                                        options: {
                                            responsive: true,
                                            scales: {
                                                y: {
                                                    beginAtZero: true
                                                }
                                            }
                                        }
                                    });
                                });
                        }

                        // Función para cargar estadísticas de consultas diarias por especialidad
                        function cargarEstadisticasConsultas() {
                            fetch('estadisticas_consultas_especialidad.php')
                                .then(response => response.json())
                                .then(data => {
                                    const ctx = document.getElementById('consultasPorEspecialidadChart').getContext('2d');
                                    if (consultasEspecialidadChart) consultasEspecialidadChart.destroy();
                                    consultasEspecialidadChart = new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: data.fechas,
                                            datasets: data.especialidades.map(esp => ({
                                                label: esp.nombre,
                                                data: esp.consultas,
                                                backgroundColor: esp.color,
                                                borderColor: esp.color,
                                                borderWidth: 1
                                            }))
                                        },
                                        options: {
                                            responsive: true,
                                            scales: {
                                                y: {
                                                    beginAtZero: true
                                                }
                                            }
                                        }
                                    });
                                });
                        }

                        // Inicializar con la opción por defecto
                        tipoEstadisticaSelect.dispatchEvent(new Event('change'));

                        document.getElementById('btnTodosMeses').onclick = function() {
                            filtroEnfermedad = 'todos';
                            setActiveBtn(this);
                            cargarEstadisticasEnfermedades();
                        };
                        document.getElementById('btnMesActual').onclick = function() {
                            filtroEnfermedad = 'mes';
                            setActiveBtn(this);
                            cargarEstadisticasEnfermedades();
                        };
                        document.getElementById('btnSemanaActual').onclick = function() {
                            filtroEnfermedad = 'semana';
                            setActiveBtn(this);
                            cargarEstadisticasEnfermedades();
                        };
                        document.getElementById('btnDiaActual').onclick = function() {
                            filtroEnfermedad = 'dia';
                            setActiveBtn(this);
                            cargarEstadisticasEnfermedades();
                        };

                        function setActiveBtn(btn) {
                            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('btn-primary'));
                            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.add('btn-secondary'));
                            btn.classList.remove('btn-secondary');
                            btn.classList.add('btn-primary');
                        }
                    });
                </script>
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>