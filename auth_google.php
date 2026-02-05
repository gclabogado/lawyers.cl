<?php
/**
 * LAWYERS.CL - Procesador de Autenticación Google (SaaS Edition)
 * Valida el token JWT, gestiona el registro/login y establece la sesión.
 */
session_start();
header('Content-Type: application/json');

// 1. CONEXIÓN CENTRALIZADA (Apunta a lawyers_saas automáticamente)
require_once 'db.php';

// 2. RECIBIR EL TOKEN DESDE EL JAVASCRIPT
$data = json_decode(file_get_contents('php://input'), true);
$id_token = $data['token'] ?? '';

if (empty($id_token)) {
    echo json_encode(['success' => false, 'error' => 'Token no recibido']);
    exit;
}

// 3. VALIDACIÓN DEL TOKEN CON EL ENDPOINT DE GOOGLE
$url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
$response = file_get_contents($url);
$payload = json_decode($response, true);

if (isset($payload['email'])) {
    // Info del usuario proveniente de Google
    $google_id = $payload['sub']; 
    $email     = $payload['email'];
    $nombre    = $payload['name'];
    $foto      = $payload['picture'] ?? '';

    // 4. LÓGICA DE REGISTRO / LOGIN EN lawyers_saas
    // Buscamos si el email ya existe
    $stmt = $conn->prepare("SELECT id, username, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        // EL USUARIO ES NUEVO EN EL SaaS: Lo registramos
        // Por defecto es 'abogado' y le regalamos 10 tokens
        $rol_default = 'abogado';
        $tokens_regalo = 10;
        
        $stmt_ins = $conn->prepare("INSERT INTO usuarios (google_id, email, username, rol, tokens) VALUES (?, ?, ?, ?, ?)");
        $stmt_ins->bind_param("ssssi", $google_id, $email, $nombre, $rol_default, $tokens_regalo);
        $stmt_ins->execute();

        $user_id = $stmt_ins->insert_id;
        $display_name = $nombre;
        $user_rol = $rol_default;
    } else {
        // EL USUARIO YA EXISTE: Extraemos sus datos
        $user = $res->fetch_assoc();
        $user_id = $user['id'];
        $display_name = $user['username'];
        $user_rol = $user['rol'];

        // Actualizamos el google_id por seguridad si es que no lo tenía
        $upd = $conn->prepare("UPDATE usuarios SET google_id = ? WHERE id = ?");
        $upd->bind_param("si", $google_id, $user_id);
        $upd->execute();
    }

    // 5. ESTABLECER VARIABLES DE SESIÓN MAESTRAS
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id']  = $user_id;   // Este ID es la llave de privacidad para los internos
    $_SESSION['username'] = $display_name;
    $_SESSION['rol']      = $user_rol; // 'admin' o 'abogado'
    $_SESSION['email']    = $email;
    $_SESSION['picture']  = $foto;

    echo json_encode(['success' => true]);
} else {
    // Token inválido o expirado
    echo json_encode(['success' => false, 'error' => 'Validación de Google fallida']);
}

$conn->close();
