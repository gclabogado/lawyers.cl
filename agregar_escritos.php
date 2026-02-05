<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

$msg = '';
$type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_solicitud = trim($_POST['tipo_solicitud']);
    $target_dir = __DIR__ . "/solicitudes/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = basename($_FILES["file"]["name"]);
    $safeName = preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $filename);
    $target_file = $target_dir . $safeName;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validaciones
    if ($fileType !== "docx") {
        $msg = "❌ Solo se permiten archivos DOCX.";
        $type = "danger";
        $uploadOk = 0;
    }
    if (file_exists($target_file)) {
        $msg = "⚠️ El archivo ya existe.";
        $type = "warning";
        $uploadOk = 0;
    }
    if ($_FILES["file"]["size"] > 20 * 1024 * 1024) { // 20MB
        $msg = "⚠️ El archivo es demasiado grande (máx. 20 MB).";
        $type = "warning";
        $uploadOk = 0;
    }

    if ($uploadOk === 1) {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $servername = "localhost";
            $username   = "b";
            $password   = "Jesu1994!!";
            $dbname     = "penitenciario";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

            $stmt = $conn->prepare("INSERT INTO solicitudes (tipo_solicitud, archivo) VALUES (?, ?)");
            $relativePath = "solicitudes/" . $safeName;
            $stmt->bind_param("ss", $tipo_solicitud, $relativePath);

            if ($stmt->execute()) {
                $msg = "✅ El archivo <b>$safeName</b> ha sido subido correctamente.";
                $type = "success";
            } else {
                $msg = "❌ Error al guardar en la base de datos.";
                $type = "danger";
            }

            $stmt->close();
            $conn->close();
        } else {
            $msg = "❌ Hubo un error al subir el archivo.";
            $type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>➕ Agregar Escritos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f5f6fa; font-family: system-ui, sans-serif; }
    .container { max-width: 600px; margin-top: 50px; }
    h1 { color:#0b3c75; font-weight:600; margin-bottom:20px; }
    .card { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,.1); }
  </style>
</head>
<body>
<div class="container">
  <div class="card p-4">
    <h1 class="text-center">➕ Agregar Escritos</h1>

    <?php if (!empty($msg)): ?>
      <div class="alert alert-<?= $type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form action="index.php?page=agregar_escritos" method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="tipo_solicitud" class="form-label">Tipo de Solicitud</label>
        <input type="text" class="form-control" id="tipo_solicitud" name="tipo_solicitud" required autofocus>
      </div>
      <div class="mb-3">
        <label for="file" class="form-label">Archivo DOCX</label>
        <input type="file" class="form-control" id="file" name="file" accept=".docx" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        <i class="fas fa-upload"></i> Subir Solicitud
      </button>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
