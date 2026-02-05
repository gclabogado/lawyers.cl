<?php
/**
 * ELIMINAR INTERNO - LAWYERS.CL SaaS v7.1
 * Borrado físico y definitivo.
 */
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || !isset($_GET['id'])) {
    header('Location: index.php?page=listar_internos');
    exit;
}

$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$user_rol = $_SESSION['rol'] ?? 'abogado';

// SEGURIDAD: Solo el dueño o un admin pueden borrar permanentemente
if ($user_rol === 'admin') {
    $sql = "DELETE FROM internos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
} else {
    $sql = "DELETE FROM internos WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
}

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "🗑️ Expediente #$id eliminado permanentemente del sistema.";
    } else {
        $_SESSION['error'] = "⚠️ No se encontró el registro o no tiene permisos.";
    }
} else {
    $_SESSION['error'] = "❌ Error de base de datos.";
}

$stmt->close();
$conn->close();
header('Location: index.php?page=listar_internos');
exit;
