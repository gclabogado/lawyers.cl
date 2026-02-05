<?php
/*
  KPI DASHBOARD v3.2 - SaaS Edition
  - Filtro: Excluye archivados.
  - UI: Tooltips inteligentes con detalles del caso al pasar el mouse.
*/

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }

require_once 'db.php'; 

// Función de seguridad
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

// 1. FILTRO DE PRIVACIDAD Y ESTADO
$mi_id  = $_SESSION['user_id'];
$mi_rol = $_SESSION['rol'];

// Condición base: Usuario correcto Y (No archivado O Nulo)
$filtro_base = ($mi_rol === 'admin') ? "1=1" : "usuario_id = '$mi_id'";
$filtro_estado = "(estado_procesal != 'archivado' OR estado_procesal IS NULL)";

// 2. CONSULTA ENRIQUECIDA (Traemos datos extra para el Tooltip)
$sql = "SELECT id, nombres, apellidos, rut, fecha_entrevista, delito, carcel, tiempo_condena, fecha_termino
        FROM internos 
        WHERE fecha_entrevista IS NOT NULL 
        AND ($filtro_base) 
        AND $filtro_estado";

$q = $conn->query($sql);

$en_plazo = [];
$vencidos = [];
$rangos = [
    '0-30' => 0, '31-60' => 0, '61-90' => 0, '91-150' => 0,
    '151-299' => 0, '300-500' => 0, '>500' => 0
];

if ($q && $q->num_rows > 0) {
    while($r = $q->fetch_assoc()){
        // Cálculos de KPI
        $f_entrevista = new DateTime($r['fecha_entrevista']);
        $hoy = new DateTime();
        $dias = $f_entrevista->diff($hoy)->days;
        $restan = 150 - $dias;
        $excedidos = $dias - 150;

        // Semáforos
        if($dias <= 30)      { $badge_class="bg-success"; $rangos['0-30']++; }
        elseif($dias <= 60)  { $badge_class="bg-info text-dark"; $rangos['31-60']++; }
        elseif($dias <= 90)  { $badge_class="bg-warning text-dark"; $rangos['61-90']++; }
        elseif($dias <= 150) { $badge_class="bg-orange text-white"; $rangos['91-150']++; }
        elseif($dias <= 299) { $badge_class="bg-danger"; $rangos['151-299']++; }
        elseif($dias <= 500) { $badge_class="bg-danger bg-gradient"; $rangos['300-500']++; }
        else                 { $badge_class="bg-dark"; $rangos['>500']++; }

        // Preparar contenido del Tooltip (Popover)
        $f_term_fmt = ($r['fecha_termino']) ? (new DateTime($r['fecha_termino']))->format('d/m/Y') : 'S/I';
        $tooltip_html = "
            <div class='text-start small'>
                <strong>Delito:</strong> ".h($r['delito'])."<br>
                <strong>Condena:</strong> ".h($r['tiempo_condena'])."<br>
                <strong>Unidad:</strong> ".h($r['carcel'])."<br>
                <span class='text-danger'><strong>Término:</strong> $f_term_fmt</span>
            </div>
        ";

        $registro = [
            'nombre' => $r['nombres'].' '.$r['apellidos'],
            'rut' => $r['rut'],
            'fecha' => $f_entrevista->format("d-m-Y"),
            'dias' => $dias,
            'restan' => max(0, $restan),
            'excedidos' => max(0, $excedidos),
            'badge' => $badge_class,
            'tooltip' => $tooltip_html // Guardamos el HTML del tooltip
        ];

        if($dias <= 150){ $en_plazo[] = $registro; } else { $vencidos[] = $registro; }
    }
}

// Ordenamiento
usort($en_plazo, fn($a,$b) => $a['restan'] <=> $b['restan']);
usort($vencidos, fn($a,$b) => $b['excedidos'] <=> $a['excedidos']);

$total = count($en_plazo) + count($vencidos);
$p_plazo = $total > 0 ? round((count($en_plazo)/$total)*100, 1) : 0;
$p_vencido = $total > 0 ? round((count($vencidos)/$total)*100, 1) : 0;
?>

