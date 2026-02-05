<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

require 'vendor/autoload.php'; // Asegúrate de que Composer haya instalado correctamente PhpWord

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$servername = "127.0.0.1";
$username = "b";
$password = "Jesu1994!!";
$dbname = "penitenciario";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM internos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $interno = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($interno) {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        $section->addTitle('Resumen del Interno', 1);

        $section->addText("ID: " . $interno['id']);
        $section->addText("Nombres: " . htmlspecialchars($interno['nombres']));
        $section->addText("Apellidos: " . htmlspecialchars($interno['apellidos']));
        $section->addText("RUT: " . htmlspecialchars($interno['rut']));
        $section->addText("Sexo: " . htmlspecialchars($interno['sexo']));
        $section->addText("Nacionalidad: " . htmlspecialchars($interno['nacionalidad']));
        $section->addText("Delito: " . htmlspecialchars($interno['delito']));
        $section->addText("Fecha de Inicio: " . htmlspecialchars($interno['fecha_inicio']));
        $section->addText("Fecha de Término: " . htmlspecialchars($interno['fecha_termino']));
        $section->addText("Fecha de Registro: " . htmlspecialchars($interno['fecha_registro']));
        $section->addText("Fecha de Entrevista: " . htmlspecialchars($interno['fecha_entrevista']));
        $section->addText("Juzgado: " . htmlspecialchars($interno['juzgado']));
        $section->addText("RIT: " . htmlspecialchars($interno['rit']));
        $section->addText("Tiempo de Condena: " . htmlspecialchars($interno['tiempo_condena']));
        $section->addText("Unidad Penal: " . htmlspecialchars($interno['carcel']));
        $section->addText("Abogado: " . htmlspecialchars($interno['abogado']));
        $section->addText("Solicitudes: " . htmlspecialchars($interno['solicitudes']));

        $fileName = "Resumen_Interno_" . $interno['id'] . ".docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tempFile));
        readfile($tempFile);
        unlink($tempFile);
        exit;
    } else {
        echo "No se encontró el interno.";
    }
} else {
    echo "ID no especificado.";
}
?>

