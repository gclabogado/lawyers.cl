<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $defensor = $_POST['defensor'];
    $interno_id = $_POST['interno'];
    $escrito_id = $_POST['escrito'];

    // Obtener información del interno
    $interno_sql = "SELECT * FROM internos WHERE id = ?";
    $stmt = $conn->prepare($interno_sql);
    $stmt->bind_param("i", $interno_id);
    $stmt->execute();
    $interno_result = $stmt->get_result();
    $interno = $interno_result->fetch_assoc();

    // Verificar que se obtuvieron datos del interno
    if (!$interno) {
        die("No se encontraron datos del interno.");
    }

    // Obtener contenido del escrito desde un archivo de plantilla
    $plantillas = [
        1 => 'pena_mixta.docx', // ID 1 corresponde a la plantilla de pena mixta
        // Agrega más plantillas aquí
    ];
    
    if (!array_key_exists($escrito_id, $plantillas)) {
        die("Plantilla no encontrada.");
    }
    
    $plantilla_path = '/var/www/html/plantillas/' . $plantillas[$escrito_id];
    
    // Verificar que la plantilla existe
    if (!file_exists($plantilla_path)) {
        die("La plantilla no existe.");
    }
    
    // Cargar la plantilla
    $templateProcessor = new TemplateProcessor($plantilla_path);

    // Reemplazar marcadores en el contenido del escrito
    $templateProcessor->setValue('defensor', $defensor);
    $templateProcessor->setValue('nombre', $interno['nombres']);
    $templateProcessor->setValue('apellido', $interno['apellidos']);
    $templateProcessor->setValue('rut', $interno['rut']);
    $templateProcessor->setValue('tiempo_condena', $interno['tiempo_condena']);
    $templateProcessor->setValue('juzgado', $interno['juzgado']);
    $templateProcessor->setValue('fecha_inicio', $interno['fecha_inicio']);
    $templateProcessor->setValue('fecha_termino', $interno['fecha_termino']);
    $templateProcessor->setValue('unidad', $interno['carcel']);
    $templateProcessor->setValue('rit', $interno['rit']);

    $fileName = 'Escrito_' . time() . '.docx';

    // Guardar el archivo en memoria y enviarlo al navegador para su descarga
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename=' . $fileName);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    ob_clean(); // Limpia el buffer de salida para evitar contenido corrupto
    $templateProcessor->saveAs('php://output');
    exit;
}
?>
