<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

$servername = "localhost";
$username = "b";
$password = "Jesu1994!!";
$dbname = "penitenciario";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM internos_eliminados ORDER BY apellidos, nombres";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internos Eliminados - Workplace Penitenciario</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background-color: #fff;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            word-wrap: break-word;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            white-space: normal;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 10px;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .dataTables_wrapper {
            width: 100%;
            overflow-x: auto;
            margin: 0;
            padding: 0;
        }
        .button-recuperar {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 10px;
        }
        .button-recuperar:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Listado de Internos Eliminados</h1>
        <table id="internosEliminadosTable">
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>RUT</th>
                    <th>Fecha de Entrevista</th>
                    <th>Unidad Penal</th>
                    <th>Abogado</th>
                    <th>Solicitudes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["nombres"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["apellidos"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["rut"]) . "</td>";
                        echo "<td>" . date('d-m-Y', strtotime($row["fecha_entrevista"])) . "</td>";
                        echo "<td>" . htmlspecialchars($row["carcel"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["abogado"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["solicitudes"]) . "</td>";
                        echo "<td>
                                <form method='post' action='recuperar_interno.php' style='display:inline;'>
                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                    <button type='submit' class='button-recuperar'>Recuperar</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No se encontraron registros</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php $conn->close(); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#internosEliminadosTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json"
                },
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100],
                "columnDefs": [
                    {
                        "targets": [3],
                        "render": function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return data.split('-').reverse().join('-');
                            }
                            return data;
                        }
                    }
                ]
            });
        });
    </script>
</body>
</html>
