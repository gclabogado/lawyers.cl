<?php
// =======================================================
// BUSCADOR INTELIGENTE 360° - SaaS v1.1 (FIXED)
// - AJAX en el mismo archivo
// - Session safe
// - Sin JSON directo en onclick (usa data-item base64)
// =======================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';

// --------------------
// AJAX SEARCH ENDPOINT
// --------------------
if (isset($_GET['ajax_search'])) {
    if (ob_get_length()) { @ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['loggedin']) || empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'NO_AUTH']);
        exit;
    }

    $uid = (int)$_SESSION['user_id'];
    $q = trim((string)($_GET['q'] ?? ''));
    $filtro_estado = $_GET['estado'] ?? 'activos'; // activos, archivados, todos

    $condiciones = ["usuario_id = ?"];
    $params = [$uid];
    $types = "i";

    if ($q !== '') {
        $condiciones[] = "(nombres LIKE ? OR apellidos LIKE ? OR rut LIKE ? OR rit LIKE ? OR delito LIKE ?)";
        $wild = "%{$q}%";
        array_push($params, $wild, $wild, $wild, $wild, $wild);
        $types .= "sssss";
    }

    if ($filtro_estado === 'activos') {
        $condiciones[] = "(estado_procesal != 'archivado' OR estado_procesal IS NULL OR estado_procesal = '')";
    } elseif ($filtro_estado === 'archivados') {
        $condiciones[] = "estado_procesal = 'archivado'";
    }

    $sql = "SELECT * FROM internos WHERE " . implode(" AND ", $condiciones) . " ORDER BY apellidos ASC LIMIT 20";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['error' => 'SQL_PREPARE', 'detail' => $conn->error]);
        exit;
    }

    if (!$stmt->bind_param($types, ...$params)) {
        echo json_encode(['error' => 'SQL_BIND', 'detail' => $stmt->error]);
        exit;
    }

    if (!$stmt->execute()) {
        echo json_encode(['error' => 'SQL_EXEC', 'detail' => $stmt->error]);
        exit;
    }

    $res = $stmt->get_result();
    $resultados = [];

    while ($row = $res->fetch_assoc()) {
        $n0 = mb_substr((string)($row['nombres'] ?? ''), 0, 1);
        $a0 = mb_substr((string)($row['apellidos'] ?? ''), 0, 1);
        $row['initials'] = strtoupper($n0 . $a0);

        $riesgo = strtoupper((string)($row['nivel_riesgo'] ?? ''));
        $row['riesgo_class'] = match(true) {
            str_contains($riesgo, 'ALTO') => 'bg-danger text-white',
            str_contains($riesgo, 'MED')  => 'bg-warning text-dark',
            str_contains($riesgo, 'BAJO') => 'bg-success text-white',
            default => 'bg-secondary text-white'
        };

        // id seguro para botón editar (si existe)
        $row['__id_safe'] = $row['id'] ?? ($row['interno_id'] ?? ($row['id_interno'] ?? null));

        $resultados[] = $row;
    }

    $stmt->close();
    echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
    exit;
}

