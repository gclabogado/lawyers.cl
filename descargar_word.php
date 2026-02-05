<?php
/**
 * GENERADOR DE WORD DIRECTO (SIN PLANTILLAS PREVIAS)
 * Convierte texto plano/HTML en un archivo .doc descargable
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = $_POST['content'] ?? '';
    $nombre_archivo = $_POST['filename'] ?? 'Documento.doc';

    // Limpieza básica para evitar nombres corruptos
    $nombre_archivo = preg_replace('/[^a-zA-Z0-9_ -]/', '', $nombre_archivo) . ".doc";

    // Cabeceras para forzar la descarga en Word
    header("Content-type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Estructura HTML básica compatible con Word
    echo "<html>";
    echo "<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
    echo "<style>
            body { font-family: 'Arial', sans-serif; font-size: 12pt; line-height: 1.5; }
            p { margin-bottom: 10px; }
          </style>";
    echo "</head>";
    echo "<body>";
    
    // Convertimos los saltos de línea (\n) en saltos de HTML (<br>)
    echo nl2br($contenido);
    
    echo "</body>";
    echo "</html>";
    exit;
}
?>
