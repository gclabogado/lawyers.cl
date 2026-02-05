<?php
// =========================
// GESTIÓN DE INGRESOS - ENTERPRISE SaaS v2.5
// =========================

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }

require_once 'db.php'; // Conexión única

$message = null; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. IDENTIDAD DEL DUEÑO (SaaS)
    $usuario_id_owner = $_SESSION['user_id'];
    
    // 2. PROCESAMIENTO DE VARIABLES (Respetando tu lógica original)
    $nacionalidad = $_POST['nacionalidad'] == 'Extranjera' ? $_POST['nacionalidad_otro'] : $_POST['nacionalidad'];
    $defensor_id = !empty($_POST['defensor_id']) ? $_POST['defensor_id'] : NULL;
    
    // Manejo de fechas para evitar errores de formato en MySQL
    $f_inicio      = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : NULL;
    $f_termino     = !empty($_POST['fecha_termino']) ? $_POST['fecha_termino'] : NULL;
    $f_entrevista  = !empty($_POST['fecha_entrevista']) ? $_POST['fecha_entrevista'] : NULL;
    $f_min_lib     = !empty($_POST['fecha_min_libertad']) ? $_POST['fecha_min_libertad'] : NULL;
    $f_min_perm    = !empty($_POST['fecha_min_permiso']) ? $_POST['fecha_min_permiso'] : NULL;

    // 3. QUERY CON TODOS LOS CAMPOS (35 parámetros incluyendo usuario_id)
    $sql = "INSERT INTO internos (
        usuario_id, nombres, apellidos, rut, sexo, nacionalidad, gentilicio, delito, 
        fecha_inicio, fecha_termino, fecha_entrevista, juzgado, rit, ruc, 
        tiempo_condena, carcel, solicitudes, abogado, estado_procesal, 
        beneficios, observaciones, nivel_riesgo, prioridad, defensor_id, 
        fecha_cumplimiento_rebaja, fecha_min_libertad, fecha_min_permiso,
        contacto1_nombre, contacto1_parentesco, contacto1_telefono, contacto1_email,
        contacto2_nombre, contacto2_parentesco, contacto2_telefono, contacto2_email
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // "isssssssssssssssssssssssisssssssssss" -> 35 tipos
        $stmt->bind_param(
            "issssssssssssssssssssssisssssssssss",
            $usuario_id_owner, // 1
            $_POST['nombres'], $_POST['apellidos'], $_POST['rut'], $_POST['sexo'], 
            $nacionalidad, $_POST['gentilicio'], $_POST['delito'], // 2-8
            $f_inicio, $f_termino, $f_entrevista, $_POST['juzgado'], $_POST['rit'], $_POST['ruc'], // 9-14
            $_POST['tiempo_condena'], $_POST['carcel'], $_POST['solicitudes'], $_POST['abogado'], // 15-18
            $_POST['estado_procesal'], $_POST['beneficios'], $_POST['observaciones'], // 19-21
            $_POST['nivel_riesgo'], $_POST['prioridad'], $defensor_id, // 22-24
            $_POST['fecha_cumplimiento_rebaja'], $f_min_lib, $f_min_perm, // 25-27
            $_POST['contacto1_nombre'], $_POST['contacto1_parentesco'], $_POST['contacto1_telefono'], $_POST['contacto1_email'], // 28-31
            $_POST['contacto2_nombre'], $_POST['contacto2_parentesco'], $_POST['contacto2_telefono'], $_POST['contacto2_email']  // 32-35
        );
        
        if ($stmt->execute()) {
            $message = ['type' => 'success', 'text' => 'Expediente registrado con éxito en tu base de datos privada.'];
        } else {
            $message = ['type' => 'error', 'text' => 'Error al guardar: ' . $stmt->error];
        }
        $stmt->close();
    } else {
        $message = ['type' => 'error', 'text' => 'Error de preparación: ' . $conn->error];
    }
}
?>

