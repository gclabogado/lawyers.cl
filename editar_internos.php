<?php
// =========================
// EDITOR MAESTRO DE INTERNOS v2.0
// =========================

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }

$servername = "localhost";
$username = "b";
$password = "Jesu1994!!";
$dbname = "penitenciario";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 1. OBTENER LISTA PARA SELECTOR
$internos = [];
$sql = "SELECT id, nombres, apellidos, rut FROM internos ORDER BY apellidos, nombres";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) { $internos[] = $row; }
}

// 2. PROCESAR ACTUALIZACIÓN
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    
    // Saneamiento básico
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $rut = trim($_POST['rut']);
    // ... resto de variables directas del POST ...
    
    // Variables opcionales o con lógica
    $nacionalidad = $_POST['nacionalidad'] == 'otro' ? $_POST['nacionalidad_otro'] : $_POST['nacionalidad'];
    $defensor_id = !empty($_POST['defensor_id']) ? $_POST['defensor_id'] : NULL;
    $fecha_min_libertad = !empty($_POST['fecha_min_libertad']) ? $_POST['fecha_min_libertad'] : NULL;
    $fecha_min_permiso = !empty($_POST['fecha_min_permiso']) ? $_POST['fecha_min_permiso'] : NULL;

    $sql = "UPDATE internos SET 
        nombres=?, apellidos=?, rut=?, sexo=?, nacionalidad=?, gentilicio=?, delito=?, 
        fecha_inicio=?, fecha_termino=?, fecha_entrevista=?, juzgado=?, rit=?, ruc=?, 
        tiempo_condena=?, carcel=?, abogado=?, estado_procesal=?, beneficios=?, observaciones=?, 
        nivel_riesgo=?, prioridad=?, defensor_id=?, fecha_cumplimiento_rebaja=?, 
        fecha_min_libertad=?, fecha_min_permiso=?, contacto1_nombre=?, contacto1_parentesco=?, 
        contacto1_telefono=?, contacto1_email=?, contacto2_nombre=?, contacto2_parentesco=?, 
        contacto2_telefono=?, contacto2_email=? 
        WHERE id=?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            "sssssssssssssssssssssisssssssssssi",
            $nombres, $apellidos, $rut, $_POST['sexo'], $nacionalidad, $_POST['gentilicio'], $_POST['delito'],
            $_POST['fecha_inicio'], $_POST['fecha_termino'], $_POST['fecha_entrevista'], $_POST['juzgado'], $_POST['rit'], $_POST['ruc'],
            $_POST['tiempo_condena'], $_POST['carcel'], $_POST['abogado'], $_POST['estado_procesal'], $_POST['beneficios'], $_POST['observaciones'],
            $_POST['nivel_riesgo'], $_POST['prioridad'], $defensor_id, $_POST['fecha_cumplimiento_rebaja'],
            $fecha_min_libertad, $fecha_min_permiso, $_POST['contacto1_nombre'], $_POST['contacto1_parentesco'],
            $_POST['contacto1_telefono'], $_POST['contacto1_email'], $_POST['contacto2_nombre'], $_POST['contacto2_parentesco'],
            $_POST['contacto2_telefono'], $_POST['contacto2_email'], $id
        );
        
        if ($stmt->execute()) {
            $message = ['type' => 'success', 'text' => 'Registro actualizado exitosamente.'];
        } else {
            $message = ['type' => 'error', 'text' => 'Error SQL: ' . $stmt->error];
        }
        $stmt->close();
    } else {
        $message = ['type' => 'error', 'text' => 'Error Prepare: ' . $conn->error];
    }
}

// 3. CARGAR DATOS DEL INTERNO SELECCIONADO
$selected_interno = null;
$selected_id = $_GET['id'] ?? ($_POST['id'] ?? null);

