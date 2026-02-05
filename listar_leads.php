<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

require_once('db.php');
$conn->select_db("penitenciario");

// Lógica de Archivación/Restauración
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $new_status = ($_GET['action'] == 'archive') ? 'Archivado' : 'Pendiente';
    $conn->query("UPDATE leads SET estado = '$new_status' WHERE id = $id");
    header("Location: listar_leads.php");
    exit;
}

$actives = $conn->query("SELECT * FROM leads WHERE estado != 'Archivado' ORDER BY id DESC");
$archived = $conn->query("SELECT * FROM leads WHERE estado = 'Archivado' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lead Management Center | Lawyers.cl</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --blue: #003366; --bg: #f8f9fa; --border: #dee2e6; }
        body { background: var(--bg); font-family: 'Segoe UI', sans-serif; padding: 20px; color: #333; }
        .container { max-width: 1550px; margin: auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        
        h2 { color: var(--blue); font-weight: 800; border-bottom: 3px solid var(--blue); padding-bottom: 10px; margin-bottom: 25px; }

        /* Tabs */
        .tabs { display: flex; gap: 10px; border-bottom: 2px solid var(--border); margin-bottom: 20px; }
        .tab-btn { padding: 12px 25px; cursor: pointer; border: none; background: none; font-weight: 700; color: #888; border-bottom: 3px solid transparent; }
        .tab-btn.active { color: var(--blue); border-bottom-color: var(--blue); }

        /* Estilo de Tabla */
        table.dataTable { border-collapse: collapse !important; font-size: 13px; }
        table.dataTable thead th { background: #f1f3f5 !important; color: var(--blue) !important; text-transform: uppercase; padding: 15px !important; }
        
        /* Cajas de Contacto Copiables */
        .contact-pill {
            display: inline-flex; align-items: center; background: #f1faff; border: 1px solid #cce5ff;
            padding: 4px 8px; border-radius: 6px; font-family: monospace; font-size: 12px; margin-bottom: 4px;
        }
        .copy-btn {
            background: none; border: none; color: var(--blue); cursor: pointer; margin-left: 8px; font-size: 14px;
            transition: 0.2s;
        }
        .copy-btn:hover { color: #28a745; transform: scale(1.2); }

        /* Mensaje a la vista */
        .msg-preview {
            background: #fdfdfd; padding: 8px; border-left: 3px solid #eee; font-style: italic;
            max-width: 400px; color: #555; font-size: 12px; line-height: 1.4;
        }

        /* Acciones */
        .btn-act { padding: 6px 12px; border-radius: 6px; border: 1px solid #ddd; background: #fff; color: var(--blue); text-decoration: none; display: inline-block; }
        .btn-act:hover { background: var(--blue); color: #fff; }
        .btn-archive:hover { background: #dc3545; color: #fff; border-color: #dc3545; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-headset me-2"></i> Strategic Response Unit - Leads</h2>

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab(event, 'activos')">PENDIENTES (<?= $actives->num_rows ?>)</button>
        <button class="tab-btn" onclick="switchTab(event, 'historial')">ARCHIVADOS (<?= $archived->num_rows ?>)</button>
    </div>

    <div id="activos" class="tab-content active">
        <table class="display datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ingreso</th>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th>Requerimiento (Max 299 char)</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = $actives->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= $r['id'] ?></strong></td>
                    <td style="white-space:nowrap;"><?= date('d/m/y H:i', strtotime($r['fecha_creacion'])) ?></td>
                    <td><strong style="color:var(--blue);"><?= htmlspecialchars($r['nombre']) ?></strong></td>
                    <td>
                        <div class="contact-pill">
                            <i class="fas fa-phone me-2 text-muted"></i>
                            <span><?= htmlspecialchars($r['telefono']) ?></span>
                            <button class="copy-btn" onclick="copyText('<?= $r['telefono'] ?>')" title="Copiar Teléfono"><i class="fas fa-copy"></i></button>
                        </div><br>
                        <div class="contact-pill">
                            <i class="fas fa-envelope me-2 text-muted"></i>
                            <span><?= htmlspecialchars($r['email']) ?></span>
                            <button class="copy-btn" onclick="copyText('<?= $r['email'] ?>')" title="Copiar Correo"><i class="fas fa-copy"></i></button>
                        </div>
                    </td>
                    <td><div class="msg-preview"><?= nl2br(htmlspecialchars($r['mensaje'])) ?></div></td>
                    <td class="text-center">
                        <div style="display:flex; gap:5px; justify-content:center;">
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $r['telefono']) ?>" target="_blank" class="btn-act" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            <a href="mailto:<?= $r['email'] ?>" class="btn-act" title="Email"><i class="fas fa-reply"></i></a>
                            <a href="index.php?page=listar_leads.php?action=archive&id=<?= $r['id'] ?>" class="btn-act btn-archive" onclick="return confirm('¿Archivar lead?')" title="Baja"><i class="fas fa-archive"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="historial" class="tab-content">
        <table class="display datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Nombre</th>
                    <th>Mensaje Completo</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = $archived->fetch_assoc()): ?>
                <tr style="opacity: 0.6;">
                    <td>#<?= $r['id'] ?></td>
                    <td><?= date('d/m/y', strtotime($r['fecha_creacion'])) ?></td>
                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                    <td><div class="msg-preview"><?= htmlspecialchars($r['mensaje']) ?></div></td>
                    <td><a href="index.php?page=listar_leads.php?action=restore&id=<?= $r['id'] ?>" class="btn-act"><i class="fas fa-undo"></i></a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
            order: [[0, 'desc']],
            pageLength: 25
        });
    });

    function switchTab(evt, tabName) {
        $('.tab-content').removeClass('active');
        $('.tab-btn').removeClass('active');
        $('#' + tabName).addClass('active');
        $(evt.currentTarget).addClass('active');
    }

    // Función mágica para copiar al portapapeles
    function copyText(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copiado: ' + text);
        });
    }
</script>
</body>
</html>
