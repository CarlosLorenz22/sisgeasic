<?php
include_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $pr_nombre = trim($_POST['pr_nombre'] ?? '');
    $sdo_nombre = trim($_POST['sdo_nombre'] ?? '');
    $pr_apellido = trim($_POST['pr_apellido'] ?? '');
    $sdo_apellido = trim($_POST['sdo_apellido'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cod_telefono = trim($_POST['cod_telefono'] ?? '');
    $peso = trim($_POST['peso'] ?? '');
    $talla = trim($_POST['talla'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $id_estado = trim($_POST['id_estado'] ?? '');
    $id_municipio = trim($_POST['id_municipio'] ?? '');
    $id_parroquia = trim($_POST['id_parroquia'] ?? '');

    $telefono_completo = ($cod_telefono && $telefono) ? $cod_telefono . $telefono : $telefono;

    if (!$cedula || !$pr_nombre || !$pr_apellido || !$genero || !$fecha_nacimiento || !$peso || !$talla || !$direccion || !$id_estado || !$id_municipio || !$id_parroquia) {
        $error = "Faltan campos obligatorios.";
    } else {
        $check = pg_query_params($conn, "SELECT 1 FROM paciente WHERE cedula = $1", [$cedula]);
        if ($check && pg_fetch_row($check)) {
            $error = "La cédula ya está registrada.";
        } else {
            $sql = "INSERT INTO paciente 
                (cedula, pr_nombre, sdo_nombre, pr_apellido, sdo_apellido, genero, fecha_nacimiento, correo, telefono, peso, talla, direccion, id_estado, id_municipio, id_parroquia)
                VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15)";
            $params = [
                $cedula, $pr_nombre, $sdo_nombre, $pr_apellido, $sdo_apellido, $genero, $fecha_nacimiento, $correo, $telefono_completo, $peso, $talla, $direccion, $id_estado, $id_municipio, $id_parroquia
            ];
            $result = pg_query_params($conn, $sql, $params);

            if ($result) {
                // Redirige correctamente al CRUD de pacientes
                echo "<!DOCTYPE html>
                <html lang='es'>
                <head>
                    <meta charset='UTF-8'>
                    <title>Paciente guardado</title>
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                </head>
                <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Paciente guardado',
                        text: 'El paciente se guardó correctamente.',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = '/sisgeasic/dashboard.php?modulo=pacientes';
                    });
                </script>
                </body>
                </html>";
                exit;
            } else {
                $error = "Error al guardar el paciente: " . pg_last_error($conn);
            }
        }
    }
} else {
    $error = "Acceso no permitido.";
}

if (isset($error)) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Error</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: " . json_encode($error) . ",
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.history.back();
        });
    </script>
    </body>
    </html>";
}
?>
