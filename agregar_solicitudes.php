<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_solicitud = $_POST['tipo_solicitud'];
    $target_dir = "solicitudes/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a valid document
    if($fileType != "docx") {
        echo "Solo se permiten archivos DOCX.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "El archivo ya existe.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["file"]["size"] > 20000000) { // 20MB limit
        echo "El archivo es demasiado grande.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "El archivo no fue subido.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // Guardar la información del archivo y el tipo de solicitud en la base de datos
            $servername = "127.0.0.1";
            $username = "b";
            $password = "Jesu1994!!";
            $dbname = "penitenciario";
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("INSERT INTO solicitudes (tipo_solicitud, archivo) VALUES (?, ?)");
            $stmt->bind_param("ss", $tipo_solicitud, $target_file);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            echo "El archivo ". htmlspecialchars(basename($_FILES["file"]["name"])). " ha sido subido.";
        } else {
            echo "Hubo un error al subir tu archivo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Solicitudes - Workplace Penitenciario</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Agregar Solicitudes</h1>
        <form action="agregar_solicitudes.php" method="post" enctype="multipart/form-data" class="mt-5">
            <div class="form-group">
                <label for="tipo_solicitud">Tipo de Solicitud:</label>
                <input type="text" class="form-control" id="tipo_solicitud" name="tipo_solicitud" required>
            </div>
            <div class="form-group">
                <label for="file">Seleccionar archivo DOCX:</label>
                <input type="file" class="form-control" id="file" name="file" required>
            </div>
            <button type="submit" class="btn btn-primary">Subir Solicitud</button>
        </form>
    </div>
</body>
</html>

