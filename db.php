<?php
// db.php - Conexión Maestra SaaS
$servername = "localhost";
$username   = "bsdad";            
$password   = "sadasdasd!!";   
$dbname     = "asdasdasd"; // <--- Apuntando a la nueva estructura

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("❌ Error Fatal de Sistema: " . $conn->connect_error);
}

// Forzar UTF-8 para tildes y ñ
$conn->set_charset("utf8mb4");
?>
