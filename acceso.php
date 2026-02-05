<?php
session_start();
require_once 'db.php'; // Usamos la conexión centralizada

$error = "";

// LOGIN WEBMASTER & OTROS
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_clasico'])) {
    $user_input = trim($_POST['username']);
    $pass_input = $_POST['password'];

    // Buscamos usuario, contraseña y ROL
    $sql  = "SELECT id, username, password, rol FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($pass_input, $user['password'])) {
            // Guardamos todo en sesión
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['rol']      = $user['rol']; // Importante para saber si es Admin o Abogado
            
            header("Location: index.php?page=kpi");
            exit;
        } else { $error = "Contraseña incorrecta."; }
    } else { $error = "Usuario no encontrado."; }
}
?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<style>
    /* ... Tus estilos ... */
    :root { --indigo: #6366f1; --slate-900: #0f172a; }
    body { background: #f8fafc; font-family: 'Inter', sans-serif; }
    .login-card { max-width: 450px; margin: 80px auto; padding: 3rem; background: white; border-radius: 28px; border: 1px solid #e2e8f0; box-shadow: 0 20px 50px rgba(0,0,0,0.05); text-align: center; }
    .admin-toggle { margin-top: 2rem; font-size: 0.75rem; color: #94a3b8; cursor: pointer; text-decoration: underline; }
    #adminForm { display: none; margin-top: 20px; text-align: left; }
    .form-input { width: 100%; padding: 10px 15px; margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 8px; }
    .btn-admin { width: 100%; background: var(--slate-900); color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; }
</style>

<div class="login-card">
    <div class="mb-4">
        <i class="fas fa-balance-scale fa-3x" style="color: var(--indigo)"></i>
        <h2 style="font-weight: 800; margin-top:15px;">Lawyers.cl</h2>
        <p style="color: #64748b;">Suite de Gestión Profesional</p>
    </div>

    <div id="g_id_onload" data-client_id="61795350321-gast69snmbhfooipiv5svf547oo7gujj.apps.googleusercontent.com" data-callback="handleSignInResponse"></div>
    <div style="display: flex; justify-content: center; margin-bottom: 2rem;">
        <div class="g_id_signin" data-type="standard" data-shape="pill" data-size="large"></div>
    </div>

    <hr>
    <div class="admin-toggle" onclick="document.getElementById('adminForm').style.display='block'">Acceso Administrativo</div>
    <div id="adminForm">
        <form method="POST">
            <input type="hidden" name="login_clasico" value="1">
            <input type="text" name="username" class="form-input" placeholder="Usuario Admin" required>
            <input type="password" name="password" class="form-input" placeholder="Contraseña" required>
            <button type="submit" class="btn-admin">Entrar como Master</button>
        </form>
        <?php if($error): ?> <p class="text-danger small mt-2"><?= $error ?></p> <?php endif; ?>
    </div>
</div>

<script>
function handleSignInResponse(response) {
    fetch('auth_google.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: response.credential })
    })
    .then(res => res.json())
    .then(data => { if(data.success) window.location.href = 'index.php?page=kpi'; });
}
</script>
