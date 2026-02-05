<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

$servername = "localhost";
$username   = "b";
$password   = "Jesu1994!!";
$dbname     = "penitenciario";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Error: " . $conn->connect_error); }

/* Procesar acciones */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = (int) $_POST['id'];
        $sql = "DELETE FROM solicitudes WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['update'])) {
        $id   = (int) $_POST['id'];
        $tipo = $_POST['tipo_solicitud'];
        $sql = "UPDATE solicitudes SET tipo_solicitud=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $tipo, $id);
        $stmt->execute();
        $stmt->close();
    }
}

$sql = "SELECT * FROM solicitudes ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestionar Escritos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f5f7fb; font-family: system-ui, sans-serif; }
    h1 { text-align:center; margin:20px 0; font-weight:600; color:#0b3c75; }
    .card-escrito { border:1px solid #e0e0e0; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.08); }
    .card-escrito:hover { box-shadow:0 4px 10px rgba(0,0,0,.12); }
    .card-body small { color:#6e6e73; }
    .actions button { margin-right:6px; }
  </style>
</head>
<body>
<div class="container">
  <h1>📑 Gestionar Escritos</h1>

  <?php if ($result->num_rows > 0): ?>
    <div class="row g-3">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-6">
          <div class="card card-escrito">
            <div class="card-body">
              <h5 class="card-title">
                <span class="badge bg-primary"><?= htmlspecialchars($row['tipo_solicitud']) ?></span>
              </h5>
              <p class="card-text">
                📄 <a href="<?= htmlspecialchars($row['archivo']) ?>" download>
                  <?= basename($row['archivo']) ?>
                </a>
              </p>
              <small>ID: <?= $row['id'] ?> • Subido el <?= date("d-m-Y H:i", strtotime($row['fecha'] ?? 'now')) ?></small>
              <div class="actions mt-3">
                <!-- Editar -->
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">✏️ Editar</button>
                <!-- Eliminar -->
                <form method="post" action="index.php?page=gestionar_escritos" style="display:inline;" onsubmit="return confirm('¿Eliminar este escrito?');">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" name="delete" class="btn btn-sm btn-danger">🗑️ Eliminar</button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Edición -->
        <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post" action="index.php?page=gestionar_escritos">
                <div class="modal-header">
                  <h5 class="modal-title">Editar Solicitud #<?= $row['id'] ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <div class="mb-3">
                    <label class="form-label">Tipo de Solicitud</label>
                    <input type="text" class="form-control" name="tipo_solicitud" 
                           value="<?= htmlspecialchars($row['tipo_solicitud']) ?>" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="update" class="btn btn-primary">Guardar cambios</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info text-center">No se encontraron escritos registrados.</div>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
