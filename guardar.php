<?php
$servername = "127.0.0.1";
$username = "b";
$password = "Jesu1994!!";
$dbname = "penitenciario";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$edad = $_POST['edad'];
$fecha_ingreso = $_POST['fecha_ingreso'];
$fecha_termino = $_POST['fecha_termino'];
$fecha_solicitud = $_POST['fecha_solicitud'];
$carcel = $_POST['carcel'];

$sql = "INSERT INTO internos (nombre, apellido, edad, fecha_ingreso, fecha_termino, fecha_solicitud, carcel) VALUES ('$nombre', '$apellido', $edad, '$fecha_ingreso', '$fecha_termino', '$fecha_solicitud', '$carcel')";

if ($conn->query($sql) === TRUE) {
    echo "Nuevo registro creado exitosamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
