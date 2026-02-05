<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    die('<div class="alert alert-danger text-center m-5"><h3>⚠️ Acceso restringido</h3><p>Debe iniciar sesión para acceder al mapa de estructura.</p></div>');
}

$conexion = new mysqli("localhost", "b", "Jesu1994!!", "");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

function esc($t){ return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); }

$databases = [];
$res = $conexion->query("SHOW DATABASES");
while ($row = $res->fetch_assoc()) {
    $db = $row['Database'];
    if (!in_array($db, ['information_schema','performance_schema','mysql','sys'])) {
        $databases[] = $db;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mapa Técnico del Sistema — Lawyers.cl</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<style>
body { background:#f8f9fa; font-family:'Segoe UI',sans-serif; }
.card { margin-bottom:1rem; }
.db-title { background:#1b263b; color:white; padding:10px 15px; border-radius:8px 8px 0 0; }
small { color:#6c757d; }
pre { background:#e9ecef; padding:10px; border-radius:5px; font-size:0.9em; }
.sample-table td, .sample-table th { vertical-align:middle; font-size:0.9em; }
</style>
</head>
<body class="p-4">

<div class="container">
  <h1 class="text-center mb-4 fw-bold text-primary"><i class="fas fa-database me-2"></i>Mapa Técnico en Vivo del Sistema</h1>

  <p class="lead text-center text-muted mb-5">
    Este panel muestra <strong>en tiempo real</strong> la estructura interna del ecosistema Lawyers.cl, compuesto por distintas bases de datos relacionadas.  
    Cada base tiene un propósito funcional dentro de la arquitectura general del sistema jurídico-penitenciario administrado por Don Gabriel Calderón Lewin ⚖️.
  </p>

  <div class="alert alert-info">
    <h5 class="fw-bold mb-2">🔍 Propósito</h5>
    <p>Este módulo permite visualizar cómo se organizan las distintas partes del sitio:
    desde la gestión penitenciaria y publicaciones normativas, hasta módulos administrativos y empresariales.  
    Incluye una vista “viva”, que toma ejemplos reales desde la base de datos.</p>

    <h6 class="mt-3">📂 Bases principales:</h6>
    <ul>
      <li><strong>penitenciario</strong>: gestión de internos, audiencias, escritos y publicaciones legales.</li>
      <li><strong>empresa</strong>: administración de miembros, normativa corporativa y documentos internos.</li>
      <li><strong>gestion_penal</strong>: planeado para automatizar tareas penales (en desarrollo o experimental).</li>
    </ul>
  </div>

  <?php foreach ($databases as $db): ?>
    <div class="card shadow-sm">
      <div class="db-title fw-bold"><i class="fas fa-database me-2"></i><?= esc($db) ?></div>
      <div class="card-body">
        <?php
          $conexion->select_db($db);
          $tables = $conexion->query("SHOW TABLES");
        ?>
        <?php if ($tables->num_rows > 0): ?>
          <div class="accordion" id="accordion_<?= esc($db) ?>">
          <?php while ($t = $tables->fetch_array()):
            $table = $t[0];
            $info = $conexion->query("SHOW TABLE STATUS LIKE '$table'")->fetch_assoc();
            $count = (int)$info['Rows'];
          ?>
            <div class="accordion-item">
              <h2 class="accordion-header" id="heading_<?= esc($db.'_'.$table) ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?= esc($db.'_'.$table) ?>">
                  <i class="fas fa-table me-2 text-secondary"></i> <?= esc($table) ?> 
                  <span class="badge bg-info text-dark ms-2"><?= $count ?> registros</span>
                </button>
              </h2>
              <div id="collapse_<?= esc($db.'_'.$table) ?>" class="accordion-collapse collapse" data-bs-parent="#accordion_<?= esc($db) ?>">
                <div class="accordion-body">
                  <table class="table table-sm table-bordered mb-3">
                    <thead class="table-light">
                      <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>
                    </thead>
                    <tbody>
                    <?php
                      $columns = $conexion->query("DESCRIBE $table");
                      while ($col = $columns->fetch_assoc()):
                    ?>
                      <tr>
                        <td><?= esc($col['Field']) ?></td>
                        <td><?= esc($col['Type']) ?></td>
                        <td><?= esc($col['Null']) ?></td>
                        <td><?= esc($col['Key']) ?></td>
                        <td><?= esc($col['Default']) ?></td>
                      </tr>
                    <?php endwhile; ?>
                    </tbody>
                  </table>

                  <?php
                    // Muestra algunos registros como ejemplo
                    $ejemplo = $conexion->query("SELECT * FROM $table LIMIT 3");
                    if ($ejemplo && $ejemplo->num_rows > 0):
                  ?>
                  <h6 class="text-muted">🧾 Ejemplo de registros:</h6>
                  <table class="table table-striped table-bordered sample-table">
                    <thead><tr>
                      <?php foreach(array_keys($ejemplo->fetch_assoc()) as $campo): ?>
                        <th><?= esc($campo) ?></th>
                      <?php endforeach; $ejemplo->data_seek(0); ?>
                    </tr></thead>
                    <tbody>
                      <?php while ($fila = $ejemplo->fetch_assoc()): ?>
                        <tr>
                          <?php foreach ($fila as $v): ?>
                            <td><?= esc(substr($v,0,60)) ?></td>
                          <?php endforeach; ?>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                  <?php else: ?>
                    <p class="text-muted fst-italic">Sin registros visibles en esta tabla.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
