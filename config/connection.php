<?php
$servername = "localhost"; 
$username = "root"; // Usuario de la base de datos
$password = ""; 
$dbname = "caja_registradora";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Conexion fallida: " . mysqli_connect_error());
}
?>