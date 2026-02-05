<?php
/**
 * LAWYERS.CL - Data Persistence Layer
 * Procesa y guarda la información de internos v3.
 */
session_start();
declare(strict_types=1);

// 1. Verificación de Seguridad
if (!isset($_SESSION['loggedin']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 2. Configuración de Base de Datos
$db_config = [
    'host' => 'localhost',
    'user' => 'b',
    'pass' => 'Jesu1994!!',
    'name' => 'penitenciario'
];

$conn = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);

if ($conn->connect_error) {
    die("Error crítico de conexión: " . $conn->connect_error);
}

// 3. Sanitización y Captura de Datos
// Usamos el operador de fusión de nulidad (??) para campos opcionales
$nombres      = $_POST['nombres'] ?? '';
$apellidos    = $_POST['apellidos'] ?? '';
$rut          = $_POST['rut'] ?? '';
$sexo         = $_POST['sexo'] ?? 'masculino';
$nacionalidad = $_POST['nacionalidad'] ?? '';
$delito       = $_POST['delito'] ?? '';
$juzgado      = $_POST['juzgado'] ?? 'No especificado';
$rit          = $_POST['rit'] ?? '';
$ruc          = $_POST['ruc'] ?? '';
$carcel       = $_POST['carcel'] ?? '';
$prioridad    = $_POST['prioridad'] ?? 'normal';
$nivel_riesgo = $_POST['nivel_riesgo'] ?? 'medio';
$abogado      = $_POST['abogado'] ?? 'Sin asignar';
$usuario_reg  = $_SESSION['username'] ?? 'sistema';

// Fechas: Convertimos vacíos a NULL para la base de datos
$f_inicio     = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
$f_termino    = !empty($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null;

// Contactos
$c1_nombre    = $_POST['contacto1_nombre'] ?? '';
$c1_parent    = $_POST['contacto1_parentesco'] ?? '';
$c1_fono      = $_POST['contacto1_telefono'] ?? '';

// 4. Preparación de SQL (Prepared Statement)
$sql = "INSERT INTO internos (
            nombres, apellidos, rut, sexo, nacionalidad, delito, 
            juzgado, rit, ruc, carcel, fecha_inicio, fecha_termino, 
            prioridad, nivel_riesgo, abogado, usuario,
            contacto1_nombre, contacto1_parentesco, contacto1_telefono
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // "sssssssssssssssisss" indica que son 19 strings (o valores compatibles)
    $stmt->bind_param(
        "sssssssssssssssssss",
        $nombres, $apellidos, $rut, $sexo, $nacionalidad, $delito,
        $juzgado, $rit, $ruc, $carcel, $f_inicio, $f_termino,
        $prioridad, $nivel_riesgo, $abogado, $usuario_reg,
        $c1_nombre, $c1_parent, $c1_fono
    );

    if ($stmt->execute()) {
        // Éxito: Redirigir al listado con mensaje
        header("Location: index.php?page=listar_internos&message=" . urlencode("✅ Interno $nombres $apellidos registrado con éxito."));
    } else {
        // Error de ejecución
        header("Location: index.php?page=agregar_internosv3&message=" . urlencode("❌ Error SQL: " . $stmt->error));
    }
    $stmt->close();
} else {
    // Error de preparación
    header("Location: index.php?page=agregar_internosv3&message=" . urlencode("❌ Error de preparación: " . $conn->error));
}

$conn->close();
