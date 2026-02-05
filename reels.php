<?php
// =======================================================
// VISOR REELS / MODO FOCUS v2.0
// =======================================================

// Configuración inicial
define('ALLOWED_ACTIONS', ['get_ids', 'load_case', 'save_obs']);

// Verificar sesión antes de cualquier operación
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin'])) {
    if (isset($_GET['action'])) {
        http_response_code(401);
        exit(json_encode(['error' => 'No autorizado']));
    }
    header('Location: index.php?page=acceso');
    exit;
}

// Manejo de API
if (isset($_GET['action']) && in_array($_GET['action'], ALLOWED_ACTIONS)) {
    handleAPI();
    exit;
}

// Si no es API, mostrar la vista
showView();

// =======================================================
// FUNCIONES
// =======================================================

function handleAPI() {
    // Limpiar buffer de salida
    while (ob_get_level()) ob_end_clean();
    
    // Configurar cabeceras JSON
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Conectar a la base de datos
    try {
        require_once 'db.php';
    } catch (Exception $e) {
        http_response_code(500);
        exit(json_encode(['error' => 'Error de conexión a la base de datos']));
    }
    
    // Verificar que el usuario está autenticado
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
        http_response_code(401);
        exit(json_encode(['error' => 'Sesión inválida']));
    }
    
    $uid = $_SESSION['user_id'];
    $rol = $_SESSION['rol'];
    
    // Procesar acción solicitada
    switch ($_GET['action']) {
        case 'get_ids':
            getCaseIds($conn, $uid, $rol);
            break;
            
        case 'load_case':
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                http_response_code(400);
                exit(json_encode(['error' => 'ID inválido']));
            }
            loadCase($conn, $uid, $rol, intval($_GET['id']));
            break;
            
        case 'save_obs':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                exit(json_encode(['error' => 'Método no permitido']));
            }
            saveObservation($conn, $uid, $rol);
            break;
    }
    
    $conn->close();
}

function getCaseIds($conn, $uid, $rol) {
    if ($rol === 'admin') {
        $sql = "SELECT id FROM internos 
                WHERE (estado_procesal != 'archivado' OR estado_procesal IS NULL) 
                ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT id FROM internos 
                WHERE usuario_id = ? 
                AND (estado_procesal != 'archivado' OR estado_procesal IS NULL) 
                ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $uid);
    }
    
    if (!$stmt->execute()) {
        http_response_code(500);
        exit(json_encode(['error' => 'Error al obtener casos']));
    }
    
    $result = $stmt->get_result();
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = (int)$row['id'];
    }
    
    echo json_encode(['ids' => $ids, 'count' => count($ids)]);
    $stmt->close();
}

function loadCase($conn, $uid, $rol, $id) {
    // Verificar permisos y obtener datos
    if ($rol === 'admin') {
        $sql = "SELECT * FROM internos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
    } else {
        $sql = "SELECT * FROM internos WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $uid);
    }
    
    if (!$stmt->execute()) {
        http_response_code(500);
        exit(json_encode(['error' => 'Error al cargar caso']));
    }
    
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if (!$data) {
        http_response_code(404);
        exit(json_encode(['error' => 'Caso no encontrado o sin permisos']));
    }
    
    // Formatear datos para la vista
    $formatted = [
        'id' => (int)$data['id'],
        'nombres' => htmlspecialchars($data['nombres'] ?? ''),
        'apellidos' => htmlspecialchars($data['apellidos'] ?? ''),
        'rut' => htmlspecialchars($data['rut'] ?? ''),
        'delito' => htmlspecialchars($data['delito'] ?? 'Sin registro'),
        'carcel' => htmlspecialchars($data['carcel'] ?? 'Sin unidad'),
        'tiempo_condena' => htmlspecialchars($data['tiempo_condena'] ?? 'S/I'),
        'observaciones' => htmlspecialchars($data['observaciones'] ?? '')
    ];
    
    // Formatear fechas
    if (!empty($data['fecha_termino'])) {
        $formatted['fecha_termino'] = date("d/m/Y", strtotime($data['fecha_termino']));
    } else {
        $formatted['fecha_termino'] = 'S/I';
    }
    
    if (!empty($data['fecha_actualizacion'])) {
        $formatted['fecha_formatted'] = date("d/m/Y H:i", strtotime($data['fecha_actualizacion']));
    } else {
        $formatted['fecha_formatted'] = 'Sin ediciones recientes';
    }
    
    echo json_encode($formatted);
    $stmt->close();
}

