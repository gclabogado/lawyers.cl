<?php
// code_viewer.php - Visor y editor de código fuente (Solo usuarios logeados)
session_start();

/* --- 1) Control de inactividad --- */
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 2500)) {
    session_unset();
    session_destroy();
    header("Location: index.php?message=session_timeout");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

/* --- 2) Verificar login --- */
$loggedin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$userEmail = $_SESSION['email'] ?? '';
$username = $_SESSION['username'] ?? '';

/* --- 3) Redirigir si no está logeado --- */
if (!$loggedin) {
    header("Location: index.php?page=acceso&message=Necesitas iniciar sesión para acceder al editor de código");
    exit;
}

/* --- 4) Configuración del editor --- */
$titulo = "Visor de Código PHP - Sistema Penitenciario";
$directorio = __DIR__;
$archivos_php = glob("*.php");
sort($archivos_php);

// Obtener el archivo seleccionado
$archivo_actual = $_GET['file'] ?? '';
$contenido = '';
$es_editable = false;
$mensaje = '';

if ($archivo_actual && file_exists($archivo_actual)) {
    // LEER EL CONTENIDO SIN htmlspecialchars - SOLO PARA MOSTRAR EN TEXTAREA
    $contenido = file_get_contents($archivo_actual);
    $es_editable = is_writable($archivo_actual);
}

// Guardar cambios si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar']) && $archivo_actual && $es_editable) {
    $nuevo_contenido = $_POST['contenido'];
    
    // Verificar que el contenido no esté vacío
    if (!empty(trim($nuevo_contenido))) {
        if (file_put_contents($archivo_actual, $nuevo_contenido) !== false) {
            $mensaje = "✅ Archivo guardado correctamente";
            $contenido = $nuevo_contenido; // Usar el nuevo contenido
            
            // Redirigir para evitar reenvío del formulario
            header("Location: code_viewer.php?file=" . urlencode($archivo_actual) . "&saved=1");
            exit;
        } else {
            $mensaje = "❌ Error al guardar el archivo";
        }
    } else {
        $mensaje = "⚠️ El archivo no puede estar vacío";
    }
}

// Mostrar mensaje de éxito después de redirección
if (isset($_GET['saved']) && $_GET['saved'] == 1) {
    $mensaje = "✅ Archivo guardado correctamente";
}

