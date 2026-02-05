<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

$servername = "localhost";
$username = "b";
$password = "Jesu1994!!";
$dbname = "gestion_penal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM internos WHERE origen = 'rapido' ORDER BY apellidos, nombres";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Internos - Ingreso Rápido</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Revisar Internos - Ingreso Rápido</h1>
    <table id="internosTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>RUN</th>
                <th>Apellidos</th>
                <th>Nombres</th>
                <th>Fecha de Nacimiento</th>
                <th>Edad</th>
                <th>Género</th>
                <th>Nacionalidad</th>
                <th>Fecha de Ingreso</th>
                <th>Unidad</th>
                <th>Puntaje</th>
                <th>Sector Unidad</th>
                <th>Compromiso Delictual</th>
                <th>Celda</th>
                <th>Apodo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["run"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["apellidos"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nombres"]) . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($row["fecha_nacimiento"])) . "</td>";
                    echo "<td>" . htmlspecialchars($row["edad"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["genero"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nacionalidad"]) . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($row["fecha_ingreso"])) . "</td>";
                    echo "<td>" . htmlspecialchars($row["unidad"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["puntaje"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["sector_unidad"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["compromiso_delictual"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["celda"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["apodo"]) . "</td>";
                    echo "<td><a href='transferir_interno.php?id=" . $row['id'] . "'>Transferir</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='16'>No se encontraron registros</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php $conn->close(); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#internosTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json"
                }
            });
        });
    </script>
</body>
</html>
