<?php
/**
 * LISTAR INTERNOS - LAWYERS.CL SaaS v7.1
 * Filtro inteligente: No muestra casos con estado_procesal = 'archivado'
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$user_rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'abogado';

// --- CONSULTA FILTRADA ---
if ($user_rol === 'admin') {
    $sql = "SELECT i.*, u.username as nombre_abogado 
            FROM internos i 
            LEFT JOIN usuarios u ON i.usuario_id = u.id 
            WHERE i.estado_procesal != 'archivado' OR i.estado_procesal IS NULL
            ORDER BY i.id DESC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT i.*, u.username as nombre_abogado 
            FROM internos i 
            LEFT JOIN usuarios u ON i.usuario_id = u.id 
            WHERE i.usuario_id = ? AND (i.estado_procesal != 'archivado' OR i.estado_procesal IS NULL)
            ORDER BY i.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

function getKpiEmoji($fecha) {
    if(!$fecha) return "—";
    $dias = (new DateTime($fecha))->diff(new DateTime())->days;
    if($dias <= 30) return "🟢 $dias d";
    if($dias <= 90) return "🟡 $dias d";
    return "🔴 $dias d";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Casos Activos - LAWYERS.CL</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --border: #e2e8f0; }
        body { font-family: sans-serif; background: var(--bg); padding: 20px; }
        .page-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .action-btn { width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border); background: white; cursor: pointer; transition: 0.2s; }
        .action-btn:hover { background: #f1f5f9; color: var(--primary); }
        .btn-archive:hover { background: #fff7ed; color: #f97316; border-color: #fdba74; }
        
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(2px); }
        .modal-overlay.active { display:flex; }
        .modal-win { background:white; border-radius:16px; width:90%; max-width:800px; max-height:85vh; overflow-y:auto; padding:30px; }
    </style>
</head>
<body>

<div class="page-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">📁 Casos Activos</h1>
            <small class="text-muted">Gestionando <?= $result->num_rows ?> expedientes vigentes</small>
        </div>
        <a href="index.php?page=agregar_internosv3" class="btn btn-primary" style="background:var(--primary); border:none; padding:10px 20px; border-radius:8px; color:white; text-decoration:none;">
            <i class="fas fa-plus me-2"></i> Nuevo Ingreso
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" style="padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0; background:#f0fdf4; color:#166534;">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <table id="mainTable" class="display w-100">
        <thead>
            <tr>
                <th>ID</th>
                <th>Interno</th>
                <th>RUT</th>
                <th>Cárcel</th>
                <th>Entrevista</th>
                <th>KPI</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr id="row-<?= $row['id'] ?>">
                <td class="fw-bold">#<?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombres'] . " " . $row['apellidos']) ?></td>
                <td class="small font-monospace"><?= $row['rut'] ?></td>
                <td><span class="badge" style="background:#f1f5f9; color:#475569; padding:5px 10px; border-radius:6px; border:1px solid #e2e8f0;"><?= $row['carcel'] ?></span></td>
                <td><?= $row['fecha_entrevista'] ? date('d-m-Y', strtotime($row['fecha_entrevista'])) : '—' ?></td>
                <td><?= getKpiEmoji($row['fecha_entrevista']) ?></td>
                <td class="text-end">
                    <button class="action-btn" onclick='showDetails(<?= json_encode($row) ?>)' title="Ver Ficha"><i class="fas fa-eye"></i></button>
                    
                    <button class="action-btn btn-archive" onclick="confirmarBaja(<?= $row['id'] ?>)" title="Mover a Archivo">
                        <i class="fas fa-box-archive"></i>
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modalDetail">
    <div class="modal-win">
        <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
            <h4 class="fw-bold mb-0" id="m_titulo">Ficha Técnica</h4>
            <button onclick="closeModal()" style="border:none; background:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div id="m_body" class="row g-3"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#mainTable').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        order: [[0, 'desc']]
    });
});

function confirmarBaja(id) {
    Swal.fire({
        title: '¿Archivar este caso?',
        text: "Se moverá al archivo histórico pero no se eliminará de la base de datos.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316',
        confirmButtonText: 'Sí, archivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'dar_de_baja.php?id=' + id;
        }
    });
}

function showDetails(data) {
    const body = document.getElementById('m_body');
    body.innerHTML = `
        <div class="col-md-6"><b>RUT:</b> ${data.rut}</div>
        <div class="col-md-6"><b>Delito:</b> ${data.delito}</div>
        <div class="col-md-12"><hr></div>
        <div class="col-md-6"><b>Tribunal:</b> ${data.juzgado}</div>
        <div class="col-md-6"><b>RIT:</b> ${data.rit}</div>
        <div class="col-md-6"><b>Término Condena:</b> ${data.fecha_termino}</div>
        <div class="col-md-6"><b>Unidad:</b> ${data.carcel}</div>
        <div class="col-md-12"><b>Observaciones:</b><br>${data.observaciones}</div>
    `;
    document.getElementById('m_titulo').innerText = data.nombres + ' ' + data.apellidos;
    document.getElementById('modalDetail').classList.add('active');
}

function closeModal() { document.getElementById('modalDetail').classList.remove('active'); }
</script>

</body>
</html>
