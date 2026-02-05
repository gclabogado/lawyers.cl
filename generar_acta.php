<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

require 'vendor/autoload.php'; // Asegúrate de que Composer haya instalado correctamente PhpWord

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

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

        // Estilos
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);

        $boldStyle = ['bold' => true];
        $paragraphStyle = [
            'alignment' => Jc::BOTH,
            'spaceAfter' => 0,
            'lineHeight' => 1.0,
        ];
        $centeredStyle = [
            'alignment' => Jc::CENTER,
        ];

        $section = $phpWord->addSection([
            'marginLeft' => 600,
            'marginRight' => 600,
            'marginTop' => 600,
            'marginBottom' => 600,
            'pageSizeW' => 12240,
            'pageSizeH' => 15840,
        ]);

        // Líneas centradas
        $section->addText('Acta de Cierre de Entrevista', array_merge($boldStyle, $centeredStyle));
        $section->addTextBreak(1);
        $section->addText('DEFENSOR (A) PENITENCIARIO:', $centeredStyle);
        $section->addTextBreak(1);

        // Contenido justificado
        $section->addText('ANTECEDENTES PERSONALES DEL SENTENCIADO', $boldStyle, $paragraphStyle);
        $section->addText('Nombres: ' . htmlspecialchars($interno['nombres']), null, $paragraphStyle);
        $section->addText('Apellidos: ' . htmlspecialchars($interno['apellidos']), null, $paragraphStyle);
        $section->addText('RUT: ' . htmlspecialchars($interno['rut']), null, $paragraphStyle);
        $section->addText('Fecha de Entrevista: ____________________________', null, $paragraphStyle);

        $section->addTextBreak(1);
        $section->addText('IDENTIFICACIÓN DEL REQUERIMIENTO', $boldStyle, $paragraphStyle);
        $section->addText('ID: ____________________________', null, $paragraphStyle);
        $section->addText('Tipo de Requerimiento:', null, $paragraphStyle);
        $section->addTextBreak(2); // Espacio en blanco
        $section->addText('Fecha Respuesta: ____________________________', null, $paragraphStyle);
        $section->addText('Tribunal o Autoridad Genchi: ____________________________', null, $paragraphStyle);
        $section->addText('Respuesta Favorable o Desfavorable: ____________________________', null, $paragraphStyle);

        $section->addTextBreak(1);
        $section->addText('INFORMACIÓN PROPORCIONADA:', $boldStyle, $paragraphStyle);
        $section->addTextBreak(3); // Tres líneas en blanco
        
        // Líneas centradas
        $section->addTextBreak(1);
        $section->addText('FIRMA INTERNO                                      FIRMA DEFENSOR', $centeredStyle);
        
        $fileName = "Acta_Cierre_Entrevista_" . $interno['id'] . ".docx";
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

