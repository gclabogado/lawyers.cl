<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "127.0.0.1";
$username = "b";
$password = "Jesu1994!!";
$dbname = "penitenciario";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        die("Correo electrónico no válido.");
    }
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $categoria = $_POST['categoria'];

    $sql = "SELECT id FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación del statement: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "El correo electrónico ya está registrado.";
    } else {
        $sql = "INSERT INTO usuarios (username, password, categoria) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación del statement: " . $conn->error);
        }
        $stmt->bind_param("sss", $username, $password, $categoria);

        if ($stmt->execute()) {
            echo "Cuenta creada exitosamente. <a href='acceso.php'>Acceder</a>";
        } else {
            echo "Error: " . $sql . "<br>" . $stmt->error;
        }
    }
    $stmt->close();
}

$conn->close();
?>
