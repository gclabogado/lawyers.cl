<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php?page=acceso");
    exit;
}

$conn = new mysqli("localhost", "b", "Jesu1994!!", "penitenciario");
if ($conn->connect_error) die("Error DB: " . $conn->connect_error);

$q = $_GET['q'] ?? "";

$sql = "
    SELECT a.*, i.nombres, i.apellidos, d.nombre AS abogado_nombre
    FROM audiencias a
    JOIN internos i ON a.interno_id = i.id
    LEFT JOIN defensores d ON a.abogado_id = d.id
    WHERE a.resultado IS NOT NULL AND a.resultado <> ''
";
if ($q !== "") {
    $q = $conn->real_escape_string($q);
    $sql .= " AND (i.nombres LIKE '%$q%' OR i.apellidos LIKE '%$q%' OR i.rut LIKE '%$q%' OR a.tribunal LIKE '%$q%')";
}
$sql .= " ORDER BY a.fecha DESC, a.hora DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📑 Resultados Ingresados</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
  body { background:#f8f9fa; }
  .resultado-linea {
    padding:8px 12px;
    border-bottom:1px solid #ddd;
    font-size:15px;
  }
  .resultado-linea strong { color:#002244; }
  .resultado-linea em { color:#666; }
  .acciones a { margin-left:10px; font-size:14px; text-decoration:none; cursor:pointer; }
</style>
</head>
<body class="container mt-4">

<h3>📑 Resultados Ingresados</h3>

<form method="get" class="form-inline mb-3">
  <input type="hidden" name="page" value="resultados">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control mr-2" placeholder="Filtrar por nombre, RUT o tribunal">
  <button class="btn btn-primary">🔍 Buscar</button>
</form>

<div class="listado">
<?php if ($res && $res->num_rows > 0): ?>
  <?php while ($row = $res->fetch_assoc()): 
    $id = $row['id'];
    $minuta = htmlspecialchars($row['minuta']);
    $resultado = htmlspecialchars($row['resultado']);
  ?>
    <div class="resultado-linea">
      <strong><?= htmlspecialchars($row['nombres']." ".$row['apellidos']) ?></strong>
      — <?= htmlspecialchars($row['rit']) ?>
      — <em><?= htmlspecialchars($row['tribunal']) ?></em>
      — <?= substr(strip_tags($row['resultado']),0,150) ?>...
      <span class="acciones">
        <a data-toggle="modal" data-target="#minutaModal<?= $id ?>">📂 Minuta</a>
        <a data-toggle="modal" data-target="#resModal<?= $id ?>">📑 Ver más</a>
      </span>
    </div>

    <!-- Modal Minuta -->
    <div class="modal fade" id="minutaModal<?= $id ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title">📂 Minuta — <?= htmlspecialchars($row['rit']) ?></h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body"><?= nl2br($minuta) ?: "<em>No registrada.</em>" ?></div>
        </div>
      </div>
    </div>

    <!-- Modal Resultado -->
    <div class="modal fade" id="resModal<?= $id ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">📑 Resultado — <?= htmlspecialchars($row['rit']) ?></h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body"><?= nl2br($resultado) ?></div>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p>No hay resultados ingresados.</p>
<?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
