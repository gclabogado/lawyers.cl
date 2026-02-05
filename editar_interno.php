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
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $rut = $_POST['rut'];
    $sexo = $_POST['sexo'];
    $nacionalidad = $_POST['nacionalidad'];
    $delito = $_POST['delito'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_termino = $_POST['fecha_termino'];
    $fecha_registro = $_POST['fecha_registro'];
    $fecha_entrevista = $_POST['fecha_entrevista'];
    $juzgado = $_POST['juzgado'];
    $rit = $_POST['rit'];
    $tiempo_condena = $_POST['tiempo_condena'];
    $carcel = $_POST['carcel'];
    $abogado = $_POST['abogado'];
    $solicitudes = $_POST['solicitudes'];

    $sql = "UPDATE internos 
            SET nombres=?, apellidos=?, rut=?, sexo=?, nacionalidad=?, delito=?, 
                fecha_inicio=?, fecha_termino=?, fecha_registro=?, fecha_entrevista=?, 
                juzgado=?, rit=?, tiempo_condena=?, carcel=?, abogado=?, solicitudes=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssssi", $nombres, $apellidos, $rut, $sexo, $nacionalidad, $delito,
        $fecha_inicio, $fecha_termino, $fecha_registro, $fecha_entrevista, $juzgado, $rit,
        $tiempo_condena, $carcel, $abogado, $solicitudes, $id);
    if ($stmt->execute()) {
        header('Location: index.php?page=listar_internos&message=edit_success');
        exit;
    } else {
        echo "Error al actualizar el registro: " . $stmt->error;
    }
    $stmt->close();
} else {
    $id = $_GET['id'];
    $sql = "SELECT * FROM internos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $interno = $result->fetch_assoc();
    } else {
        die("No se encontró el interno con ID " . htmlspecialchars($id));
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Interno</title>
<style>
    :root {
        --primary:#0b3c75;
        --primary-hover:#114d95;
        --bg:#f4f6fa;
        --panel:#fff;
        --border:#ddd;
        --shadow:0 4px 10px rgba(0,0,0,.08);
    }
    body {
        font-family: system-ui, Arial, sans-serif;
        background: var(--bg);
        margin:0; padding:20px;
        color:#333;
    }
    h1 {
        text-align:center;
        color: var(--primary);
        margin-bottom:20px;
    }
    form {
        background: var(--panel);
        border:1px solid var(--border);
        border-radius:12px;
        box-shadow: var(--shadow);
        max-width:900px;
        margin:auto;
        padding:25px;
    }
    label {
        font-weight:600;
        margin-bottom:5px;
        display:block;
        color:#444;
    }
    input, textarea {
        width:100%;
        padding:10px;
        margin-bottom:18px;
        border:1px solid #ccc;
        border-radius:8px;
        font-size:.95rem;
        transition:.2s;
    }
    input:focus, textarea:focus {
        border-color: var(--primary);
        outline:none;
        box-shadow:0 0 4px rgba(11,60,117,.2);
    }
    textarea.large-textarea { height:90px; }
    .form-row {
        display:flex;
        gap:15px;
        flex-wrap:wrap;
    }
    .form-row div { flex:1; min-width:200px; }
    .btn-submit {
        display:block;
        width:100%;
        background: var(--primary);
        color:#fff;
        padding:12px;
        font-size:1rem;
        font-weight:600;
        border:none;
        border-radius:8px;
        cursor:pointer;
        transition:.2s;
    }
    .btn-submit:hover { background: var(--primary-hover); }

    /* Botón FAQ flotante */
    .faq-btn {
        position:fixed; bottom:20px; right:20px;
        background:var(--primary); color:#fff; font-size:1.4rem;
        width:52px; height:52px; border-radius:50%; border:none;
        box-shadow: var(--shadow); cursor:pointer; transition:.2s;
    }
    .faq-btn:hover { background: var(--primary-hover); }

    /* Modal FAQ */
    .modal {
        display:none; position:fixed; top:0; left:0;
        width:100%; height:100%;
        background:rgba(0,0,0,.6);
        justify-content:center; align-items:center;
        z-index:999;
    }
    .modal-content {
        background:#fff;
        padding:25px;
        border-radius:12px;
        box-shadow: var(--shadow);
        max-width:700px;
        width:90%;
        max-height:80%;
        overflow-y:auto;
        animation:fadeIn .3s ease;
    }
    .close {
        float:right; cursor:pointer;
        font-size:1.3rem; font-weight:bold;
    }
    .modal-content h2 { color:var(--primary); margin-top:0; }
    .modal-content h3 { margin-bottom:5px; }
    @keyframes fadeIn {
        from { opacity:0; transform:translateY(-20px);}
        to { opacity:1; transform:translateY(0);}
    }
</style>
</head>
<body>

<h1>✏️ Editar Interno</h1>
<form method="post" action="index.php?page=editar_interno">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($interno['id']); ?>">

    <div class="form-row">
        <div>
            <label>Nombres:</label>
            <input type="text" name="nombres" value="<?php echo htmlspecialchars($interno['nombres']); ?>" required>
        </div>
        <div>
            <label>Apellidos:</label>
            <input type="text" name="apellidos" value="<?php echo htmlspecialchars($interno['apellidos']); ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>RUT:</label>
            <input type="text" name="rut" value="<?php echo htmlspecialchars($interno['rut']); ?>" required>
        </div>
        <div>
            <label>Sexo:</label>
            <input type="text" name="sexo" value="<?php echo htmlspecialchars($interno['sexo']); ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Nacionalidad:</label>
            <input type="text" name="nacionalidad" value="<?php echo htmlspecialchars($interno['nacionalidad']); ?>" required>
        </div>
        <div>
            <label>Delito:</label>
            <textarea name="delito" class="large-textarea" required><?php echo htmlspecialchars($interno['delito']); ?></textarea>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Fecha Inicio:</label>
            <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($interno['fecha_inicio']))); ?>" required>
        </div>
        <div>
            <label>Fecha Término:</label>
            <input type="date" name="fecha_termino" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($interno['fecha_termino']))); ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Fecha Registro:</label>
            <input type="date" name="fecha_registro" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($interno['fecha_registro']))); ?>" required>
        </div>
        <div>
            <label>Fecha Entrevista:</label>
            <input type="date" name="fecha_entrevista" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($interno['fecha_entrevista']))); ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Juzgado:</label>
            <textarea name="juzgado" class="large-textarea" required><?php echo htmlspecialchars($interno['juzgado']); ?></textarea>
        </div>
        <div>
            <label>RIT:</label>
            <input type="text" name="rit" value="<?php echo htmlspecialchars($interno['rit']); ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Tiempo Condena:</label>
            <input type="text" name="tiempo_condena" value="<?php echo htmlspecialchars($interno['tiempo_condena']); ?>" required>
        </div>
        <div>
            <label>Unidad Penal:</label>
            <input type="text" name="carcel" value="<?php echo htmlspecialchars($interno['carcel']); ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Abogado:</label>
            <input type="text" name="abogado" value="<?php echo htmlspecialchars($interno['abogado']); ?>" required>
        </div>
        <div>
            <label>Solicitudes:</label>
            <textarea name="solicitudes" class="large-textarea" required><?php echo htmlspecialchars($interno['solicitudes']); ?></textarea>
        </div>
    </div>

    <input type="submit" value="💾 Guardar Cambios" class="btn-submit">
</form>

<!-- Botón FAQ -->
<button class="faq-btn" onclick="document.getElementById('faqModal').style.display='flex'">❓</button>

<!-- Modal FAQ -->
<div id="faqModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('faqModal').style.display='none'">✖</span>
    <h2>❓ Preguntas Frecuentes — Editar Interno</h2>
    <h3>1. ¿Qué campos son obligatorios?</h3><p>Todos los mostrados en el formulario.</p>
    <h3>2. ¿Cómo cambio fechas?</h3><p>Seleccionando desde el calendario (formato AAAA-MM-DD).</p>
    <h3>3. ¿Puedo modificar el RUT?</h3><p>Sí, pero debe ser válido para evitar problemas.</p>
    <h3>4. ¿Qué pasa si cambio el abogado?</h3><p>El sistema actualizará el responsable en listados y reportes.</p>
    <h3>5. ¿Cómo sé que guardé bien?</h3><p>Si el cambio fue exitoso, verá el mensaje ✅ Editado con éxito.</p>
  </div>
</div>
</body>
</html>