// --------------------
// HTML VIEW
// --------------------
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }
$self = htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscador Inteligente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --bg-app:#f1f5f9; }
        body { background-color: var(--bg-app); font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; }
        .search-container { max-width:900px; margin:0 auto; }
        .search-box {
            position:relative; box-shadow:0 10px 25px -5px rgba(0,0,0,0.1);
            border-radius:16px; background:white; padding:10px; display:flex; align-items:center;
        }
        .search-input { border:none; outline:none; box-shadow:none; font-size:1.2rem; padding:15px; width:100%; background:transparent; }
        .search-icon { font-size:1.5rem; color:#cbd5e1; margin-left:15px; }
        .result-card {
            background:white; border:1px solid #e2e8f0; border-radius:12px;
            padding:20px; margin-bottom:15px; cursor:pointer; transition:all .2s ease;
        }
        .result-card:hover { transform:translateY(-3px); box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); border-color:#cbd5e1; }
        .avatar {
            width:50px; height:50px; border-radius:12px; background:#eff6ff; color:#3b82f6;
            display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.1rem;
        }
        .modal-header-custom { background:linear-gradient(135deg,#0f172a 0%,#334155 100%); color:white; }
        .data-label { font-size:.75rem; text-transform:uppercase; color:#64748b; font-weight:700; display:block; margin-bottom:2px; }
        .data-value { font-size:.95rem; font-weight:500; color:#1e293b; margin-bottom:15px; display:block; }
        .section-title { color:#4f46e5; font-weight:800; font-size:.8rem; text-transform:uppercase; border-bottom:1px solid #e2e8f0; margin-bottom:15px; padding-bottom:5px; margin-top:10px; }
    </style>
</head>
<body>

<div class="container py-5 search-container">

    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark"><i class="fas fa-search me-2 text-primary"></i> Buscador Centralizado</h2>
        <p class="text-muted">Encuentra expedientes por Nombre, RUT, Causa o Delito</p>
    </div>

    <div class="search-box mb-4">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchInput" class="search-input" placeholder="Escribe para buscar (Ej: Elida, 14895816, Calama, Drogas)..." autofocus>

        <select class="form-select border-0 bg-light w-auto ms-2 fw-bold text-secondary" id="statusFilter" style="border-radius:10px;">
            <option value="activos">🟢 Activos</option>
            <option value="archivados">📦 Archivados</option>
            <option value="todos">🌎 Todos</option>
        </select>
    </div>

    <div id="loading" class="text-center py-4 d-none"><i class="fas fa-circle-notch fa-spin fa-2x text-primary"></i></div>
    <div id="resultsArea">
        <div class="text-center text-muted py-5">
            <i class="fas fa-keyboard fa-3x mb-3 opacity-25"></i>
            <p>Empieza a escribir para ver resultados en tiempo real.</p>
        </div>
    </div>
</div>

<div class="modal fade" id="fichaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header modal-header-custom border-0 p-4">
                <div>
                    <h4 class="modal-title fw-bold" id="m_nombre">Nombre del Interno</h4>
                    <span class="badge bg-white text-dark mt-2" id="m_rut">RUT</span>
                    <span class="badge bg-danger ms-2" id="m_riesgo">RIESGO</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <div class="bg-white p-3 rounded-4 shadow-sm h-100">
                            <div class="section-title"><i class="fas fa-gavel me-2"></i> Situación Procesal</div>
                            <span class="data-label">Delito</span><span class="data-value fw-bold" id="m_delito"></span>
                            <span class="data-label">Causa RIT / RUC</span><span class="data-value" id="m_rit_ruc"></span>
                            <span class="data-label">Tribunal</span><span class="data-value" id="m_juzgado"></span>
                            <span class="data-label">Unidad Penal</span><span class="data-value" id="m_carcel"></span>
                            <span class="data-label">Condena</span><span class="data-value text-danger fw-bold" id="m_condena"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="bg-white p-3 rounded-4 shadow-sm h-100">
                            <div class="section-title"><i class="fas fa-calendar-alt me-2"></i> Cómputo de Fechas</div>
                            <div class="row">
                                <div class="col-6"><span class="data-label">Inicio</span><span class="data-value" id="m_inicio"></span></div>
                                <div class="col-6"><span class="data-label">Término</span><span class="data-value fw-bold text-danger" id="m_termino"></span></div>
                                <div class="col-6"><span class="data-label">Mín. Libertad</span><span class="data-value" id="m_libertad"></span></div>
                                <div class="col-6"><span class="data-label">Mín. Permiso</span><span class="data-value" id="m_permiso"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="bg-white p-3 rounded-4 shadow-sm">
                            <div class="section-title"><i class="fas fa-user-friends me-2"></i> Red de Apoyo & Gestión</div>
                            <div class="row">
                                <div class="col-md-4"><span class="data-label">Abogado</span><span class="data-value" id="m_abogado"></span></div>
                                <div class="col-md-4"><span class="data-label">Contacto 1</span><span class="data-value" id="m_c1"></span></div>
                                <div class="col-md-4"><span class="data-label">Teléfono</span><span class="data-value" id="m_tel1"></span></div>
                                <div class="col-12 border-top pt-2">
                                    <span class="data-label">Observaciones / Bitácora</span>
                                    <p class="data-value text-muted small fst-italic bg-light p-2 rounded" id="m_obs"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <a href="#" id="btn_edit" class="btn btn-outline-primary rounded-pill"><i class="fas fa-edit me-2"></i>Editar Ficha</a>
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let searchTimeout;
const ENDPOINT = "<?= $self ?>";

$(document).ready(function(){
  $('#searchInput').on('keyup', function(){
    clearTimeout(searchTimeout);
    const query = $(this).val();
    const estado = $('#statusFilter').val();
    searchTimeout = setTimeout(() => performSearch(query, estado), 250);
  });

  $('#statusFilter').on('change', function(){
    $('#searchInput').trigger('keyup');
  });

  performSearch('', 'activos');
});

function performSearch(q, estado) {
  $('#loading').removeClass('d-none');

  $.ajax({
    url: ENDPOINT,
    data: { ajax_search: 1, q: q, estado: estado },
    dataType: 'json',
    success: function(data) {
      $('#loading').addClass('d-none');
      if (data && data.error === 'NO_AUTH') {
        $('#resultsArea').html('<div class="text-center text-danger">Sesión expirada. Vuelve a iniciar sesión.</div>');
        return;
      }
      if (data && data.error) {
        $('#resultsArea').html('<div class="text-center text-danger">Error API: '+ escapeHtml(data.error) +'</div>');
        return;
      }
      renderResults(Array.isArray(data) ? data : []);
    },
    error: function(xhr) {
      $('#loading').addClass('d-none');
      console.log(xhr.responseText);
      $('#resultsArea').html('<div class="text-center text-danger">Error en la conexión o endpoint. Revisa consola (F12).</div>');
    }
  });
}

function renderResults(data) {
  let html = '';

  if (data.length === 0) {
    html = `<div class="text-center py-5 text-muted">
              <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
              <h5>No se encontraron resultados</h5>
              <p class="small">Intenta con otro nombre, RUT o cambia el filtro.</p>
            </div>`;
  } else {
    data.forEach(item => {
      let metaInfo = escapeHtml(item.rut || '');
      if (item.rit) metaInfo += ` • RIT: ${escapeHtml(item.rit)}`;

      // Guardamos item en data-item (base64) para evitar reventar onclick por comillas/saltos
      const payload = btoa(unescape(encodeURIComponent(JSON.stringify(item))));

      html += `
      <div class="result-card" data-item="${payload}">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">${escapeHtml(item.initials || '--')}</div>
          <div class="flex-grow-1">
            <h5 class="fw-bold mb-1 text-dark">${escapeHtml((item.nombres||'') + ' ' + (item.apellidos||''))}</h5>
            <div class="small text-muted fw-bold text-uppercase mb-1">${metaInfo}</div>
            <div class="small text-secondary text-truncate" style="max-width: 500px;">
              <i class="fas fa-gavel me-1 text-primary"></i> ${escapeHtml(item.delito || 'Sin delito especificado')}
            </div>
          </div>
          <div class="text-end d-none d-md-block">
            <span class="badge ${escapeHtml(item.riesgo_class || 'bg-secondary text-white')} mb-2 d-block">
              Riesgo ${escapeHtml(item.nivel_riesgo || 'N/A')}
            </span>
            <small class="text-muted"><i class="fas fa-building me-1"></i> ${escapeHtml(item.carcel || 'N/A')}</small>
          </div>
        </div>
      </div>`;
    });
  }

  $('#resultsArea').html(html);

  // Click handler seguro
  $('.result-card').off('click').on('click', function(){
    const payload = $(this).attr('data-item');
    const item = JSON.parse(decodeURIComponent(escape(atob(payload))));
    openModal(item);
  });
}

function openModal(item) {
  const val = (v) => v ? escapeHtml(String(v)) : '<span class="text-muted small fst-italic">S/I</span>';
  const fmtDate = (d) => d ? escapeHtml(String(d).split('-').reverse().join('/')) : '<span class="text-muted small fst-italic">--/--/----</span>';

  $('#m_nombre').text((item.nombres || '') + ' ' + (item.apellidos || ''));
  $('#m_rut').text(item.rut || 'S/I');
  $('#m_riesgo').text(item.nivel_riesgo || 'S/I').attr('class', 'badge ms-2 ' + (item.riesgo_class || 'bg-secondary text-white'));

  $('#m_delito').html(val(item.delito));
  $('#m_rit_ruc').html(val(item.rit) + ' / ' + val(item.ruc));
  $('#m_juzgado').html(val(item.juzgado));
  $('#m_carcel').html(val(item.carcel));
  $('#m_condena').html(val(item.tiempo_condena));

  $('#m_inicio').html(fmtDate(item.fecha_inicio));
  $('#m_termino').html(fmtDate(item.fecha_termino));
  $('#m_libertad').html(fmtDate(item.fecha_min_libertad));
  $('#m_permiso').html(fmtDate(item.fecha_min_permiso));

  $('#m_abogado').html(val(item.abogado));
  const c1 = (item.contacto1_nombre ? escapeHtml(item.contacto1_nombre) : '<span class="text-muted small fst-italic">S/I</span>');
  const par = (item.contacto1_parentesco ? escapeHtml(item.contacto1_parentesco) : 'S/I');
  $('#m_c1').html(`${c1} <span class="text-muted">(${par})</span>`);
  $('#m_tel1').html(val(item.contacto1_telefono));
  $('#m_obs').text(item.observaciones || '');

  // Edit link (si tienes editor)
  const idSafe = item.__id_safe || '';
  if (idSafe) {
    $('#btn_edit').attr('href', 'index.php?page=editar_interno&id=' + encodeURIComponent(idSafe));
  } else {
    $('#btn_edit').attr('href', '#');
  }

  new bootstrap.Modal(document.getElementById('fichaModal')).show();
}

function escapeHtml(str) {
  return String(str)
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;')
    .replaceAll('"','&quot;')
    .replaceAll("'","&#039;");
}
</script>
</body>
</html>
