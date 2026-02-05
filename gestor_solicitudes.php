<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: acceso.php");
    exit;
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "!g529523542", "gestion_clientes");

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Dar de baja solicitud antes de cargar los datos si se envía la acción
if (isset($_POST['dar_de_baja']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $sql_baja = "UPDATE solicitudes SET estado_solicitud='dada de baja' WHERE id=$id";
    if ($conn->query($sql_baja) === TRUE) {
        echo "<p>Solicitud ID $id dada de baja exitosamente.</p>";
    } else {
        echo "<p>Error al dar de baja: " . $conn->error . "</p>";
    }
}

// Filtro de estado de solicitud
$filtro_estado = isset($_POST['estado_solicitud']) ? $_POST['estado_solicitud'] : 'activa';

// Condición para el filtro de fechas
$filtro_fecha = "";
if (!empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin'])) {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $filtro_fecha = "AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

// Consulta para obtener las solicitudes según el filtro de estado y rango de fechas (si aplica)
$sql = "SELECT id, nombre_apellido, telefono, estado, descripcion, antecedentes_penales, fecha, estado_solicitud 
        FROM solicitudes WHERE estado_solicitud='$filtro_estado' $filtro_fecha ORDER BY fecha DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Solicitudes</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #0044cc;
            margin-bottom: 20px;
        }

        .filter-form, .table-container {
            max-width: 800px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .filter-form label, .filter-form input, .filter-form select, .filter-form button {
            display: inline-block;
            margin: 10px 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #0044cc;
            color: white;
        }

        .btn {
            padding: 8px 12px;
            color: #fff;
            background-color: #0044cc;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn.dar-de-baja {
            background-color: #cc0000;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<h2>Gestor de Solicitudes de Abogados</h2>

<div class="filter-form">
    <form method="POST">
        <label for="estado_solicitud">Estado de la Solicitud:</label>
        <select id="estado_solicitud" name="estado_solicitud">
            <option value="activa" <?php echo ($filtro_estado === 'activa') ? 'selected' : ''; ?>>Activa</option>
            <option value="dada de baja" <?php echo ($filtro_estado === 'dada de baja') ? 'selected' : ''; ?>>Dada de Baja</option>
        </select>
        
        <label for="fecha_inicio">Fecha inicio:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio">
        
        <label for="fecha_fin">Fecha fin:</label>
        <input type="date" id="fecha_fin" name="fecha_fin">
        
        <button type="submit" class="btn">Filtrar</button>
    </form>
</div>

<div class="table-container">
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre y Apellido</th>
            <th>Teléfono</th>
            <th>Usted es</th>
            <th>Descripción</th>
            <th>Antecedentes</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_apellido']); ?></td>
                    <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($row['estado']); ?></td>
                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($row['antecedentes_penales']); ?></td>
                    <td><?php echo $row['fecha']; ?></td>
                    <td><?php echo $row['estado_solicitud']; ?></td>
                    <td>
                        <?php if ($row['estado_solicitud'] == 'activa'): ?>
                            <form action="gestor_solicitudes.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="dar_de_baja" class="btn dar-de-baja">Dar de Baja</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No hay solicitudes en el rango de fechas y estado seleccionado.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php
// Cerrar conexión
$conn->close();
?>

</body>
</html>
