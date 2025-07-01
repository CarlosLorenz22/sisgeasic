<?php
$conn = pg_connect("host=localhost dbname=bd_asic user=postgres password=30429913");
if (!$conn) {
    die("No se pudo conectar a PostgreSQL: " . pg_last_error());
}
?>