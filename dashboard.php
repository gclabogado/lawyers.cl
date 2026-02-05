<?php
// =======================================================
// DASHBOARD COMMAND CENTER v3.7 (SaaS Edition - Filtrada)
// =======================================================

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }

require_once 'db.php'; 

$mi_id  = $_SESSION['user_id'];
$mi_rol = $_SESSION['rol'];

// --- LÓGICA DE FILTRO DE SEGURIDAD Y ESTADO ---
// Filtramos por usuario (SaaS) y excluimos explícitamente a los archivados
$condicion_base = ($mi_rol === 'admin') 
    ? "(estado_procesal != 'archivado' OR estado_procesal IS NULL)" 
    : "usuario_id = '$mi_id' AND (estado_procesal != 'archivado' OR estado_procesal IS NULL)";

// --- CARGA DE DATOS ---

// 1. Totales Activos (Excluye Archivados)
$sql_kpi = "
    SELECT 'Mis Clientes Activos' AS name, COUNT(*) AS total FROM internos WHERE $condicion_base
    UNION ALL SELECT 'Modelos de Escritos' AS name, COUNT(*) AS total FROM solicitudes
";

$totales = $conn->query($sql_kpi);
$cards = [];
if ($totales) {
    while ($row = $totales->fetch_assoc()) { $cards[] = $row; }
}

// 2. Próximos términos (Solo de internos activos)
$sql_proximos = "
    SELECT id, nombres, apellidos, rut, carcel, fecha_termino, 
           DATEDIFF(fecha_termino, CURDATE()) AS dias_restantes
    FROM internos
    WHERE $condicion_base
    AND fecha_termino BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
    ORDER BY fecha_termino ASC LIMIT 10
";
$q_proximos = $conn->query($sql_proximos);

// 3. Distribución Penal (Solo de internos activos)
$sql_carceles = "
    SELECT carcel, COUNT(*) total 
    FROM internos 
    WHERE $condicion_base AND carcel <> '' 
    GROUP BY carcel 
    ORDER BY total DESC LIMIT 5
";
$q_carceles = $conn->query($sql_carceles);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
?>

<style>
    :root { --primary-dark: #0f172a; --accent-color: #3b82f6; --indigo: #6366f1; }
    .kpi-metric { background: white; border-radius: 16px; padding: 1.5rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); height: 100%; transition: all 0.3s ease; }
    .kpi-metric:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-color: var(--indigo); }
    .hero-widget { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-radius: 20px; padding: 2.5rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }
    .table-container { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
    .table-header { background: #f8fafc; padding: 1.25rem; border-bottom: 1px solid #e2e8f0; }
    .avatar-initials { width: 40px; height: 40px; background: #eef2ff; color: var(--indigo); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; }
    .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .icon-blue { background: #eff6ff; color: #3b82f6; }
    .icon-indigo { background: #eef2ff; color: #6366f1; }
</style>

<div class="container-fluid py-4">
    
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">Bienvenido, <?= h($_SESSION['username']) ?></h2>
            <p class="text-slate-500">Panel de Control Operativo <span class="badge bg-success-subtle text-success border border-success-subtle ms-2">Casos Activos</span></p>
        </div>
        <div class="text-end">
            <span class="text-muted small">Última actualización: <?= date('H:i') ?></span>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="hero-widget h-100 shadow-xl">
                <div class="position-relative z-1">
                    <span class="badge bg-indigo-500 mb-3 px-3 py-2 rounded-pill" style="background:#4f46e5">Enterprise v7.1</span>
                    <h1 class="display-5 fw-bold mb-2"><?= ($mi_rol === 'admin') ? 'Consola de Mando' : 'Escritorio Jurídico'; ?></h1>
                    <p class="text-slate-400 fs-5">
                        Estás visualizando únicamente los expedientes en gestión. Los casos cerrados residen en el Archivo Histórico.
                    </p>
                    <div class="mt-4">
                        <a href="index.php?page=agregar_internosv3" class="btn btn-primary px-4 py-2 rounded-pill fw-bold" style="background:#4f46e5; border:none">
                            <i class="fas fa-plus-circle me-2"></i> Nuevo Ingreso Smart
                        </a>
                        <a href="index.php?page=archivo_internos" class="btn btn-outline-light px-4 py-2 rounded-pill fw-bold ms-2">
                            <i class="fas fa-archive me-2"></i> Ver Bóveda
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="row g-3 h-100">
                <?php foreach($cards as $c): 
                    $is_escritos = (strpos($c['name'], 'Modelos') !== false);
                    $iconClass = $is_escritos ? 'fa-file-signature' : 'fa-user-check';
                    $boxColor = $is_escritos ? 'icon-indigo' : 'icon-blue';
                ?>
                <div class="col-12">
                    <div class="kpi-metric d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-slate-500 small text-uppercase fw-bold d-block mb-1"><?= h($c['name']) ?></span>
                            <h2 class="fw-bold text-slate-900 mb-0"><?= number_format($c['total']) ?></h2>
                            <small class="<?= $is_escritos ? 'text-indigo-500' : 'text-success' ?> fw-medium">
                                <?= $is_escritos ? 'Librería automatizada' : 'Expedientes vigentes' ?>
                            </small>
                        </div>
                        <div class="icon-box <?= $boxColor ?>">
                            <i class="fas <?= $iconClass ?>"></i>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="table-container h-100 shadow-sm">
                <div class="table-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="fas fa- hourglass-half text-warning me-2"></i> Alertas de Término (Activos)</h6>
                    <a href="index.php?page=listar_internos" class="text-indigo-600 small fw-bold text-decoration-none">Ver listado completo</a>
                </div>
                <div class="table-responsive">
                    <table class="table custom-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Interno</th>
                                <th>Unidad</th>
                                <th>Término</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($q_proximos && $q_proximos->num_rows > 0): ?>
                                <?php while($r = $q_proximos->fetch_assoc()): 
                                    $initials = strtoupper(substr($r['nombres'] ?? 'I',0,1).substr($r['apellidos'] ?? 'N',0,1));
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initials me-3"><?= $initials ?></div>
                                            <div>
                                                <div class="fw-bold text-slate-900"><?= h($r['nombres']) ?></div>
                                                <div class="small text-slate-500"><?= h($r['apellidos']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-slate-600 small"><?= h($r['carcel']) ?></span></td>
                                    <td class="fw-medium text-slate-700"><?= date("d/m/Y", strtotime($r['fecha_termino'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3">
                                            <?= $r['dias_restantes'] ?> días
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-5 text-slate-400">Sin vencimientos críticos en casos activos.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="table-container h-100 shadow-sm">
                <div class="table-header">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="fas fa-map-marked-alt text-indigo-500 me-2"></i> Carga por Recinto</h6>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if ($q_carceles && $q_carceles->num_rows > 0): ?>
                        <?php while($r = $q_carceles->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0">
                            <span class="fw-medium text-slate-700 small"><?= h($r['carcel']) ?></span>
                            <span class="badge rounded-pill bg-primary"><?= $r['total'] ?></span>
                        </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center py-5 text-muted">Sin datos.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
