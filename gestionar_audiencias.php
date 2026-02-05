<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php?page=acceso");
    exit;
}

$conn = new mysqli("localhost", "b", "Jesu1994!!", "penitenciario");
if ($conn->connect_error) {
    die("Error DB: " . $conn->connect_error);
}

// --- ELIMINAR ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM audiencias WHERE id=$id");
    echo "<div class='alert alert-success text-center'>🗑️ Audiencia eliminada.</div>";
}

// --- ACTUALIZAR ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_id'])) {
    $id    = intval($_POST['update_id']);
    $tipo  = $_POST['tipo_audiencia'];
    $trib  = $_POST['tribunal'];
    $rit   = strtoupper(trim($_POST['rit']));
    $fecha = $_POST['fecha'];
    $hora  = $_POST['hora'];

    $stmt = $conn->prepare("UPDATE audiencias SET tipo_audiencia=?, tribunal=?, rit=?, fecha=?, hora=? WHERE id=?");
    $stmt->bind_param("sssssi", $tipo, $trib, $rit, $fecha, $hora, $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success text-center'>✏️ Audiencia actualizada.</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>❌ Error: " . $stmt->error . "</div>";
    }
}

// --- CONSULTA ---
$result = $conn->query("
    SELECT a.id, i.nombres, i.apellidos, d.nombre AS abogado,
           a.tipo_audiencia, a.tribunal, a.rit, a.fecha, a.hora
    FROM audiencias a
    LEFT JOIN internos i ON a.interno_id = i.id
    LEFT JOIN defensores d ON a.abogado_id = d.id
    ORDER BY a.fecha DESC, a.hora DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestionar Audiencias</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    body { background:#f4f6f9; font-family: Arial, sans-serif; }
    h2 { text-align:center; margin:20px 0; }
    table { background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.1); }
    th { background:#007BFF; color:#fff; text-align:center; }
    td { vertical-align: middle; }
    .btn { margin:2px; }

    /* Bloques diferenciados dentro del modal */
    .form-block {
        padding:10px 15px;
        border-radius:8px;
        margin-bottom:10px;
    }
    .form-light { background:#f9f9f9; }
    .form-blue { background:#e9f2ff; }
    .form-yellow { background:#fffbe6; }
</style>
</head>
<body>
<div class="container">
    <h2>📋 Gestionar Audiencias</h2>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>👤 Interno</th>
                <th>👨⚖️ Abogado</th>
                <th>📑 Tipo</th>
                <th>🏛️ Tribunal</th>
                <th>📄 RIT</th>
                <th>📅 Fecha</th>
                <th>⏰ Hora</th>
                <th>⚙️ Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['nombres']." ".$row['apellidos'] ?></td>
                <td><?= $row['abogado'] ?></td>
                <td><?= $row['tipo_audiencia'] ?></td>
                <td><?= $row['tribunal'] ?></td>
                <td><?= $row['rit'] ?></td>
                <td><?= $row['fecha'] ?></td>
                <td><?= $row['hora'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal<?= $row['id'] ?>">✏️ Editar</button>
                    <a href="index.php?page=gestionar_audiencias&delete=<?= $row['id'] ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('¿Eliminar esta audiencia?')">🗑️ Eliminar</a>
                </td>
            </tr>

            <!-- MODAL -->
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" action="index.php?page=gestionar_audiencias">
                    <div class="modal-header bg-primary text-white">
                      <h5 class="modal-title">✏️ Editar Audiencia</h5>
                      <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="update_id" value="<?= $row['id'] ?>">

                      <div class="form-block form-light">
                        <label>📑 Tipo:</label>
                        <input type="text" name="tipo_audiencia" class="form-control" value="<?= $row['tipo_audiencia'] ?>" required>
                      </div>

                      <div class="form-block form-blue">
                        <label>🏛️ Tribunal:</label>
                        <input type="text" name="tribunal" class="form-control" value="<?= $row['tribunal'] ?>" required>
                      </div>

                      <div class="form-block form-yellow">
                        <label>📄 RIT:</label>
                        <input type="text" name="rit" class="form-control" value="<?= $row['rit'] ?>" required>
                      </div>

                      <div class="form-block form-light">
                        <label>📅 Fecha:</label>
                        <input type="date" name="fecha" class="form-control" value="<?= $row['fecha'] ?>" required>
                      </div>

                      <div class="form-block form-blue">
                        <label>⏰ Hora:</label>
                        <input type="time" name="hora" class="form-control" value="<?= $row['hora'] ?>" required>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-success">💾 Guardar</button>
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">❌ Cancelar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
