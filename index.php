<?php
/**
 * LAWYERS.CL - Enterprise Hub Jurídico
 * @version 2.4.0 (SaaS Master Edition)
 * @author Senior Dev Architecture
 */

declare(strict_types=1);
ob_start();
session_start();

/* --- 1. CONFIGURACIÓN CENTRAL Y SEGURIDAD --- */
define('SESSION_TIMEOUT', 3600);
define('DEFAULT_PAGE_LOGGED', 'dashboard');
define('DEFAULT_PAGE_PUBLIC', 'landing_global');

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header("Location: index.php?message=session_timeout");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

/* --- 2. ESTADO DE AUTENTICACIÓN --- */
$auth = [
    'is_logged' => (bool)($_SESSION['loggedin'] ?? false),
    'email'     => (string)($_SESSION['email'] ?? ''),
    'name'      => (string)($_SESSION['username'] ?? 'User'),
    // LLAVE MAESTRA: Tú eres admin si el rol es 'admin' o si es tu correo de la DPP
    'is_admin'  => (($_SESSION['rol'] ?? '') === 'admin' || ($_SESSION['email'] ?? '') === 'gabriel.calderon@dpp.cl')
];

/* --- 3. MOTOR DE RUTEO (PÚBLICO / PRIVADO / MASTER) --- */
$page = preg_replace('/[^a-z0-9_-]/i', '', $_GET['page'] ?? ($auth['is_logged'] ? DEFAULT_PAGE_LOGGED : DEFAULT_PAGE_PUBLIC));

$routes = [
    'public'  => ['landing_global', 'gabriel-calderon-abogado-penal-la-serena', 'acceso'],
    
    'private' => [
        'home', 'dashboard', 'dashboardf', 'kpi', 'clientes', 
        'agregar_internosv2','agregar_internosv3', 'listar_internos', 
        'editar_internos', 'archivo_internos', 'creador_escritos',
        'busca','reels', 'calculadorajuridica', 'ficha2', 'herramienta_defensores', 
        'search', 'ultimas_leyes', 'links_interes'
    ],
    
    // ESTAS PÁGINAS SOLO LAS VES TÚ
    'master'  => [
        'gestionar_usuarios', 'gestionar_escritos', 'agregar_escritos', 
        'limpieza_db', 'estructura', 'listar_leads', 'crear_cuenta'
    ]
];

// Resolución segura de permisos
$allowed = $routes['public']; // Todos ven lo público

if ($auth['is_logged']) {
    $allowed = array_merge($allowed, $routes['private']); // Abogados ven lo privado
    
    if ($auth['is_admin']) {
        $allowed = array_merge($allowed, $routes['master']); // Tú ves TODO
    }
}

// Validación de la vista final
$view = in_array($page, $allowed, true) ? "{$page}.php" : ($auth['is_logged'] ? "dashboard.php" : "landing_global.php");

/* --- 4. AYUDANTES DE VISTA --- */
date_default_timezone_set('America/Santiago');
$now = date("H:i | d-m-Y");

