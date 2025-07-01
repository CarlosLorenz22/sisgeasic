<?php
// Configuración de la conexión a la base de datos
$host = 'localhost'; // Cambia esto si tu base de datos está en otro servidor
$port = '5432';      // Puerto predeterminado de PostgreSQL
$dbname = 'bd_asic'; // Nombre de tu base de datos
$user = 'postgres';  // Usuario de la base de datos
$password = '30429913'; // Contraseña del usuario

// Conexión a la base de datos
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Verifica la conexión
if (!$conn) {
    error_log('Error de conexión a la base de datos: ' . pg_last_error());
    die();
}
?>
