<?php
/**
 * ARCHIVO HISTÓRICO - LAWYERS.CL SaaS v7.1
 * Incluye Restauración y Eliminación Permanente
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }

require_once 'db.php'; // Conexión Maestra SaaS

$user_id = $_SESSION['user_id'];
$user_rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'abogado';

// --- QUERY DE ARCHIVADOS ---
if ($user_rol === 'admin') {
    $sql = "SELECT i.*, u.username as nombre_abogado 
            FROM internos i 
            LEFT JOIN usuarios u ON i.usuario_id = u.id 
            WHERE i.estado_procesal = 'archivado' 
            ORDER BY i.fecha_actualizacion DESC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT i.*, u.username as nombre_abogado 
            FROM internos i 
            LEFT JOIN usuarios u ON i.usuario_id = u.id 
            WHERE i.usuario_id = ? AND i.estado_procesal = 'archivado' 
            ORDER BY i.fecha_actualizacion DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Archivo Histórico - LAWYERS.CL</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { --vault-dark: #334155; --bg: #f1f5f9; --border: #e2e8f0; }
        body { font-family: sans-serif; background: var(--bg); padding: 20px; }
        .vault-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 4px solid var(--vault-dark); }
        
        .action-btns { display: flex; gap: 8px; justify-content: flex-end; }
        
        .btn-restore { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 700; text-decoration: none; transition: 0.2s; }
        .btn-restore:hover { background: #16a34a; color: white; }
        
        .btn-purge { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 700; text-decoration: none; transition: 0.2s; }
        .btn-purge:hover { background: #dc2626; color: white; }
    </style>
</head>
<body>

<div class="vault-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0" style="color: var(--vault-dark);"><i class="fas fa-archive me-2"></i> Bóveda Histórica</h1>
            <p class="text-muted small mb-0">Gestión de expedientes fuera de la vista activa</p>
        </div>
        <a href="index.php?page=listar_internos" class="btn btn-outline-secondary" style="padding: 8px 15px; border-radius: 8px; text-decoration: none; color: #64748b; border: 1px solid #cbd5e1; font-size: 13px;">
            <i class="fas fa-arrow-left me-2"></i> Volver a Activos
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert" style="background:#f0fdf4; color:#166534; padding:12px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0; font-size: 14px;">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <table id="archiveTable" class="display w-100">
        <thead>
            <tr>
                <th>ID</th>
                <th>Interno</th>
                <th>RUT</th>
                <th>Fecha de Baja</th>
                <th>Cárcel</th>
                <?php if ($user_rol === 'admin'): ?><th>Abogado</th><?php endif; ?>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr id="row-<?= $row['id'] ?>">
                <td class="text-muted">#<?= $row['id'] ?></td>
                <td class="fw-bold"><?= htmlspecialchars($row['nombres'] . " " . $row['apellidos']) ?></td>
                <td class="font-monospace small"><?= $row['rut'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['fecha_actualizacion'])) ?></td>
                <td><?= htmlspecialchars($row['carcel']) ?></td>
                <?php if ($user_rol === 'admin'): ?><td><?= htmlspecialchars($row['nombre_abogado']) ?></td><?php endif; ?>
                <td class="text-end">
                    <div class="action-btns">
                        <a href="restaurar_interno.php?id=<?= $row['id'] ?>" class="btn-restore" title="Devolver a lista activa">
                            <i class="fas fa-undo"></i> RESTAURAR
                        </a>
                        <button class="btn-purge" onclick="confirmarPurga(<?= $row['id'] ?>)" title="Borrar definitivamente">
                            <i class="fas fa-trash-alt"></i> ELIMINAR
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#archiveTable').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        order: [[3, 'desc']]
    });
});

function confirmarPurga(id) {
    Swal.fire({
        title: '¿Confirmar eliminación?',
        text: '⚠️ Esta acción es irreversible. El expediente se borrará permanentemente de la base de datos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, borrar para siempre',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir al script de eliminación física
            window.location.href = 'eliminar_interno.php?id=' + id;
        }
    });
}
</script>

</body>
</html>
