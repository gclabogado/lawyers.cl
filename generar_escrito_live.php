<?php
/**
 * GENERADOR LIVE - Procesa datos en memoria sin guardar en DB
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_POST['live_data']) || !isset($_POST['solicitud'])) {
    die("Error: Faltan datos para generar el documento.");
}

// 1. Recibir datos del Frontend
$data = json_decode($_POST['live_data'], true);
$tipo_doc = $_POST['solicitud'];

// 2. Buscar la ruta de la plantilla en tu sistema actual
$conn = new mysqli("localhost", "b", "Jesu1994!!", "penitenciario");
$stmt = $conn->prepare("SELECT archivo FROM solicitudes WHERE tipo_solicitud = ? LIMIT 1");
$stmt->bind_param("s", $tipo_doc);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$ruta_plantilla = $res['archivo'] ?? '';
$conn->close();

if (!$ruta_plantilla || !file_exists($ruta_plantilla)) {
    die("Error: No encuentro la plantilla para '$tipo_doc' en el servidor.");
}

// 3. Procesar el Word (Usando ZipArchive para máxima compatibilidad)
$tempFile = tempnam(sys_get_temp_dir(), 'LiveDoc');
copy($ruta_plantilla, $tempFile);

$zip = new ZipArchive();
if ($zip->open($tempFile) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    
    // Mapeo de variables (Aseguramos que coincidan con tus plantillas)
    // Las claves del array $data vienen de herramienta_defensores.php
    foreach ($data as $key => $val) {
        $xml = str_replace('${' . $key . '}', htmlspecialchars($val ?? '', ENT_XML1, 'UTF-8'), $xml);
    }
    
    // Guardamos cambios
    $zip->addFromString('word/document.xml', $xml);
    $zip->close();

    // 4. Descargar
    $filename = str_replace(' ', '_', $data['apellidos']) . '_' . date('dmY') . '.docx';
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempFile));
    readfile($tempFile);
    unlink($tempFile); // Borramos el temporal
    exit;
} else {
    die("Error: No se pudo abrir el archivo de plantilla temporal.");
}
?>
