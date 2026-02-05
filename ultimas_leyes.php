<?php
date_default_timezone_set("America/Santiago");

// URL oficial Ley Chile (últimas 20 leyes)
$url = "https://www.leychile.cl/Consulta/obtxml?opt=3&cantidad=20";

// Intentar obtener el XML
$xmlData = @file_get_contents($url);
?>
<section class="card">
  <h2>📜 Últimas Leyes Publicadas</h2>

  <?php if ($xmlData === false): ?>
    <p style="color:red">❌ Error al conectar con el servicio de Ley Chile.</p>
  <?php else: 
    $xml = @simplexml_load_string($xmlData);
    if ($xml === false): ?>
      <p style="color:red">❌ No se pudo procesar la respuesta XML.</p>
    <?php else: 
      echo "<ul style='list-style:none;padding:0;margin:0'>";
      $firstDate = null; $count = 0;
      foreach ($xml->NORMA as $norma):
        $fecha   = (string)$norma->FECHA_PUBLICACION;
        $titulo  = (string)$norma->TITULO;
        $numero  = (string)$norma->TIPOS_NUMEROS->TIPO_NUMERO->COMPUESTO;
        $idNorma = (string)$norma['idNorma']; // <-- ID único para link

        if ($count == 0) $firstDate = $fecha;
        $count++;
  ?>
        <li style="margin-bottom:1rem;padding:1rem;border:1px solid var(--border);border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)">
          <div style="font-weight:600;color:var(--primary)"><?= htmlspecialchars($numero) ?></div>
          <div><?= htmlspecialchars($titulo) ?></div>
          <small style="color:var(--muted)">📅 Publicada: <?= htmlspecialchars($fecha) ?></small><br>
          <a href="https://www.leychile.cl/Navegar?idNorma=<?= urlencode($idNorma) ?>" 
             target="_blank" 
             style="color:var(--primary);font-weight:500;text-decoration:none">
             🔗 Ver en Ley Chile
          </a>
        </li>
  <?php 
      endforeach;
      echo "</ul>";

      // Cálculo de días desde la última ley
      if ($firstDate):
        $hoy = new DateTime();
        $pub = new DateTime($firstDate);
        $diff = $hoy->diff($pub)->days;
  ?>
        <div style="margin-top:20px;padding:1rem;background:#eef5ff;border-left:4px solid var(--primary)">
          Han pasado <strong><?= $diff ?> días</strong> desde la última ley publicada (<?= $firstDate ?>).
        </div>
  <?php 
      endif;
    endif;
  endif; ?>
</section>