<style>
    :root { --primary: #4f46e5; --primary-hover: #4338ca; }
    .form-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; overflow: hidden; }
    .form-header { background: #f8fafc; padding: 2rem; border-bottom: 1px solid #e2e8f0; }
    .section-title { color: var(--primary); font-size: 0.85rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px; }
    .section-title::after { content: ""; flex: 1; height: 1px; background: #e2e8f0; }
    .btn-submit { background: var(--primary); color: white; font-weight: 600; padding: 12px 24px; border-radius: 8px; border: none; width: 100%; transition: 0.2s; }
    .btn-submit:hover { background: var(--primary-hover); transform: translateY(-1px); }
</style>

<div class="container-fluid py-4">

    <?php if ($message): ?>
    <div class="alert alert-<?= $message['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3 mb-4">
        <i class="fas <?= $message['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i>
        <?= $message['text'] ?>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="form-card">
                <div class="form-header">
                    <h3 class="fw-bold text-dark mb-1">Nuevo Ingreso Enterprise</h3>
                    <p class="text-muted small mb-0">Registro completo de ficha técnica para abogados.</p>
                </div>

                <form method="POST" class="p-4 p-lg-5">
                    
                    <div class="section-title"><i class="fas fa-id-card"></i> Identificación</div>
                    <div class="row g-3 mb-5">
                        <div class="col-md-4"><label class="form-label small fw-bold">Nombres *</label><input type="text" name="nombres" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Apellidos *</label><input type="text" name="apellidos" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">RUT *</label><input type="text" name="rut" class="form-control" placeholder="12345678-9" required></div>
                        
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Sexo</label>
                            <select name="sexo" class="form-select" required>
                                <option value="masculino">Masculino</option>
                                <option value="femenino">Femenino</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Nacionalidad</label>
                            <select name="nacionalidad" id="nacSelect" class="form-select" required>
                                <option value="Chilena">Chilena</option>
                                <option value="Extranjera">Extranjera (Especificar)</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-none" id="nacOtroDiv">
                            <label class="form-label small fw-bold">Especifique País</label>
                            <input type="text" name="nacionalidad_otro" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Gentilicio</label>
                            <input type="text" name="gentilicio" class="form-control" placeholder="Ej: Colombiano">
                        </div>
                    </div>

                    <div class="section-title"><i class="fas fa-gavel"></i> Situación Procesal</div>
                    <div class="row g-3 mb-5">
                        <div class="col-md-6"><label class="form-label small fw-bold">Delito Principal *</label><input type="text" name="delito" class="form-control" required></div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="estado_procesal" class="form-select">
                                <option value="condenado">Condenado</option>
                                <option value="imputado">Imputado</option>
                                <option value="procesado">Procesado</option>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label small fw-bold">Juzgado</label><input type="text" name="juzgado" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label small fw-bold">RIT</label><input type="text" name="rit" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label small fw-bold">RUC</label><input type="text" name="ruc" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label small fw-bold">Tiempo Condena</label><input type="text" name="tiempo_condena" class="form-control"></div>
                    </div>

                    <div class="section-title"><i class="fas fa-calendar-alt"></i> Cómputo & Fechas</div>
                    <div class="row g-3 mb-5">
                        <div class="col-md-4"><label class="form-label small fw-bold text-success">Inicio Condena</label><input type="date" name="fecha_inicio" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold text-danger">Término Condena</label><input type="date" name="fecha_termino" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Fecha Entrevista</label><input type="date" name="fecha_entrevista" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Rebaja (2/3 o 1/2)</label><input type="text" name="fecha_cumplimiento_rebaja" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Mínimo Libertad</label><input type="date" name="fecha_min_libertad" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Mínimo Permisos</label><input type="date" name="fecha_min_permiso" class="form-control"></div>
                    </div>

                    <div class="section-title"><i class="fas fa-building-user"></i> Gestión Interna</div>
                    <div class="row g-3 mb-5">
                        <div class="col-md-4"><label class="form-label small fw-bold">Unidad Penal *</label><input type="text" name="carcel" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Abogado Encargado</label><input type="text" name="abogado" class="form-control"></div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Riesgo</label>
                            <select name="nivel_riesgo" class="form-select"><option value="bajo">Bajo</option><option value="medio" selected>Medio</option><option value="alto">Alto</option></select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Prioridad</label>
                            <select name="prioridad" class="form-select"><option value="normal" selected>Normal</option><option value="alta">Alta</option></select>
                        </div>
                        <div class="col-12"><label class="form-label small fw-bold">Beneficios Solicitados</label><textarea name="beneficios" class="form-control" rows="2"></textarea></div>
                        <div class="col-12"><label class="form-label small fw-bold">Observaciones / Historial</label><textarea name="observaciones" class="form-control" rows="3"></textarea></div>
                    </div>

                    <div class="section-title"><i class="fas fa-users-rectangle"></i> Red de Apoyo / Contactos</div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6 border-end">
                            <h6 class="text-muted fw-bold small mb-3">CONTACTO 1</h6>
                            <input type="text" name="contacto1_nombre" class="form-control mb-2" placeholder="Nombre completo">
                            <input type="text" name="contacto1_parentesco" class="form-control mb-2" placeholder="Parentesco">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" name="contacto1_telefono" class="form-control" placeholder="Teléfono"></div>
                                <div class="col-6"><input type="email" name="contacto1_email" class="form-control" placeholder="Email"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted fw-bold small mb-3">CONTACTO 2</h6>
                            <input type="text" name="contacto2_nombre" class="form-control mb-2" placeholder="Nombre completo">
                            <input type="text" name="contacto2_parentesco" class="form-control mb-2" placeholder="Parentesco">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" name="contacto2_telefono" class="form-control" placeholder="Teléfono"></div>
                                <div class="col-6"><input type="email" name="contacto2_email" class="form-control" placeholder="Email"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-top">
                        <button type="submit" class="btn-submit shadow-lg">
                            <i class="fas fa-save me-2"></i> REGISTRAR FICHA EN REPOSITORIO SAAS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('nacSelect').addEventListener('change', function() {
        document.getElementById('nacOtroDiv').classList.toggle('d-none', this.value !== 'Extranjera');
    });
</script>
