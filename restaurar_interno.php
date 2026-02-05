<?php
// restaurar_interno.php
session_start();
require_once 'db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = (int)$_GET['id'];
    $uid = $_SESSION['user_id'];

    // Devolvemos el estado a 'condenado' para que reaparezca en el listado activo
    $sql = "UPDATE internos SET estado_procesal = 'condenado' WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $uid);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Caso #$id restaurado al listado activo.";
    } else {
        $_SESSION['error'] = "❌ Error al restaurar el expediente.";
    }
    $stmt->close();
}
header('Location: index.php?page=archivo_internos');
exit;
