<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "b";
$password = "Jesu1994!!";
$dbname = "penitenciario";

session_start();

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verificar el método de la solicitud
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que todos los campos requeridos están presentes
    if (isset($_POST['nombres']) && isset($_POST['apellidos']) && isset($_POST['rut']) && 
        isset($_POST['sexo']) && isset($_POST['nacionalidad']) && isset($_POST['fecha_ingreso']) && 
        isset($_POST['fecha_termino']) && isset($_POST['fecha_entrevista']) && 
        isset($_POST['juzgado']) && isset($_POST['rit']) && isset($_POST['carcel']) && 
        isset($_POST['abogado']) && isset($_POST['delito']) && isset($_POST['tiempo_condena'])) {
        
        $nombres = $_POST['nombres'];
        $apellidos = $_POST['apellidos'];
        $abogado = $_POST['abogado'];
        $rut = $_POST['rut'];
        $sexo = $_POST['sexo'];
        $nacionalidad = $_POST['nacionalidad'];
        $delito = $_POST['delito'];
        $tiempo_condena = $_POST['tiempo_condena'];
        $fecha_ingreso = $_POST['fecha_ingreso'];
        $fecha_termino = $_POST['fecha_termino'];
        $fecha_entrevista = $_POST['fecha_entrevista'];
        $juzgado = $_POST['juzgado'];
        $rit = $_POST['rit'];
        $carcel = $_POST['carcel'];
        $solicitudes = isset($_POST['solicitudes']) ? $_POST['solicitudes'] : NULL;
        $usuario = $_SESSION['username']; // Usar el nombre de usuario de la sesión

        // Preparar y ejecutar la consulta
        $stmt = $conn->prepare("INSERT INTO internos (nombres, apellidos, abogado, rut, sexo, nacionalidad, delito, tiempo_condena, fecha_inicio, fecha_termino, fecha_registro, fecha_entrevista, juzgado, rit, carcel, usuario, solicitudes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?)");
        
        // Verificar si la preparación de la consulta fue exitosa
        if ($stmt === false) {
            die("Error preparando la consulta: " . $conn->error);
        }

        $stmt->bind_param("ssssssssssssssss", $nombres, $apellidos, $abogado, $rut, $sexo, $nacionalidad, $delito, $tiempo_condena, $fecha_ingreso, $fecha_termino, $fecha_entrevista, $juzgado, $rit, $carcel, $usuario, $solicitudes);

        // Verificar si la ejecución de la consulta fue exitosa
        if ($stmt->execute()) {
            // Redirigir a index.php con un parámetro de éxito
            header("Location: index.php?message=interno_agregado");
            exit();
        } else {
            // Redirigir a index.php con un parámetro de error
            header("Location: index.php?message=error");
            exit();
        }

        $stmt->close();
    } else {
        // Redirigir a index.php con un parámetro de error si faltan campos
        header("Location: index.php?message=error");
        exit();
    }
}

$conn->close();
?>

