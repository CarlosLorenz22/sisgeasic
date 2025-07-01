<?php
// Inicia la sesión para mantener el estado del usuario
session_start();

// Conexión a la base de datos
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");

// Obtiene el nombre del usuario desde la base de datos
$nombre_usuario = $_SESSION['usuario'];
$query = "SELECT nombre FROM usuario WHERE nombre_usuario = $1";
$result = pg_query_params($conn, $query, array($nombre_usuario));

if ($row = pg_fetch_assoc($result)) {
    $nombre = $row['nombre']; // Obtiene el nombre desde la columna 'nombre'
} else {
    $nombre = "Usuario"; // Valor predeterminado si no se encuentra el nombre
}
?>

    <!-- Botón flotante animado para reportes en el centro con opciones en círculo -->
    <div class="fab-container-circular">
        <button class="fab-main-circular" id="fabReportes">
            <i class="fa-solid fa-file-medical"></i>
    </button>
        <ul class="fab-options-circular" id="fabOptions">
            <li>
                <a href="/sisgeasic/modulos/exportar_pacientes.php" class="fab-option-circular" style="--i:0;" download="reporte de morbilidad general.csv">
                    Reporte general
                </a>
            </li>
        </ul>
    </div>
    <style>
        .fab-container-circular {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
            width: 320px;
            height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fab-main-circular {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #4e73df;
            color: #fff;
            border: none;
            outline: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            font-size: 36px;
            transition: transform 0.2s;
            cursor: pointer;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fab-main-circular:active {
            transform: translate(-50%, -50%) scale(0.95);
        }
        .fab-options-circular {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 420px;
            height: 420px;
            pointer-events: none;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent;
        }
        .fab-options-circular li {
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent;
        }
        .fab-option-circular {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 210px;
            min-height: 80px;
            max-width: 220px;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: #fff;
            color: #4e73df;
            border-radius: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.10);
            text-decoration: none;
            font-weight: 600;
            opacity: 0;
            pointer-events: auto;
            border: none;
            padding: 16px 18px;
            text-align: center;
            transition: background 0.2s, color 0.2s, opacity 0.3s, box-shadow 0.2s;
            z-index: 1;
            word-break: break-word;
            white-space: normal;
            /* Distribución circular, más separado (170px) */
            transform: translate(-50%, -50%) rotate(calc(90deg * var(--i))) translate(170px) rotate(calc(-90deg * var(--i)));
        }
        .fab-option-circular:hover {
            background: #4e73df;
            color: #fff;
            box-shadow: 0 4px 16px rgba(78,115,223,0.15);
            list-style: none !important;
        }
        .fab-options-circular.show .fab-option-circular {
            opacity: 1;
            transition-delay: calc(0.05s * var(--i));
        }
        .fab-options-circular:not(.show) .fab-option-circular {
            pointer-events: none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fabMain = document.getElementById('fabReportes');
            const fabOptions = document.getElementById('fabOptions');
            fabMain.addEventListener('click', function(e) {
                e.stopPropagation();
                fabOptions.classList.toggle('show');
            });
            document.addEventListener('click', function(e) {
                if (!fabMain.contains(e.target) && !fabOptions.contains(e.target)) {
                    fabOptions.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>