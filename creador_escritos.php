<?php
/**
 * CREADOR DE ESCRITOS - LAWYERS.CL SaaS v7.1
 * Base de datos: lawyers_saas
 */
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

require_once 'db.php'; // Usa la conexión centralizada

$user_id = $_SESSION['user_id'];
$user_rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'abogado';

// --- CONSULTA DE INTERNOS (Solo activos y propios) ---
if ($user_rol === 'admin') {
    // Admin ve todos los activos
    $sql_internos = "SELECT id, nombres, apellidos, rut FROM internos 
                     WHERE (estado_procesal != 'archivado' OR estado_procesal IS NULL)
                     ORDER BY apellidos, nombres";
    $stmt_i = $conn->prepare($sql_internos);
} else {
    // Abogado ve solo sus activos
    $sql_internos = "SELECT id, nombres, apellidos, rut FROM internos 
                     WHERE usuario_id = ? AND (estado_procesal != 'archivado' OR estado_procesal IS NULL)
                     ORDER BY apellidos, nombres";
    $stmt_i = $conn->prepare($sql_internos);
    $stmt_i->bind_param("i", $user_id);
}

$stmt_i->execute();
$result_internos = $stmt_i->get_result();

// --- CONSULTA DE TIPOS DE SOLICITUD ---
$sql_solicitudes = "SELECT tipo_solicitud FROM solicitudes ORDER BY tipo_solicitud ASC";
$result_solicitudes = $conn->query($sql_solicitudes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Creador de Escritos - LAWYERS.CL</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root { --brand-blue: #0b3c75; --bg-soft: #f5f7fb; }
        body { background: var(--bg-soft); font-family: -apple-system, system-ui, sans-serif; }
        h1 { text-align: center; margin: 30px 0; font-weight: 700; color: var(--brand-blue); }
        .card { border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.05); }
        .form-label { font-weight: 600; font-size: 0.9rem; color: #444; }
        .btn-generate { background: var(--brand-blue); color: white; border-radius: 10px; padding: 12px; font-weight: 600; width: 100%; transition: 0.3s; border: none; }
        .btn-generate:hover { background: #082d5a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(11, 60, 117, 0.2); }
        
        /* Estilo Select2 Premium */
        .select2-container--default .select2-selection--single {
            height: 50px; border-radius: 10px; padding: 10px; border: 1px solid #dee2e6;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 28px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 48px; }
    </style>
</head>
<body>

<div class="container" style="max-width: 650px;">
    <h1><i class="fas fa-pen-nib me-2"></i> Creador de Escritos</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-4 p-lg-5">
            <form method="POST" action="generar_escrito.php">
                
                <div class="mb-4">
                    <label for="interno" class="form-label">1. Seleccione el Interno / Cliente</label>
                    <select id="interno" name="interno_id" class="form-control select2" required>
                        <option value="">-- Buscar por Nombre o RUT --</option>
                        <?php while ($row = $result_internos->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>">
                                <?= htmlspecialchars($row['apellidos'] . " " . $row['nombres'] . " - " . $row['rut']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="form-text mt-2 small">Solo se muestran casos en estado activo.</div>
                </div>

                <div class="mb-5">
                    <label for="solicitud" class="form-label">2. Tipo de Solicitud Jurídica</label>
                    <select id="solicitud" name="solicitud" class="form-control select2" required>
                        <option value="">-- Seleccione el formato --</option>
                        <?php if ($result_solicitudes && $result_solicitudes->num_rows > 0): ?>
                            <?php while ($sol = $result_solicitudes->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($sol['tipo_solicitud']) ?>">
                                    <?= htmlspecialchars($sol['tipo_solicitud']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="btn-generate shadow-sm">
                    <i class="fas fa-file-word me-2"></i> GENERAR DOCUMENTO WORD
                </button>
            </form>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="index.php?page=listar_internos" class="text-muted text-decoration-none small">
            <i class="fas fa-chevron-left me-1"></i> Volver al listado
        </a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function(){
    $('.select2').select2({
        width: '100%',
        language: { noResults: () => 'No se encontraron registros activos' }
    });

    // Auto-enfocar el primer selector al cargar
    setTimeout(() => { $('#interno').select2('open'); }, 500);

    // Salto automático al segundo campo tras elegir el primero
    $('#interno').on('select2:select', function(){
        $('#solicitud').select2('open');
    });
});
</script>

</body>
</html>
<?php 
$stmt_i->close();
$conn->close(); 
?>