// Fecha y hora local
date_default_timezone_set('America/Santiago');
$currentDateTime = date("H:i, d-m-Y");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0b3c75;
            --primary-dark: #082a54;
            --primary-light: #1e5bac;
            --secondary: #ffcc00;
            --accent: #e63946;
            --success: #2a9d8f;
            --warning: #e9c46a;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 12px;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #1e1e1e;
            color: #d4d4d4;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header estilo Lawyers.cl */
        .editor-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-lg);
        }

        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .header-left .subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }

        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: 600;
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: #c1121f;
            transform: translateY(-2px);
        }

        .container {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 320px;
            background: #252526;
            border-right: 1px solid #3e3e42;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 1.5rem;
            background: #2d2d30;
            border-bottom: 1px solid #3e3e42;
        }

        .stats {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .stat {
            text-align: center;
            flex: 1;
        }

        .stat-value {
            font-weight: 700;
            color: var(--secondary);
            font-size: 1.2rem;
        }

        .search-box {
            padding: 1rem;
            background: #2d2d30;
            border-bottom: 1px solid #3e3e42;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem;
            background: #3c3c3c;
            border: 1px solid #464647;
            color: #d4d4d4;
            border-radius: 25px;
            font-size: 0.9rem;
        }

        .file-list {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .file-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-radius: 8px;
            margin: 0.25rem 0;
            transition: var(--transition);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .file-item:hover {
            background: #2a2d2e;
            transform: translateX(5px);
        }

        .file-item.active {
            background: #094771;
            border-left: 4px solid var(--secondary);
        }

        .file-name {
            color: #569cd6;
            font-weight: 500;
        }

        .file-size {
            color: #888;
            font-size: 0.8rem;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .editor-toolbar {
            background: #2d2d30;
            padding: 1rem 2rem;
            border-bottom: 1px solid #3e3e42;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success);
        }

        .btn-success:hover {
            background: #238f7a;
        }

        .btn-warning {
            background: var(--warning);
            color: var(--dark);
        }

        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .file-info {
            margin-left: auto;
            color: #888;
            font-size: 0.9rem;
        }

        .editor-container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        textarea {
            flex: 1;
            background: #1e1e1e;
            color: #d4d4d4;
            border: none;
            padding: 2rem;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            resize: none;
            outline: none;
            tab-size: 4;
            white-space: pre;
            overflow-wrap: normal;
            overflow-x: auto;
        }

        .mensaje {
            padding: 1rem;
            margin: 1rem 2rem;
            border-radius: var(--border-radius);
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mensaje.success {
            background: var(--success);
        }

        .mensaje.error {
            background: var(--danger);
        }

        .mensaje.warning {
            background: var(--warning);
            color: var(--dark);
        }

        .empty-state {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            text-align: center;
        }

        .empty-state h2 {
            margin-bottom: 1rem;
            color: #569cd6;
        }

        /* Modal de confirmación */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }

        .modal-content {
            background-color: #2d2d30;
            margin: 15% auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            width: 400px;
            text-align: center;
            border: 2px solid var(--primary);
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: 300px;
            }
            .editor-toolbar {
                flex-wrap: wrap;
            }
            .modal-content {
                width: 90%;
                margin: 20% auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header con información de usuario -->
    <div class="editor-header">
        <div class="header-left">
            <h1>👨‍💻 Editor de Código PHP</h1>
            <div class="subtitle">Lawyers.cl - Sistema de Gestión Penitenciaria</div>
        </div>
        <div class="user-info">
            <div class="avatar">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
            <div>👤 <?= htmlspecialchars($username) ?></div>
            <div class="datetime">
                <i class="fas fa-clock"></i> <?= htmlspecialchars($currentDateTime) ?>
            </div>
            <a href="index.php?page=home" class="logout-btn">
                <i class="fas fa-arrow-left"></i> Volver al Sistema
            </a>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h3>💾 Confirmar Guardado</h3>
            <p>¿Estás seguro de que quieres guardar los cambios en <strong id="fileName"><?php echo $archivo_actual; ?></strong>?</p>
            <div class="modal-buttons">
                <button class="btn btn-success" onclick="confirmSave()">
                    <i class="fas fa-check"></i> Sí, Guardar
                </button>
                <button class="btn btn-danger" onclick="closeModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Sidebar con lista de archivos -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>📁 Archivos PHP</h2>
                <small><?php echo count($archivos_php); ?> archivos encontrados</small>
                <div class="stats">
                    <div class="stat">
                        <div class="stat-value"><?= htmlspecialchars($username) ?></div>
                        <div>Usuario</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?= count($archivos_php) ?></div>
                        <div>Archivos</div>
                    </div>
                </div>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchFiles" placeholder="🔍 Buscar archivo..." onkeyup="buscarArchivos()">
            </div>
            
            <div class="file-list" id="fileList">
                <?php foreach($archivos_php as $archivo): 
                    $clase = $archivo == $archivo_actual ? 'file-item active' : 'file-item';
                    $tamano_kb = round(filesize($archivo)/1024, 1);
                ?>
                <div class="<?php echo $clase; ?>" onclick="cargarArchivo('<?php echo $archivo; ?>')">
                    <span class="file-name">📄 <?php echo $archivo; ?></span>
                    <span class="file-size"><?php echo $tamano_kb; ?> KB</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Área principal del editor -->
        <div class="main-content">
            <?php if(!empty($mensaje)): ?>
            <div class="mensaje <?php 
                if (strpos($mensaje, '✅') !== false) echo 'success';
                elseif (strpos($mensaje, '❌') !== false) echo 'error';
                else echo 'warning';
            ?>">
                <i class="fas fa-info-circle"></i> <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>
            
            <?php if($archivo_actual): ?>
            <div class="editor-toolbar">
                <button type="submit" form="editorForm" class="btn btn-success" id="saveButton" <?php echo !$es_editable ? 'disabled' : ''; ?>>
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button type="button" class="btn" onclick="copiarCodigo()">
                    <i class="fas fa-copy"></i> Copiar código
                </button>
                <button type="button" class="btn" onclick="abrirEnNuevaVentana()">
                    <i class="fas fa-external-link-alt"></i> Abrir en nueva pestaña
                </button>
                <div class="file-info">
                    <?php if($es_editable): ?>
                    <span style="color: #4caf50;">✅ Modo edición activado</span>
                    <?php else: ?>
                    <span style="color: #f44336;">❌ Solo lectura - Verifica permisos del archivo</span>
                    <?php endif; ?>
                    | Modificado: <?php echo date('d/m/Y H:i', filemtime($archivo_actual)); ?>
                </div>
            </div>
            
            <form method="post" id="editorForm" class="editor-container">
                <textarea name="contenido" id="codigoEditor" spellcheck="false" 
                          placeholder="Selecciona un archivo para editar su código..." 
                          <?php echo !$es_editable ? 'readonly' : ''; ?>><?php echo htmlspecialchars($contenido); ?></textarea>
                <input type="hidden" name="guardar" value="1">
                <input type="hidden" name="archivo" value="<?php echo $archivo_actual; ?>">
            </form>
            <?php else: ?>
            <div class="empty-state">
                <div>
                    <h2>👆 Selecciona un archivo para editar</h2>
                    <p>Elige un archivo PHP de la lista lateral para ver y editar su código fuente.</p>
                    <p><small>Usuario autenticado: <strong><?= htmlspecialchars($username) ?></strong></small></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function cargarArchivo(archivo) {
        window.location.href = '?file=' + encodeURIComponent(archivo);
    }
    
    function mostrarModal() {
        document.getElementById('fileName').textContent = '<?php echo $archivo_actual; ?>';
        document.getElementById('confirmModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }
    
    function confirmSave() {
        document.getElementById('editorForm').submit();
    }
    
    // Interceptar el envío del formulario
    document.getElementById('editorForm')?.addEventListener('submit', function(e) {
        if (!confirm('¿Estás seguro de que quieres guardar los cambios en <?php echo $archivo_actual; ?>?')) {
            e.preventDefault();
        }
    });
    
    function copiarCodigo() {
        const textarea = document.getElementById('codigoEditor');
        textarea.select();
        document.execCommand('copy');
        
        // Mostrar notificación temporal
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
        btn.style.background = '#2a9d8f';
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
        }, 2000);
    }
    
    function abrirEnNuevaVentana() {
        const archivo = '<?php echo $archivo_actual; ?>';
        window.open(archivo, '_blank');
    }
    
    function buscarArchivos() {
        const input = document.getElementById('searchFiles');
        const filter = input.value.toLowerCase();
        const items = document.querySelectorAll('.file-item');
        
        items.forEach(item => {
            const fileName = item.querySelector('.file-name').textContent.toLowerCase();
            if (fileName.includes(filter)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Auto-tamaño del textarea
    document.addEventListener('DOMContentLoaded', function() {
        const editor = document.getElementById('codigoEditor');
        if (editor) {
            editor.style.height = 'auto';
            editor.style.height = (editor.scrollHeight) + 'px';
            
            editor.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }
        
        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('confirmModal');
            if (e.target === modal) {
                closeModal();
            }
        });

        // Verificar si el textarea es editable
        if (editor && editor.readOnly) {
            console.log('El archivo es de solo lectura. Verifica los permisos del archivo.');
        }
    });
    </script>
</body>
</html>