$flash = filter_input(INPUT_GET, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
$messages = [
    'logout' => ['type' => 'success', 'text' => 'Sesión cerrada correctamente.'],
    'session_timeout' => ['type' => 'warning', 'text' => 'La sesión expiró por seguridad.']
];

function renderNavItem(string $targetPage, string $icon, string $label, string $activePage): void {
    $isActive = ($targetPage === $activePage) ? 'active' : '';
    printf(
        '<a href="index.php?page=%s" class="nav-item %s">
            <span class="nav-icon"><i class="fas %s"></i></span>
            <span class="nav-text">%s</span>
        </a>',
        $targetPage, $isActive, $icon, $label
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lawyers.cl | Intelligent Legal Enterprise</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-sidebar: #0f172a; 
            --sidebar-text: #94a3b8;
            --sidebar-text-hover: #ffffff;
            --primary-accent: #4f46e5;
            --primary-light: #e0e7ff;
            --sidebar-width: 260px;
            --header-height: 70px;
            --transition-speed: 0.3s;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: #1e293b;
            overflow-x: hidden;
        }

        .app-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            z-index: 1040;
            display: flex; flex-direction: column;
            transition: transform var(--transition-speed) ease;
            box-shadow: 4px 0 24px rgba(0,0,0,0.05);
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex; align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .logo-text {
            font-size: 1.25rem; font-weight: 800; color: white;
            letter-spacing: -0.5px; text-decoration: none;
        }
        .logo-text span { color: var(--primary-accent); }

        .sidebar-content { flex: 1; overflow-y: auto; padding: 1.5rem 1rem; }
        
        .nav-category {
            font-size: 0.7rem; text-transform: uppercase;
            letter-spacing: 1.5px; font-weight: 700;
            color: #475569; margin: 1.5rem 0 0.5rem 0.5rem;
        }

        .nav-item {
            display: flex; align-items: center;
            padding: 0.75rem 1rem; margin-bottom: 4px;
            color: var(--sidebar-text); text-decoration: none;
            border-radius: 8px; transition: all 0.2s ease;
            font-weight: 500; font-size: 0.9rem;
        }

        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--sidebar-text-hover); }
        .nav-item.active { background: var(--primary-accent); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4); }
        .nav-icon { width: 24px; text-align: center; margin-right: 12px; font-size: 1rem; }

        .app-main { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; }

        .app-header {
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: sticky; top: 0; z-index: 1020;
            padding: 0 2rem;
            display: flex; align-items: center; justify-content: space-between;
        }

        .user-profile {
            display: flex; align-items: center; gap: 12px;
            padding: 6px 12px; border-radius: 50px;
            background: white; border: 1px solid #e2e8f0;
        }

        .content-wrapper { flex: 1; padding: 2rem; animation: fadeIn 0.4s ease-out; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 991.98px) {
            .app-sidebar { transform: translateX(-100%); }
            .app-sidebar.active { transform: translateX(0); }
            .app-main { margin-left: 0; }
        }
        
        .badge-status { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px; }
        .master-tag { background: #fee2e2; color: #ef4444; font-size: 0.65rem; padding: 2px 8px; border-radius: 10px; font-weight: 800; text-transform: uppercase; margin-left: 5px; }
    </style>
</head>
<body>

<aside class="app-sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="index.php" class="logo-text">LAWYERS<span>.CL</span></a>
    </div>

    <div class="sidebar-content">
        <div class="nav-category">Presencia Global</div>
        <?php 
            renderNavItem('landing_global', 'fa-globe', 'Inicio Global', $page);
            renderNavItem('gabriel-calderon-abogado-penal-la-serena', 'fa-user-tie', 'Perfil Pro', $page);
        ?>

        <?php if ($auth['is_logged']): ?>
            <div class="nav-category">Inteligencia</div>
            <?php 
                renderNavItem('dashboard', 'fa-chart-line', 'Panel Principal', $page);
                renderNavItem('dashboardf', 'fa-table-columns', 'Diversidad', $page);
                renderNavItem('kpi', 'fa-arrow-trend-up', 'KPI Analytics', $page);
            ?>

            <div class="nav-category">Gestión de Casos</div>
            <?php 
                renderNavItem('agregar_internosv3', 'fa-bolt', 'Ingreso Turbo', $page);
                renderNavItem('agregar_internosv2', 'fa-user-plus', 'Ingreso Manual', $page);
                renderNavItem('listar_internos', 'fa-list-ul', 'Casos Activos', $page);
renderNavItem('search', 'fa-list-ul', 'Buscar', $page);
renderNavItem('reels', 'fa-list-ul', 'Repaso', $page);               
 renderNavItem('archivo_internos', 'fa-box-archive', 'Archivo Histórico', $page);
            ?>

            <div class="nav-category">Legal Tech</div>
            <?php 
                renderNavItem('creador_escritos', 'fa-wand-magic-sparkles', 'Redacción IA', $page);
            ?>

            <div class="nav-category">Recursos</div>
            <?php 
                renderNavItem('calculadorajuridica', 'fa-calculator', 'Calc. Penales', $page);
                renderNavItem('herramienta_defensores', 'fa-toolbox', 'Toolbox DPP', $page);
                renderNavItem('ultimas_leyes', 'fa-scale-balanced', 'Leyes al Día', $page);
            ?>

            <?php if ($auth['is_admin']): ?>
                <div class="nav-category" style="color: #ef4444;">Master Console</div>
                <?php 
                    renderNavItem('gestionar_usuarios', 'fa-users-gear', 'Control Usuarios', $page); 
                    renderNavItem('gestionar_escritos', 'fa-folder-tree', 'Biblioteca Global', $page);
                    renderNavItem('listar_leads', 'fa-magnet', 'CRM / Leads', $page);
                    renderNavItem('crear_cuenta', 'fa-user-shield', 'Altas Manuales', $page);
                ?>
            <?php endif; ?>

        <?php else: ?>
            <div class="mt-4 px-2 text-center">
                <a href="index.php?page=acceso" class="btn btn-outline-primary btn-sm w-100 rounded-pill">Identificarse</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="p-3 border-top border-white-10">
        <div class="badge-status w-100 justify-content-center">
            <i class="fas fa-circle" style="font-size: 8px;"></i> System Online
        </div>
    </div>
</aside>

<main class="app-main">
    <header class="app-header">
        <div class="d-flex align-items-center">
            <button class="btn btn-light d-lg-none me-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div class="d-none d-md-block text-muted small fw-bold"><i class="far fa-clock me-2"></i> <?= $now ?></div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <?php if ($auth['is_logged']): ?>
                <div class="user-profile">
                    <span class="small fw-bold"><?= $auth['name'] ?></span>
                    <?= $auth['is_admin'] ? '<span class="master-tag">Webmaster</span>' : '' ?>
                </div>
                <a href="logout.php" class="btn btn-white text-danger border shadow-sm"><i class="fas fa-power-off"></i></a>
            <?php else: ?>
                <a href="index.php?page=acceso" class="btn btn-primary btn-sm px-4 rounded-pill shadow-sm fw-bold">Acceso</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="content-wrapper">
        <?php 
            if (file_exists($view)) {
                include $view;
            } else {
                echo '<div class="text-center py-5"><h3>Página no encontrada</h3><p>El archivo ['.$page.'.php] no existe.</p></div>';
            }
        ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>
</body>
</html>
<?php ob_end_flush(); ?>
