<?php
/**
 * GENERADOR DE ESCRITOS WORD - SaaS v7.1
 * Corrección de Error Fatal en línea 43
 */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php?page=acceso');
    exit;
}

require 'vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

// Conexión centralizada
require_once 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interno_id'])) {
    try {
        $id = intval($_POST['interno_id']);
        $tipo_solicitud = $_POST['solicitud'];
        $uid = $_SESSION['user_id'];
        $rol = $_SESSION['rol'] ?? 'abogado';

        // 1. OBTENER INFORMACIÓN DEL INTERNO
        $sql_interno = ($rol === 'admin') 
            ? "SELECT * FROM internos WHERE id = ?" 
            : "SELECT * FROM internos WHERE id = ? AND usuario_id = ?";
        
        $stmt = $conn->prepare($sql_interno);
        if (!$stmt) throw new Exception("Error SQL Internos: " . $conn->error);
        
        if ($rol === 'admin') {
            $stmt->bind_param("i", $id);
        } else {
            $stmt->bind_param("ii", $id, $uid);
        }
        $stmt->execute();
        $interno = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$interno) throw new Exception("Expediente no encontrado.");

        // 2. OBTENER PLANTILLA (Aquí es donde fallaba la línea 43)
        // He añadido una verificación para asegurar que la tabla 'solicitudes' existe
        $stmt_sol = $conn->prepare("SELECT archivo FROM solicitudes WHERE tipo_solicitud = ?");
        if (!$stmt_sol) {
            throw new Exception("Error SQL Solicitudes: " . $conn->error . ". Verifique que la tabla 'solicitudes' exista en 'lawyers_saas'");
        }
        
        $stmt_sol->bind_param("s", $tipo_solicitud);
        $stmt_sol->execute();
        $res_sol = $stmt_sol->get_result()->fetch_assoc();
        $stmt_sol->close();

        if (!$res_sol) throw new Exception("No existe la plantilla para: " . $tipo_solicitud);
        
        $templatePath = $res_sol['archivo'];
        if (!file_exists($templatePath)) throw new Exception("El archivo .docx no existe en: " . $templatePath);

        // 3. PROCESAR WORD
        $templateProcessor = new TemplateProcessor($templatePath);

        // Formateadores
        $fmtDate = function($d) { return ($d && $d !== '0000-00-00') ? date('d-m-Y', strtotime($d)) : '___/___/______'; };
        $fmtText = function($t) { return !empty($t) ? strtoupper($t) : '________________'; };

        // Mapeo de campos de la tabla 'internos'
        $templateProcessor->setValue('nombres', $fmtText($interno['nombres']));
        $templateProcessor->setValue('apellidos', $fmtText($interno['apellidos']));
        $templateProcessor->setValue('rut', $fmtText($interno['rut']));
        $templateProcessor->setValue('delito', $fmtText($interno['delito']));
        $templateProcessor->setValue('juzgado', $fmtText($interno['juzgado']));
        $templateProcessor->setValue('unidad_penal', $fmtText($interno['carcel']));
        $templateProcessor->setValue('f_entrevista', $fmtDate($interno['fecha_entrevista']));

        // 4. DESCARGA
        $fileName = "Escrito_" . str_replace(' ', '_', $tipo_solicitud) . "_" . $interno['rut'] . ".docx";
        
        if (ob_get_contents()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        
        $templateProcessor->saveAs('php://output');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "❌ Error: " . $e->getMessage();
        header('Location: index.php?page=creador_escritos');
        exit;
    }
}
