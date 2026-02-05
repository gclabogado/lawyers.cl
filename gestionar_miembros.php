<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

$servername = "127.0.0.1";
$username = "b";
$password = "Jesu1994!!";
$dbname = "penitenciario";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filter = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    $filter = $_POST['filter'];
}

$sql = "SELECT * FROM miembros";
if (!empty($filter)) {
    $sql .= " WHERE UPPER(nombre_completo) LIKE UPPER('%$filter%') OR UPPER(rut) LIKE UPPER('%$filter%') OR UPPER(domicilio) LIKE UPPER('%$filter%') OR UPPER(numero_contacto) LIKE UPPER('%$filter%') OR UPPER(cargo) LIKE UPPER('%$filter%') OR UPPER(otros) LIKE UPPER('%$filter%')";
}
$result = $conn->query($sql);
$miembros = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $miembros[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $nombre_completo = strtoupper($_POST['nombre_completo']);
    $rut = strtoupper($_POST['rut']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $domicilio = strtoupper($_POST['domicilio']);
    $numero_contacto = strtoupper($_POST['numero_contacto']);
    $cargo = strtoupper($_POST['cargo']);
    $fecha_inicio_relacion = $_POST['fecha_inicio_relacion'];
    $otros = strtoupper($_POST['otros']);

    $contrato_path = null;
    $documentos_path = null;

    if (isset($_FILES['contrato']) && $_FILES['contrato']['error'] === UPLOAD_ERR_OK) {
        $contrato_path = 'uploads/' . basename($_FILES['contrato']['name']);
        move_uploaded_file($_FILES['contrato']['tmp_name'], $contrato_path);
    }

    if (isset($_FILES['documentos'])) {
        $documentos_path = [];
        foreach ($_FILES['documentos']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['documentos']['error'][$index] === UPLOAD_ERR_OK) {
                $path = 'uploads/' . basename($_FILES['documentos']['name'][$index]);
                move_uploaded_file($tmpName, $path);
                $documentos_path[] = $path;
            }
        }
        $documentos_path = implode(',', $documentos_path);
    }

    if ($action === 'add') {
        $sql = "INSERT INTO miembros (nombre_completo, rut, fecha_nacimiento, domicilio, numero_contacto, cargo, fecha_inicio_relacion, contrato_path, otros, documentos_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $nombre_completo, $rut, $fecha_nacimiento, $domicilio, $numero_contacto, $cargo, $fecha_inicio_relacion, $contrato_path, $otros, $documentos_path);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php?page=gestionar_miembros&message=miembro_agregado');
        exit;
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $sql = "UPDATE miembros SET nombre_completo=?, rut=?, fecha_nacimiento=?, domicilio=?, numero_contacto=?, cargo=?, fecha_inicio_relacion=?, contrato_path=?, otros=?, documentos_path=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssi", $nombre_completo, $rut, $fecha_nacimiento, $domicilio, $numero_contacto, $cargo, $fecha_inicio_relacion, $contrato_path, $otros, $documentos_path, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php?page=gestionar_miembros&message=miembro_editado');
        exit;
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $sql = "DELETE FROM miembros WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php?page=gestionar_miembros&message=miembro_eliminado');
        exit;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Miembros de la Empresa</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        main {
            display: flex;
            flex: 1;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background-color: #fff;
        }
        h2, h3 {
            text-align: center;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn {
            margin-top: 10px;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
    <script>
        function toggleForm() {
            var form = document.getElementById('miembroForm');
            form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
        }
        function editarMiembro(miembro) {
            document.getElementById('id').value = miembro.id;
            document.getElementById('nombre_completo').value = miembro.nombre_completo;
            document.getElementById('rut').value = miembro.rut;
            document.getElementById('fecha_nacimiento').value = miembro.fecha_nacimiento;
            document.getElementById('domicilio').value = miembro.domicilio;
            document.getElementById('numero_contacto').value = miembro.numero_contacto;
            document.getElementById('cargo').value = miembro.cargo;
            document.getElementById('fecha_inicio_relacion').value = miembro.fecha_inicio_relacion;
            document.getElementById('otros').value = miembro.otros;
            document.getElementById('action').value = 'edit';
            toggleForm();
        }
    </script>
</head>
<body>
    <main>
        <div class="container">
            <h2>Gestionar Miembros de la Empresa</h2>
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success">
                    <?php
                    switch ($_GET['message']) {
                        case 'miembro_agregado':
                            echo "Miembro agregado exitosamente.";
                            break;
                        case 'miembro_editado':
                            echo "Miembro editado exitosamente.";
                            break;
                        case 'miembro_eliminado':
                            echo "Miembro eliminado exitosamente.";
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de filtro -->
            <form action="index.php?page=gestionar_miembros" method="post" class="mb-4">
                <div class="form-group">
                    <label for="filter">Filtrar por:</label>
                    <input type="text" class="form-control" id="filter" name="filter" value="<?php echo $filter; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>

            <!-- Lista de miembros -->
            <h3>Miembros</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>RUT</th>
                            <th>Fecha de Nacimiento</th>
                            <th>Domicilio</th>
                            <th>Número de Contacto</th>
                            <th>Cargo</th>
                            <th>Fecha de Inicio</th>
                            <th>Contrato</th>
                            <th>Otros Documentos</th>
                            <th>Otros</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($miembros as $miembro): ?>
                            <tr>
                                <td><?php echo strtoupper($miembro['nombre_completo']); ?></td>
                                <td><?php echo strtoupper($miembro['rut']); ?></td>
                                <td><?php echo $miembro['fecha_nacimiento']; ?></td>
                                <td><?php echo strtoupper($miembro['domicilio']); ?></td>
                                <td><?php echo strtoupper($miembro['numero_contacto']); ?></td>
                                <td><?php echo strtoupper($miembro['cargo']); ?></td>
                                <td><?php echo $miembro['fecha_inicio_relacion']; ?></td>
                                <td><?php echo $miembro['contrato_path'] ? '<a href="'.$miembro['contrato_path'].'" target="_blank">VER CONTRATO</a>' : ''; ?></td>
                                <td>
                                    <?php
                                    if ($miembro['documentos_path']) {
                                        $documentos = explode(',', $miembro['documentos_path']);
                                        foreach ($documentos as $documento) {
                                            echo '<a href="'.$documento.'" target="_blank">VER DOCUMENTO</a><br>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo strtoupper($miembro['otros']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editarMiembro(<?php echo htmlspecialchars(json_encode($miembro)); ?>)">Editar</button>
                                    <form action="index.php?page=gestionar_miembros" method="post" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?php echo $miembro['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Botón para desplegar/ocultar el formulario -->
            <button class="btn btn-success mb-3" onclick="toggleForm()">Agregar/Editar Miembro</button>

            <!-- Formulario para agregar/editar miembros -->
            <div id="miembroForm" style="display: none;">
                <h3>Formulario de Miembro</h3>
                <form action="index.php?page=gestionar_miembros" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="action" id="action" value="add">
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo:</label>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
                    </div>
                    <div class="form-group">
                        <label for="rut">RUT:</label>
                        <input type="text" class="form-control" id="rut" name="rut" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                    <div class="form-group">
                        <label for="domicilio">Domicilio:</label>
                        <input type="text" class="form-control" id="domicilio" name="domicilio">
                    </div>
                    <div class="form-group">
                        <label for="numero_contacto">Número de Contacto:</label>
                        <input type="text" class="form-control" id="numero_contacto" name="numero_contacto">
                    </div>
                    <div class="form-group">
                        <label for="cargo">Cargo:</label>
                        <select class="form-control" id="cargo" name="cargo" required>
                            <option value="SOCIO & ABOGADO DEFENSOR">SOCIO & ABOGADO DEFENSOR</option>
                            <option value="ASISTENTE ADMINISTRATIVA(O)">ASISTENTE ADMINISTRATIVA(O)</option>
                            <option value="TRABAJADOR SOCIAL">TRABAJADOR SOCIAL</option>
                            <option value="REEMPLAZO ASISTENTE">REEMPLAZO ASISTENTE</option>
                            <option value="REEMPLAZO ADMINISTRATIVA">REEMPLAZO ADMINISTRATIVA</option>
                            <option value="ABOGADO DE NOMINA DE REEMPLAZOS">ABOGADO DE NOMINA DE REEMPLAZOS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio_relacion">Fecha de Inicio de Relación Laboral:</label>
                        <input type="date" class="form-control" id="fecha_inicio_relacion" name="fecha_inicio_relacion" required>
                    </div>
                    <div class="form-group">
                        <label for="contrato">Copia de Contrato:</label>
                        <input type="file" class="form-control-file" id="contrato" name="contrato">
                    </div>
                    <div class="form-group">
                        <label for="documentos">Otros Documentos:</label>
                        <input type="file" class="form-control-file" id="documentos" name="documentos[]" multiple>
                    </div>
                    <div class="form-group">
                        <label for="otros">Otros:</label>
                        <textarea class="form-control" id="otros" name="otros"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
