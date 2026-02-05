<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

$servername = "localhost";
$username = "b";
$password = "Jesu1994!!";
$dbname = "penitenciario";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Copiar el registro de internos_eliminados a internos
    $sql_copy = "INSERT INTO internos SELECT * FROM internos_eliminados WHERE id = ?";
    $stmt_copy = $conn->prepare($sql_copy);
    if ($stmt_copy === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }
    $stmt_copy->bind_param("i", $id);
    if ($stmt_copy->execute()) {
        // Eliminar el registro de la tabla internos_eliminados
        $sql_delete = "DELETE FROM internos_eliminados WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        if ($stmt_delete === false) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt_delete->bind_param("i", $id);
        if ($stmt_delete->execute()) {
            header('Location: index.php?page=listar_eliminados&message=recuperado_success');
        } else {
            header('Location: index.php?page=listar_eliminados&message=recuperado_error');
        }
        $stmt_delete->close();
    } else {
        header('Location: index.php?page=listar_eliminados&message=recuperado_error');
    }
    $stmt_copy->close();
} else {
    header('Location: index.php?page=listar_eliminados');
}

$conn->close();
?>
