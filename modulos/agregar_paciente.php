<?php
include_once __DIR__ . '/../backend/conexion.php';

// Cargar estados
$estados = [];
$res = pg_query($conn, "SELECT id_estado, nombre FROM estado ORDER BY nombre");
while ($row = pg_fetch_assoc($res)) {
    $estados[] = $row;
}
?>
<?php include_once 'menu.php'; ?>
<div class="container mt-4">
    <h2 class="mb-4">Agregar Paciente</h2>
    <form id="formAgregarPaciente" class="row justify-content-center needs-validation" method="post" action="/sisgeasic/backend/paciente/procesar_agregar_paciente.php" style="max-width: 800px; margin: 0 auto;" novalidate>
        <div class="col-md-12">
            <div class="form-group text-center">
                <label>Cédula: <span class="text-danger">*</span></label>
                <input name="cedula" class="form-control mx-auto" style="max-width:300px;" required pattern="^[0-9]{6,10}$">
                <div class="invalid-feedback">Ingrese una cédula válida (solo números, 6-10 dígitos).</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Primer Nombre: <span class="text-danger">*</span></label>
                <input name="pr_nombre" class="form-control" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$">
                <div class="invalid-feedback">Ingrese un nombre válido.</div>
            </div>
            <div class="form-group">
                <label>Primer Apellido: <span class="text-danger">*</span></label>
                <input name="pr_apellido" class="form-control" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$">
                <div class="invalid-feedback">Ingrese un apellido válido.</div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Género: <span class="text-danger">*</span></label><br>
                    <div id="generoGroup" class="d-flex flex-row align-items-center">
                        <div class="form-check form-check-inline mr-3">
                            <input class="form-check-input" type="radio" name="genero" id="generoM" value="M" required>
                            <label class="form-check-label" for="generoM">Masculino</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="genero" id="generoF" value="F" required>
                            <label class="form-check-label" for="generoF">Femenino</label>
                        </div>
                    </div>
                    <div class="invalid-feedback d-block" id="generoFeedback">Seleccione un género.</div>
                </div>
            </div>
            <div class="form-group">
                <label>Correo</label>
                <input name="correo" type="email" class="form-control" placeholder="ejemplo@correo.com">
                <div class="invalid-feedback">Ingrese un correo válido.</div>
            </div>
            <div class="form-group">
                <label>Peso (kg): <span class="text-danger">*</span></label>
                <input name="peso" type="number" step="0.01" class="form-control" placeholder="Peso en kilogramos" required min="1" max="300">
                <div class="invalid-feedback">Ingrese un peso válido.</div>
            </div>
            <div class="form-group">
                <label>Estado: <span class="text-danger">*</span></label>
                <select name="id_estado" id="id_estado" class="form-control" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?= $estado['id_estado'] ?>"><?= htmlspecialchars($estado['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Seleccione un estado.</div>
            </div>
            <div class="form-group">
                <label>Municipio: <span class="text-danger">*</span></label>
                <select name="id_municipio" id="id_municipio" class="form-control" required>
                    <option value="">Seleccione</option>
                </select>
                <div class="invalid-feedback">Seleccione un municipio.</div>
            </div>
            <div class="form-group">
                <label>Parroquia: <span class="text-danger">*</span></label>
                <select name="id_parroquia" id="id_parroquia" class="form-control" required>
                    <option value="">Seleccione</option>
                </select>
                <div class="invalid-feedback">Seleccione una parroquia.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Segundo Nombre:</label>
                <input name="sdo_nombre" class="form-control" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{0,30}$">
                <div class="invalid-feedback">Ingrese un nombre válido.</div>
            </div>
            <div class="form-group">
                <label>Segundo Apellido:</label>
                <input name="sdo_apellido" class="form-control" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{0,30}$">
                <div class="invalid-feedback">Ingrese un apellido válido.</div>
            </div>
            <div class="form-group">
                <label>Fecha de Nacimiento: <span class="text-danger">*</span></label>
                <input name="fecha_nacimiento" id="fecha_nacimiento" type="date" class="form-control" required>
                <div class="invalid-feedback">Seleccione una fecha válida.</div>
            </div>
            <div class="form-group">
                <label>Teléfono:</label>
                <div class="input-group">
                    <select name="cod_telefono" class="custom-select" style="max-width:100px;">
                        <option value="">Seleccione</option>
                        <option value="0412">0412</option>
                        <option value="0414">0414</option>
                        <option value="0416">0416</option>
                        <option value="0424">0424</option>
                        <option value="0426">0426</option>
                        <option value="0212">0212</option>
                    </select>
                    <input name="telefono" class="form-control" placeholder="Número de Teléfono" pattern="^[0-9]{7}$">
                </div>
                <div class="invalid-feedback">Ingrese un teléfono válido.</div>
            </div>
            <div class="form-group">
                <label>Talla (cm): <span class="text-danger">*</span></label>
                <input name="talla" type="number" step="0.01" class="form-control" placeholder="Talla en centímetros" required min="30" max="250">
                <div class="invalid-feedback">Ingrese una talla válida.</div>
            </div>
            <div class="form-group">
                <label>Dirección: <span class="text-danger">*</span></label>
                <textarea name="direccion" class="form-control" required minlength="5"></textarea>
                <div class="invalid-feedback">Ingrese una dirección válida.</div>
            </div>
        </div>
        <div class="col-12 text-center mb-4">
            <button type="submit" class="btn btn-primary px-5">Agregar Paciente Normal</button>
            <a href="dashboard.php?modulo=pacientes" class="btn btn-secondary px-5">Cancelar</a>
        </div>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Al cambiar el estado, cargar municipios
    $('#id_estado').on('change', function() {
        var estado_id = $(this).val();
        $('#id_municipio').html('<option value="">Seleccione</option>');
        $('#id_parroquia').html('<option value="">Seleccione</option>');
        if (estado_id) {
            $.ajax({
                url: '/sisgeasic/backend/paciente/ajax_municipios.php',
                type: 'GET',
                data: { estado_id: estado_id },
                dataType: 'json'
            })
            .done(function(data) {
                $('#id_municipio').html('<option value="">Seleccione</option>');
                if (Array.isArray(data)) {
                    data.forEach(function(m) {
                        $('#id_municipio').append('<option value="'+m.id_municipio+'">'+m.nombre+'</option>');
                    });
                }
            })
            .fail(function(xhr, status, error) {
                Swal.fire('Error', 'No se pudieron cargar los municipios.<br>' + xhr.responseText, 'error');
            });
        }
    });
    // Al cambiar el municipio, cargar parroquias
    $('#id_municipio').on('change', function() {
        var municipio_id = $(this).val();
        $('#id_parroquia').html('<option value="">Seleccione</option>');
        if (municipio_id) {
            $.ajax({
                url: '/sisgeasic/backend/paciente/ajax_parroquias.php',
                type: 'GET',
                data: { municipio_id: municipio_id },
                dataType: 'json'
            })
            .done(function(data) {
                $('#id_parroquia').html('<option value="">Seleccione</option>');
                if (Array.isArray(data)) {
                    data.forEach(function(p) {
                        $('#id_parroquia').append('<option value="'+p.id_parroquia+'">'+p.nombre+'</option>');
                    });
                }
            })
            .fail(function(xhr, status, error) {
                Swal.fire('Error', 'No se pudieron cargar las parroquias.<br>' + xhr.responseText, 'error');
            });
        }
    });
    // Bootstrap validation + bordes verdes
    var form = document.getElementById('formAgregarPaciente');
    form.addEventListener('submit', function(event) {
        // Validación personalizada para género
        var generoChecked = $('input[name="genero"]:checked').length > 0;
        if (!generoChecked) {
            $('#generoGroup .form-check-input').removeClass('is-valid').addClass('is-invalid');
            $('#generoFeedback').show();
        } else {
            $('#generoGroup .form-check-input').removeClass('is-invalid').addClass('is-valid');
            $('#generoFeedback').hide();
        }

        // Mostrar SweetAlert para confirmar la cédula antes de enviar
        if (form.checkValidity() && generoChecked) {
            event.preventDefault();
            event.stopPropagation();
            var cedula = $('input[name="cedula"]').val();
            Swal.fire({
                title: '¿Está seguro?',
                html: 'Va a registrar la cédula <b>' + cedula + '</b>.<br>Verifique si la cédula es correcta.<br><span class="text-danger">Una vez registrada en el sistema no podrá ser editada.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);

    // Bordes verdes en campos válidos
    $('#formAgregarPaciente input, #formAgregarPaciente select, #formAgregarPaciente textarea').on('input change', function() {
        // Género: solo marcar el seleccionado
        if ($(this).attr('name') === 'genero') {
            if ($('input[name="genero"]:checked').length > 0) {
                $('#generoGroup .form-check-input').removeClass('is-invalid').removeClass('is-valid');
                $(this).addClass('is-valid');
                $('#generoFeedback').hide();
            } else {
                $('#generoGroup .form-check-input').removeClass('is-valid').addClass('is-invalid');
                $('#generoFeedback').show();
            }
        } else {
            if (this.checkValidity()) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        }
    });

    // Inicializa el feedback de género oculto
    $('#generoFeedback').hide();

    // Validación especial para fecha de nacimiento
    $('#fecha_nacimiento').on('change', function() {
        const input = $(this);
        const fecha = new Date(input.val());
        const hoy = new Date();
        const hace100 = new Date();
        hace100.setFullYear(hoy.getFullYear() - 100);

        if (fecha > hoy) {
            input.val('');
            input.removeClass('is-valid').addClass('is-invalid');
            Swal.fire({
                icon: 'error',
                title: 'Fecha no válida',
                text: 'No puedes seleccionar una fecha futura.',
                confirmButtonText: 'Aceptar'
            });
        } else if (fecha < hace100) {
            input.val('');
            input.removeClass('is-valid').addClass('is-invalid');
            Swal.fire({
                icon: 'error',
                title: 'Fecha no válida',
                text: 'La fecha corresponde a una persona mayor de 100 años.',
                confirmButtonText: 'Aceptar'
            });
        } else {
            // Marca como válido si pasa la validación
            input.removeClass('is-invalid').addClass('is-valid');
        }
    });
});
</script>
