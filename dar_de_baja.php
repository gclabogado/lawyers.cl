<?php
/**
 * ARCHIVAR INTERNO - LAWYERS.CL SaaS v7.1
 * Cambia el estado a 'archivado' (Caja).
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

// Solo el dueño o el admin pueden archivar
if ($user_rol === 'admin') {
    $sql = "UPDATE internos SET estado_procesal = 'archivado' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
} else {
    $sql = "UPDATE internos SET estado_procesal = 'archivado' WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['message'] = "📦 Caso #$id enviado a la Bóveda Histórica.";
} else {
    $_SESSION['error'] = "❌ Error al archivar el caso.";
}

$stmt->close();
$conn->close();
header('Location: index.php?page=listar_internos');
exit;
