<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php?page=acceso");
    exit;
}

include('db.php');
$mensaje = "";

// Eliminar normativa
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $stmt = $conn->prepare("SELECT archivo FROM normativa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($archivo);
    $stmt->fetch();
    $stmt->close();

    if ($archivo && file_exists("uploads/$archivo")) {
        unlink("uploads/$archivo");
    }

    $conn->query("DELETE FROM normativa WHERE id = $id");
    $mensaje = "📛 Documento eliminado correctamente.";
}

// Obtener listado
$result = $conn->query("SELECT id, titulo, resumen, fecha_documento, archivo, subido_por FROM normativa ORDER BY fecha_documento DESC");
?>

<h2 style="color:#2b3a67;">📚 Gestión de Normativas / Fallos Jurídicos</h2>

<?php if ($mensaje): ?>
    <div class="message"><?php echo $mensaje; ?></div>
<?php endif; ?>

<table class="table-docs">
    <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Resumen</th>
            <th>Fecha</th>
            <th>Archivo</th>
            <th>Subido por</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
            <td><?php echo substr(htmlspecialchars($row['resumen']), 0, 60) . '...'; ?></td>
            <td><?php echo $row['fecha_documento']; ?></td>
            <td><a href="uploads/<?php echo $row['archivo']; ?>" target="_blank">📄 Ver PDF</a></td>
            <td><?php echo $row['subido_por']; ?></td>
            <td>
                <a href="editar_normativa.php?id=<?php echo $row['id']; ?>">✏️ Editar</a> |
                <a href="index.php?page=gestionar_normativa&eliminar=<?php echo $row['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este documento?')">🗑️ Eliminar</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