<style>
    .kpi-card { border:none; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.05); transition:transform 0.2s; }
    .kpi-card:hover { transform: translateY(-3px); }
    .bg-orange { background-color: #fd7e14; }
    .table-card { border-radius:16px; overflow:hidden; border:none; box-shadow:0 4px 12px rgba(0,0,0,0.05); background: #fff; }
    .badge-pill { padding: 0.5em 0.8em; border-radius: 50rem; font-weight: 600; font-size: 0.75rem; }
    
    /* Estilo para el nombre con tooltip */
    .hover-info { cursor: help; border-bottom: 1px dashed #ccc; transition: color 0.2s; }
    .hover-info:hover { color: #4f46e5; border-bottom-color: #4f46e5; }
</style>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">Control de Plazos <span class="badge bg-primary fs-6 align-middle">v3.2</span></h2>
            <p class="text-muted small mb-0">Monitor de entrevistas y cumplimiento normativo (Solo Casos Activos)</p>
        </div>
        <div>
            <span class="badge bg-white text-dark border shadow-sm p-2">
                <i class="far fa-clock me-1 text-primary"></i> <?= date("H:i") ?>
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card kpi-card h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <p class="text-muted text-uppercase fw-bold small mb-1">Casos Activos Analizados</p>
                    <h2 class="fw-bold text-dark mb-0"><?= $total ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kpi-card h-100 border-start border-4 border-success">
                <div class="card-body">
                    <p class="text-muted text-uppercase fw-bold small mb-1">En Plazo</p>
                    <h2 class="fw-bold text-success mb-0"><?= count($en_plazo) ?></h2>
                    <small class="text-success fw-bold"><?= $p_plazo ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kpi-card h-100 border-start border-4 border-danger">
                <div class="card-body">
                    <p class="text-muted text-uppercase fw-bold small mb-1">Vencidos</p>
                    <h2 class="fw-bold text-danger mb-0"><?= count($vencidos) ?></h2>
                    <small class="text-danger fw-bold"><?= $p_vencido ?>%</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card table-card h-100 p-3">
                <h6 class="fw-bold mb-3">📊 Estado General</h6>
                <div class="d-flex justify-content-center">
                    <div style="width: 180px;"><canvas id="consolidadoChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card table-card h-100 p-3">
                <h6 class="fw-bold mb-3">📆 Distribución por Antigüedad (Días desde entrevista)</h6>
                <div style="height: 180px;"><canvas id="rangosChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="card table-card border-danger mb-4">
        <div class="card-header bg-danger text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-fire me-2"></i> Casos Críticos (>150 días)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr><th>Interno</th><th>RUT</th><th class="text-center">Días</th><th class="text-center">Exceso</th><th class="text-center">Estado</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($vencidos)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted small italic">No hay casos vencidos activos.</td></tr>
                    <?php else: foreach($vencidos as $v): ?>
                        <tr>
                            <td>
                                <span class="hover-info fw-bold" 
                                      data-bs-toggle="popover" 
                                      data-bs-trigger="hover focus" 
                                      data-bs-html="true" 
                                      title="<i class='fas fa-info-circle'></i> Detalles del Caso" 
                                      data-bs-content="<?= h($v['tooltip']) ?>">
                                    <?= h($v['nombre']) ?>
                                </span>
                            </td>
                            <td class="small"><?= h($v['rut']) ?></td>
                            <td class="text-center"><?= $v['dias'] ?></td>
                            <td class="text-center text-danger fw-bold">+<?= $v['excedidos'] ?></td>
                            <td class="text-center"><span class="badge badge-pill <?= $v['badge'] ?>">Crítico</span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-success"><i class="fas fa-check-circle me-2"></i> Casos En Plazo (Vigentes)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr><th>Interno</th><th>RUT</th><th class="text-center">Días</th><th class="text-center">Quedan</th><th class="text-center">Estado</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($en_plazo)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted small italic">Sin casos vigentes activos.</td></tr>
                    <?php else: foreach($en_plazo as $p): ?>
                        <tr>
                            <td>
                                <span class="hover-info fw-bold" 
                                      data-bs-toggle="popover" 
                                      data-bs-trigger="hover focus" 
                                      data-bs-html="true" 
                                      title="<i class='fas fa-info-circle'></i> Detalles del Caso" 
                                      data-bs-content="<?= h($p['tooltip']) ?>">
                                    <?= h($p['nombre']) ?>
                                </span>
                            </td>
                            <td class="small"><?= h($p['rut']) ?></td>
                            <td class="text-center"><?= $p['dias'] ?></td>
                            <td class="text-center fw-bold text-primary"><?= $p['restan'] ?> d</td>
                            <td class="text-center"><span class="badge badge-pill <?= $p['badge'] ?>">Vigente</span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Inicializar Popovers de Bootstrap
document.addEventListener("DOMContentLoaded", function(){
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl)
    });
});

// Gráficos (Mantenemos tu lógica visual)
new Chart(document.getElementById('consolidadoChart'), {
    type: 'doughnut',
    data: {
        labels: ['En Plazo', 'Vencidos'],
        datasets: [{
            data: [<?= count($en_plazo) ?>, <?= count($vencidos) ?>],
            backgroundColor: ['#10b981', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: { cutout: '75%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
});

new Chart(document.getElementById('rangosChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($rangos)) ?>,
        datasets: [{
            label: 'Internos Activos',
            data: <?= json_encode(array_values($rangos)) ?>,
            backgroundColor: '#4f46e5',
            borderRadius: 4
        }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