function saveObservation($conn, $uid, $rol) {
    // Validar datos de entrada
    if (!isset($_POST['id']) || !isset($_POST['observacion'])) {
        http_response_code(400);
        exit(json_encode(['error' => 'Datos incompletos']));
    }
    
    $id = intval($_POST['id']);
    $observacion = trim($_POST['observacion']);
    
    // Verificar que el usuario tiene permisos sobre este caso
    if ($rol !== 'admin') {
        $checkSql = "SELECT id FROM internos WHERE id = ? AND usuario_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $id, $uid);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            http_response_code(403);
            exit(json_encode(['error' => 'No tienes permisos para modificar este caso']));
        }
        $checkStmt->close();
    }
    
    // Actualizar observación
    $now = date("Y-m-d H:i:s");
    $sql = "UPDATE internos SET observaciones = ?, fecha_actualizacion = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Escapar caracteres especiales pero mantener formato
    $observacionEscaped = $conn->real_escape_string($observacion);
    $stmt->bind_param("ssi", $observacionEscaped, $now, $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'ok', 
            'date' => date("d/m/Y H:i", strtotime($now)),
            'id' => $id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'msg' => 'Error al guardar']);
    }
    
    $stmt->close();
}

function showView() {
    // Cargar CSS y JS desde archivos externos si es posible
    // Para este ejemplo, los mantenemos embebidos
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modo Reels - Sistema Penitenciario</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --border-dark: #334155;
            --text-light: #e2e8f0;
            --text-muted: #94a3b8;
            --primary: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        
        body {
            background-color: var(--bg-dark);
            font-family: 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
        }
        
        .reel-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .reel-header {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-dark);
            z-index: 1000;
        }
        
        .reel-stage {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            position: relative;
        }
        
        .case-card {
            width: 100%;
            max-width: 480px;
            background: var(--card-dark);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-dark);
            overflow: hidden;
            height: 85vh;
            max-height: 850px;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        .case-card.loading {
            opacity: 0.7;
        }
        
        .card-top {
            background: linear-gradient(135deg, var(--primary), #3b82f6);
            padding: 2rem 1.5rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .card-top::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.1;
        }
        
        .avatar-char {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            border: 3px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }
        
        .avatar-char:hover {
            transform: scale(1.05);
        }
        
        .scroll-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: var(--border-dark) transparent;
        }
        
        .scroll-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .scroll-content::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .scroll-content::-webkit-scrollbar-thumb {
            background-color: var(--border-dark);
            border-radius: 3px;
        }
        
        .data-pill {
            background: var(--bg-dark);
            border: 1px solid var(--border-dark);
            padding: 0.75rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .data-pill:hover {
            border-color: var(--primary);
            transform: translateY(-1px);
        }
        
        .lbl {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .val {
            font-size: 0.95rem;
            color: var(--text-light);
            font-weight: 500;
            margin-top: 0.25rem;
            word-break: break-word;
        }
        
        .editor-box {
            background: var(--bg-dark);
            border: 1px solid var(--border-dark);
            color: var(--text-light);
            width: 100%;
            border-radius: 12px;
            padding: 1rem;
            resize: none;
            font-size: 0.9rem;
            line-height: 1.5;
            transition: all 0.2s ease;
        }
        
        .editor-box:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .nav-arrow {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .nav-arrow:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
        
        .nav-arrow:disabled {
            opacity: 0.3;
            cursor: not-allowed;
            transform: none;
        }
        
        .action-footer {
            padding: 1rem 1.5rem;
            background: var(--card-dark);
            border-top: 1px solid var(--border-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .save-badge {
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }
        
        .save-badge.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .counter-badge {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .progress-tracker {
            height: 3px;
            background: var(--border-dark);
            margin: 0.5rem 0;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: var(--primary);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .fade-anim {
            animation: fadeIn 0.4s ease;
        }
        
        .pulse-anim {
            animation: pulse 2s infinite;
        }
        
        @media (max-width: 768px) {
            .reel-stage {
                padding: 0.5rem;
            }
            
            .case-card {
                height: 90vh;
                max-height: none;
            }
            
            .nav-arrow {
                position: fixed;
                bottom: 1rem;
                z-index: 100;
            }
            
            .nav-arrow.prev {
                left: 1rem;
            }
            
            .nav-arrow.next {
                right: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="reel-wrapper">
        <header class="reel-header sticky-top">
            <div class="container-fluid py-2">
                <div class="row align-items-center">
                    <div class="col-4">
                        <a href="index.php?page=panel_principal" class="btn btn-sm btn-outline-light d-inline-flex align-items-center">
                            <i class="fas fa-arrow-left me-2"></i> Volver
                        </a>
                    </div>
                    <div class="col-4 text-center">
                        <div class="d-flex flex-column align-items-center">
                            <span class="badge bg-primary px-3 py-2 mb-1">
                                <i class="fas fa-play-circle me-2"></i> MODO REELS
                            </span>
                            <div id="counter" class="counter-badge">
                                <i class="fas fa-sync fa-spin me-1"></i> Cargando...
                            </div>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <button id="btnHelp" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="progress-tracker mt-2">
                    <div id="progressBar" class="progress-bar"></div>
                </div>
            </div>
        </header>

        <main class="reel-stage">
            <button id="btnPrev" class="nav-arrow prev" onclick="prevCase()" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="case-card fade-anim" id="cardContainer">
                <!-- Card Top -->
                <div class="card-top">
                    <div class="avatar-char" id="c_init">--</div>
                    <h5 class="mb-2 fw-bold text-truncate px-2" id="c_name">Cargando caso...</h5>
                    <div class="badge bg-light text-dark px-3 py-2 shadow-sm" id="c_rut">
                        <i class="fas fa-id-card me-1"></i> <span>---</span>
                    </div>
                </div>

                <!-- Scrollable Content -->
                <div class="scroll-content">
                    <!-- Caso Info Grid -->
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="data-pill">
                                <span class="lbl">
                                    <i class="fas fa-gavel text-warning"></i> DELITO
                                </span>
                                <div class="val" id="c_delito">---</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="data-pill">
                                <span class="lbl">
                                    <i class="fas fa-building text-info"></i> UNIDAD
                                </span>
                                <div class="val" id="c_carcel">---</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="data-pill">
                                <span class="lbl">
                                    <i class="fas fa-clock"></i> CONDENA
                                </span>
                                <div class="val" id="c_condena">---</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="data-pill">
                                <span class="lbl">
                                    <i class="fas fa-flag-checkered text-danger"></i> TÉRMINO
                                </span>
                                <div class="val text-warning" id="c_termino">---</div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="lbl text-primary">
                                <i class="fas fa-book me-1"></i> BITÁCORA / OBSERVACIONES
                            </span>
                            <span id="saveBadge" class="badge bg-success save-badge px-3 py-2">
                                <i class="fas fa-check me-1"></i> Guardado
                            </span>
                        </div>
                        <textarea id="c_obs" class="editor-box" rows="8" 
                                  placeholder="Escribe notas rápidas del caso... Presiona Ctrl+Enter para guardar."></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">
                                <i class="fas fa-history me-1"></i> Última modificación
                            </small>
                            <small class="text-muted fw-bold" id="c_updated">...</small>
                        </div>
                        <div class="mt-2 text-end">
                            <small class="text-muted">
                                <span id="charCount">0</span> caracteres
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="action-footer">
                    <button class="btn btn-outline-light btn-sm rounded-pill d-md-none" onclick="prevCase()">
                        <i class="fas fa-chevron-left me-1"></i> Anterior
                    </button>
                    <button id="btnSave" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm w-100 mx-md-4" onclick="saveData()">
                        <i class="fas fa-save me-2"></i> GUARDAR CAMBIOS
                    </button>
                    <button class="btn btn-outline-light btn-sm rounded-pill d-md-none" onclick="nextCase()">
                        Siguiente <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>

            <button id="btnNext" class="nav-arrow next" onclick="nextCase()" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        </main>

        <!-- Toast para mensajes -->
        <div id="toast" class="toast position-fixed top-0 end-0 m-3" role="alert">
            <div class="toast-body bg-dark text-white border-start border-3 border-primary">
                <span id="toastMessage"></span>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let playlist = [];
        let currentIndex = 0;
        let currentId = 0;
        let autoSaveTimer = null;
        let hasUnsavedChanges = false;
        const BASE_URL = 'index.php?page=reels';

        $(document).ready(function() {
            // Cargar playlist inicial
            loadPlaylist();
            
            // Configurar event listeners
            $('#c_obs').on('input', function() {
                updateCharCount();
                hasUnsavedChanges = true;
            });
            
            // Atajos de teclado
            $(document).keydown(function(e) {
                // Ctrl+Enter para guardar
                if (e.ctrlKey && e.keyCode === 13) {
                    e.preventDefault();
                    saveData();
                }
                // Flechas para navegar
                if (e.keyCode === 37) prevCase(); // Left arrow
                if (e.keyCode === 39) nextCase(); // Right arrow
            });
            
            // Ayuda
            $('#btnHelp').click(function() {
                showToast('Usa las flechas ← → para navegar. Ctrl+Enter para guardar rápido.', 'info');
            });
            
            // Prevenir pérdida de datos no guardados
            window.addEventListener('beforeunload', function(e) {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'Tienes cambios sin guardar. ¿Seguro que quieres salir?';
                }
            });
        });

        function loadPlaylist() {
            showLoading(true);
            $.ajax({
                url: BASE_URL + '&action=get_ids',
                method: 'GET',
                dataType: 'json',
                timeout: 10000
            })
            .done(function(response) {
                if (response.ids && response.ids.length > 0) {
                    playlist = response.ids;
                    updateCounter(0);
                    updateProgressBar(0);
                    loadCase(0);
                    updateNavButtons();
                } else {
                    showEmptyState();
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                showError('Error al cargar los casos: ' + textStatus);
            })
            .always(function() {
                showLoading(false);
            });
        }

        function loadCase(index) {
            if (index < 0 || index >= playlist.length) return;
            
            // Verificar cambios no guardados
            if (hasUnsavedChanges && !confirm('Tienes cambios sin guardar. ¿Cargar siguiente caso?')) {
                return;
            }
            
            currentIndex = index;
            currentId = playlist[index];
            
            // Actualizar UI
            $('#cardContainer').addClass('loading');
            updateCounter(index);
            updateProgressBar(index);
            updateNavButtons();
            
            $.ajax({
                url: BASE_URL + '&action=load_case',
                method: 'GET',
                data: { id: currentId },
                dataType: 'json',
                timeout: 5000
            })
            .done(function(data) {
                if (data.error) {
                    showError('Error: ' + data.error);
                    return;
                }
                
                // Actualizar datos
                $('#c_name').text(data.nombres + ' ' + data.apellidos);
                $('#c_rut span').text(data.rut);
                $('#c_init').text((data.nombres[0] || '') + (data.apellidos[0] || ''));
                $('#c_delito').text(data.delito);
                $('#c_carcel').text(data.carcel);
                $('#c_condena').text(data.tiempo_condena);
                $('#c_termino').text(data.fecha_termino);
                $('#c_obs').val(data.observaciones);
                $('#c_updated').text(data.fecha_formatted);
                
                // Resetear estado de cambios
                hasUnsavedChanges = false;
                updateCharCount();
                
                // Efecto visual
                $('#cardContainer').removeClass('loading');
                $('#cardContainer').css('animation', 'none');
                setTimeout(() => {
                    $('#cardContainer').css('animation', 'fadeIn 0.4s ease');
                }, 10);
            })
            .fail(function() {
                showError('Error al cargar el caso');
            })
            .always(function() {
                $('#cardContainer').removeClass('loading');
            });
        }

        function nextCase() {
            if (currentIndex < playlist.length - 1) {
                loadCase(currentIndex + 1);
            } else {
                showToast('¡Has llegado al final de la lista!', 'warning');
            }
        }

        function prevCase() {
            if (currentIndex > 0) {
                loadCase(currentIndex - 1);
            }
        }

        function saveData() {
            const obs = $('#c_obs').val().trim();
            const btn = $('#btnSave');
            
            // Validar
            if (obs.length > 5000) {
                showToast('Las observaciones exceden el límite de 5000 caracteres', 'error');
                return;
            }
            
            btn.prop('disabled', true)
               .html('<i class="fas fa-circle-notch fa-spin me-2"></i> Guardando...');
            
            $.ajax({
                url: BASE_URL + '&action=save_obs',
                method: 'POST',
                data: {
                    id: currentId,
                    observacion: obs
                },
                dataType: 'json',
                timeout: 5000
            })
            .done(function(response) {
                if (response.status === 'ok') {
                    // Actualizar fecha
                    $('#c_updated').text(response.date);
                    hasUnsavedChanges = false;
                    
                    // Mostrar feedback
                    showSaveSuccess();
                    
                    // Auto-avanzar si está habilitado
                    const autoAdvance = localStorage.getItem('reels_auto_advance') === 'true';
                    if (autoAdvance && currentIndex < playlist.length - 1) {
                        setTimeout(() => loadCase(currentIndex + 1), 1000);
                    }
                } else {
                    showToast('Error al guardar: ' + (response.msg || 'Desconocido'), 'error');
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                showToast('Error de conexión al guardar', 'error');
            })
            .always(function() {
                btn.prop('disabled', false)
                   .html('<i class="fas fa-save me-2"></i> GUARDAR CAMBIOS');
            });
        }

        function updateCounter(index) {
            const total = playlist.length;
            $('#counter').html(`
                <i class="fas fa-user me-1"></i> Caso ${index + 1} de ${total}
            `);
        }

        function updateProgressBar(index) {
            const progress = ((index + 1) / playlist.length) * 100;
            $('#progressBar').css('width', progress + '%');
        }

        function updateNavButtons() {
            $('#btnPrev').prop('disabled', currentIndex === 0);
            $('#btnNext').prop('disabled', currentIndex === playlist.length - 1);
        }

        function updateCharCount() {
            const count = $('#c_obs').val().length;
            $('#charCount').text(count);
            
            // Cambiar color según cantidad
            if (count > 4000) {
                $('#charCount').addClass('text-danger').removeClass('text-warning');
            } else if (count > 3000) {
                $('#charCount').addClass('text-warning').removeClass('text-danger');
            } else {
                $('#charCount').removeClass('text-danger text-warning');
            }
        }

        function showSaveSuccess() {
            const badge = $('#saveBadge');
            badge.addClass('show');
            setTimeout(() => badge.removeClass('show'), 2000);
            
            showToast('Observaciones guardadas correctamente', 'success');
        }

        function showLoading(show) {
            if (show) {
                $('#cardContainer').addClass('loading');
            } else {
                $('#cardContainer').removeClass('loading');
            }
        }

        function showEmptyState() {
            $('#cardContainer').html(`
                <div class="d-flex flex-column justify-content-center align-items-center p-5 text-center" style="height: 100%;">
                    <div class="mb-4">
                        <i class="fas fa-inbox fa-4x text-muted"></i>
                    </div>
                    <h4 class="mb-3">No hay casos activos</h4>
                    <p class="text-muted mb-4">No se encontraron casos para revisar en este momento.</p>
                    <a href="index.php?page=panel_principal" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i> Volver al panel
                    </a>
                </div>
            `);
            $('#counter').text('0/0');
            updateNavButtons();
        }

        function showError(message) {
            showToast(message, 'error');
            console.error('Error Reels:', message);
        }

        function showToast(message, type = 'info') {
            const toastEl = $('#toast');
            const toastMsg = $('#toastMessage');
            
            // Configurar color según tipo
            let borderColor = 'border-primary';
            if (type === 'error') borderColor = 'border-danger';
            if (type === 'success') borderColor = 'border-success';
            if (type === 'warning') borderColor = 'border-warning';
            
            toastEl.find('.toast-body')
                   .removeClass('border-primary border-danger border-success border-warning')
                   .addClass(borderColor);
            
            toastMsg.text(message);
            
            // Mostrar toast
            const toast = new bootstrap.Toast(toastEl[0]);
            toast.show();
        }
    </script>
</body>
</html>
    <?php
}
?>