if ($selected_id) {
    $stmt = $conn->prepare("SELECT * FROM internos WHERE id = ?");
    $stmt->bind_param("i", $selected_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_interno = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    :root {
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --bg-panel: #ffffff;
        --border-color: #e2e8f0;
    }

    /* Contenedor Principal */
    .edit-panel {
        background: var(--bg-panel);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .panel-header {
        background: #f8fafc;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    /* Buscador Select2 Personalizado */
    .select2-container--default .select2-selection--single {
        height: 48px;
        border: 2px solid #cbd5e1;
        border-radius: 12px;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 16px;
        color: #334155;
        font-weight: 500;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
        right: 12px;
    }

    /* Secciones del Formulario */
    .form-section-title {
        color: var(--primary);
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .form-section-title::after {
        content: ""; flex: 1; height: 1px; background: #e2e8f0;
    }

    /* Inputs Modernos */
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 0.65rem 1rem;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    textarea.form-control { resize: vertical; min-height: 100px; }

    /* Contador Caracteres */
    .char-count { font-size: 0.75rem; color: #94a3b8; text-align: right; margin-top: 4px; }

    /* Botones */
    .btn-save {
        background: var(--primary);
        color: white;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        border: none;
        transition: all 0.2s;
    }
    .btn-save:hover { background: var(--primary-hover); transform: translateY(-1px); }
</style>

<div class="container-fluid py-4">

    <div class="row justify-content-center mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <label class="form-label mb-2"><i class="fas fa-search me-2"></i>Buscar Expediente a Editar</label>
                <select id="internoSelect" class="form-control">
                    <option value="">Escriba nombre o RUT...</option>
                    <?php foreach ($internos as $i): ?>
                        <option value="<?= $i['id'] ?>" <?= ($selected_id == $i['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i['apellidos'] . ", " . $i['nombres'] . " (" . $i['rut'] . ")") ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <div class="alert alert-<?= $message['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3 d-flex align-items-center">
                <i class="fas <?= $message['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> fa-lg me-3"></i>
                <div><?= $message['text'] ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($selected_interno): ?>
    <form method="POST" id="editForm" class="animate-in">
        <input type="hidden" name="id" value="<?= $selected_interno['id'] ?>">
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="edit-panel">
                    <div class="panel-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Editando: <?= htmlspecialchars($selected_interno['nombres'] . ' ' . $selected_interno['apellidos']) ?></h4>
                            <span class="badge bg-light text-secondary border">ID: #<?= $selected_interno['id'] ?></span>
                            <span class="badge bg-light text-secondary border ms-2"><?= htmlspecialchars($selected_interno['carcel']) ?></span>
                        </div>
                        <div>
                            <a href="index.php?page=listar_internos" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                <i class="fas fa-arrow-left me-2"></i> Volver
                            </a>
                        </div>
                    </div>

                    <div class="p-4 p-lg-5">
                        
                        <div class="form-section-title"><i class="fas fa-user-circle text-primary"></i> Identidad</div>
                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" class="form-control" required value="<?= htmlspecialchars($selected_interno['nombres']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" class="form-control" required value="<?= htmlspecialchars($selected_interno['apellidos']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RUT <span class="text-danger">*</span></label>
                                <input type="text" name="rut" class="form-control" required value="<?= htmlspecialchars($selected_interno['rut']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sexo</label>
                                <select name="sexo" class="form-select">
                                    <option value="masculino" <?= $selected_interno['sexo']=='masculino'?'selected':'' ?>>Masculino</option>
                                    <option value="femenino" <?= $selected_interno['sexo']=='femenino'?'selected':'' ?>>Femenino</option>
                                    <option value="otro" <?= $selected_interno['sexo']=='otro'?'selected':'' ?>>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nacionalidad</label>
                                <select name="nacionalidad" id="nacionalidadSelect" class="form-select">
                                    <option value="Chilena" <?= $selected_interno['nacionalidad']=='Chilena'?'selected':'' ?>>Chilena</option>
                                    <option value="Extranjera" <?= $selected_interno['nacionalidad']!='Chilena'?'selected':'' ?>>Extranjera</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="otroNacionalidad" style="display:none;">
                                <label class="form-label">Especifique País</label>
                                <input type="text" name="nacionalidad_otro" class="form-control" value="<?= htmlspecialchars($selected_interno['nacionalidad']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gentilicio</label>
                                <input type="text" name="gentilicio" class="form-control" value="<?= htmlspecialchars($selected_interno['gentilicio']) ?>">
                            </div>
                        </div>

                        <div class="form-section-title"><i class="fas fa-gavel text-primary"></i> Procesal Penal</div>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label">Delito Principal <span class="text-danger">*</span></label>
                                <input type="text" name="delito" class="form-control" required value="<?= htmlspecialchars($selected_interno['delito']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="estado_procesal" class="form-select">
                                    <option value="condenado" <?= $selected_interno['estado_procesal']=='condenado'?'selected':'' ?>>Condenado</option>
                                    <option value="imputado" <?= $selected_interno['estado_procesal']=='imputado'?'selected':'' ?>>Imputado</option>
                                    <option value="procesado" <?= $selected_interno['estado_procesal']=='procesado'?'selected':'' ?>>Procesado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Juzgado</label>
                                <input type="text" name="juzgado" class="form-control" value="<?= htmlspecialchars($selected_interno['juzgado']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">RIT</label>
                                <input type="text" name="rit" class="form-control" value="<?= htmlspecialchars($selected_interno['rit']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">RUC</label>
                                <input type="text" name="ruc" class="form-control" value="<?= htmlspecialchars($selected_interno['ruc']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tiempo Condena</label>
                                <input type="text" name="tiempo_condena" class="form-control" placeholder="Ej: 5 años y 1 día" value="<?= htmlspecialchars($selected_interno['tiempo_condena']) ?>">
                            </div>
                        </div>

                        <div class="form-section-title"><i class="fas fa-calendar-alt text-primary"></i> Plazos y Cómputos</div>
                        <div class="row g-4 mb-5">
                            <div class="col-md-3">
                                <label class="form-label text-success">Inicio Condena</label>
                                <input type="date" name="fecha_inicio" class="form-control border-success" value="<?= $selected_interno['fecha_inicio'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-danger">Término Condena</label>
                                <input type="date" name="fecha_termino" class="form-control border-danger" value="<?= $selected_interno['fecha_termino'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Última Entrevista</label>
                                <input type="date" name="fecha_entrevista" class="form-control" value="<?= $selected_interno['fecha_entrevista'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cumplimiento Rebaja</label>
                                <input type="text" name="fecha_cumplimiento_rebaja" class="form-control" value="<?= htmlspecialchars($selected_interno['fecha_cumplimiento_rebaja']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mínimo para Libertad</label>
                                <input type="date" name="fecha_min_libertad" class="form-control" value="<?= $selected_interno['fecha_min_libertad'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mínimo para Permisos</label>
                                <input type="date" name="fecha_min_permiso" class="form-control" value="<?= $selected_interno['fecha_min_permiso'] ?>">
                            </div>
                        </div>

                        <div class="form-section-title"><i class="fas fa-tasks text-primary"></i> Gestión Interna</div>
                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <label class="form-label">Unidad Penal</label>
                                <input type="text" name="carcel" class="form-control" required value="<?= htmlspecialchars($selected_interno['carcel']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Abogado Asignado</label>
                                <input type="text" name="abogado" class="form-control" value="<?= htmlspecialchars($selected_interno['abogado']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Riesgo</label>
                                <select name="nivel_riesgo" class="form-select">
                                    <option value="bajo" <?= $selected_interno['nivel_riesgo']=='bajo'?'selected':'' ?>>Bajo</option>
                                    <option value="medio" <?= $selected_interno['nivel_riesgo']=='medio'?'selected':'' ?>>Medio</option>
                                    <option value="alto" <?= $selected_interno['nivel_riesgo']=='alto'?'selected':'' ?>>Alto</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Prioridad</label>
                                <select name="prioridad" class="form-select">
                                    <option value="normal" <?= $selected_interno['prioridad']=='normal'?'selected':'' ?>>Normal</option>
                                    <option value="alta" <?= $selected_interno['prioridad']=='alta'?'selected':'' ?>>Alta</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Beneficios Obtenidos</label>
                                <textarea name="beneficios" class="form-control" rows="2" maxlength="1000"><?= htmlspecialchars($selected_interno['beneficios']) ?></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Observaciones Técnicas</label>
                                <textarea name="observaciones" class="form-control" rows="4" maxlength="1000"><?= htmlspecialchars($selected_interno['observaciones']) ?></textarea>
                                <div class="char-count">Máx 1000 caracteres</div>
                            </div>
                        </div>

                        <div class="form-section-title"><i class="fas fa-address-book text-primary"></i> Red de Apoyo</div>
                        <div class="row g-4">
                            <div class="col-md-6 border-end">
                                <h6 class="fw-bold text-muted mb-3 small">CONTACTO PRIMARIO</h6>
                                <div class="mb-3">
                                    <input type="text" name="contacto1_nombre" class="form-control form-control-sm mb-2" placeholder="Nombre Completo" value="<?= htmlspecialchars($selected_interno['contacto1_nombre']) ?>">
                                    <input type="text" name="contacto1_parentesco" class="form-control form-control-sm mb-2" placeholder="Parentesco (Ej: Madre)" value="<?= htmlspecialchars($selected_interno['contacto1_parentesco']) ?>">
                                    <input type="text" name="contacto1_telefono" class="form-control form-control-sm" placeholder="Teléfono" value="<?= htmlspecialchars($selected_interno['contacto1_telefono']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted mb-3 small">CONTACTO SECUNDARIO</h6>
                                <div class="mb-3">
                                    <input type="text" name="contacto2_nombre" class="form-control form-control-sm mb-2" placeholder="Nombre Completo" value="<?= htmlspecialchars($selected_interno['contacto2_nombre']) ?>">
                                    <input type="text" name="contacto2_parentesco" class="form-control form-control-sm mb-2" placeholder="Parentesco" value="<?= htmlspecialchars($selected_interno['contacto2_parentesco']) ?>">
                                    <input type="text" name="contacto2_telefono" class="form-control form-control-sm mb-2" placeholder="Teléfono" value="<?= htmlspecialchars($selected_interno['contacto2_telefono']) ?>">
                                    <input type="email" name="contacto2_email" class="form-control form-control-sm" placeholder="Email (Opcional)" value="<?= htmlspecialchars($selected_interno['contacto2_email']) ?>">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="bg-light p-4 border-top d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-link text-danger text-decoration-none btn-sm" onclick="if(confirm('¿Descartar cambios?')) location.reload();">
                            Cancelar Cambios
                        </button>
                        <button type="submit" class="btn-save shadow-sm">
                            <i class="fas fa-save me-2"></i> Guardar Expediente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>

</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2 con estilo
        $('#internoSelect').select2({
            placeholder: "Buscar por nombre, apellido o RUT...",
            allowClear: true,
            width: '100%'
        });

        // Redirección al seleccionar
        $('#internoSelect').on('change', function() {
            let id = $(this).val();
            if(id) window.location.href = 'index.php?page=editar_internos&id=' + id;
        });

        // Lógica Nacionalidad
        const nacSelect = document.getElementById('nacionalidadSelect');
        const otroDiv = document.getElementById('otroNacionalidad');
        
        if(nacSelect) {
            nacSelect.addEventListener('change', function() {
                otroDiv.style.display = (this.value === 'Extranjera') ? 'block' : 'none';
            });
            // Trigger inicial
            if(nacSelect.value === 'Extranjera') otroDiv.style.display = 'block';
        }
    });
</script>
