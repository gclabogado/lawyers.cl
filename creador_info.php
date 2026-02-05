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
    die("Error: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 Manual del Sistema de Escritos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .header { background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); color: white; padding: 40px 0; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.1); margin-bottom: 25px; }
        .card-header { background: #34495e; color: white; font-weight: 600; font-size: 1.2rem; }
        .step-number { background: #3498db; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; }
        .step { display: flex; align-items: center; margin-bottom: 20px; padding: 15px; background: white; border-radius: 10px; }
        .code-block { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 8px; font-family: 'Courier New', monospace; margin: 10px 0; }
        .variable { background: #e74c3c; color: white; padding: 2px 8px; border-radius: 4px; font-family: 'Courier New', monospace; }
        .section-title { color: #2c3e50; border-left: 4px solid #3498db; padding-left: 15px; margin: 40px 0 25px 0; font-weight: 600; }
        .feature-box { text-align: center; padding: 20px; border: 2px dashed #bdc3c7; border-radius: 10px; margin: 10px; }
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <h1>📚 Manual del Sistema de Escritos</h1>
        <p class="lead">Guía completa de uso, configuración y creación de plantillas</p>
    </div>
</div>

<div class="container mt-4">

    <!-- Sección 1: Cómo Funciona el Sistema -->
    <div class="card">
        <div class="card-header">
            🔄 ¿CÓMO FUNCIONA EL CREADOR DE ESCRITOS?
        </div>
        <div class="card-body">
            
            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <h5>Selección de Interno y Solicitud</h5>
                    <p class="mb-0">El usuario selecciona un interno de la base de datos y el tipo de documento a generar.</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <h5>Procesamiento con PhpWord</h5>
                    <p class="mb-0">El sistema toma la plantilla Word (.docx) y reemplaza automáticamente las variables con los datos del interno.</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <div>
                    <h5>Generación y Descarga</h5>
                    <p class="mb-0">Se crea un documento Word personalizado que se descarga inmediatamente al computador del usuario.</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">4</div>
                <div>
                    <h5>Almacenamiento</h5>
                    <p class="mb-0">Los documentos <strong>NO se guardan en el servidor</strong>. Cada usuario guarda localmente lo que necesita.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Sección 2: Cómo Ingresar Nuevos Modelos -->
    <div class="card">
        <div class="card-header">
            📥 ¿CÓMO AGREGAR NUEVAS PLANTILLAS?
        </div>
        <div class="card-body">
            
            <h5>Paso 1: Crear la Plantilla Word</h5>
            <ul>
                <li>Crea un documento Word normal</li>
                <li>Usa variables entre <span class="variable">${}</span> donde quieras datos automáticos</li>
                <li>Guarda como <strong>.docx</strong> en la carpeta <code>solicitudes/</code></li>
            </ul>

            <div class="code-block">
// Ejemplo de plantilla Word:<br>
<br>
SOLICITUD JUDICIAL<br>
<br>
Señor Juez:<br>
<br>
${nombres} ${apellidos}, RUT ${rut},<br>
internado en ${unidad_penal},<br>
solicita ${tipo_solicitud}.<br>
<br>
Fecha: ${fecha_actual}
            </div>

            <h5>Paso 2: Registrar en Base de Datos</h5>
            <div class="code-block">
INSERT INTO solicitudes (tipo_solicitud, archivo) <br>
VALUES ('Mi Nueva Solicitud', 'solicitudes/mi_plantilla.docx');
            </div>

            <h5>Paso 3: ¡Listo!</h5>
            <p>La nueva plantilla aparecerá automáticamente en el creador de escritos.</p>

        </div>
    </div>

    <!-- Sección 3: Variables Disponibles -->
    <div class="card">
        <div class="card-header">
            🔤 VARIABLES DISPONIBLES
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col-md-6">
                    <h6>👤 Datos Personales:</h6>
                    <ul>
                        <li><span class="variable">${nombres}</span> - Nombres completos</li>
                        <li><span class="variable">${apellidos}</span> - Apellidos completos</li>
                        <li><span class="variable">${rut}</span> - RUT del interno</li>
                        <li><span class="variable">${sexo}</span> - Género (masculino/femenino/otros)</li>
                        <li><span class="variable">${nacionalidad}</span> - Nacionalidad</li>
                        <li><span class="variable">${gentilicio}</span> - Gentilicio</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>⚖️ Datos Penitenciarios:</h6>
                    <ul>
                        <li><span class="variable">${unidad_penal}</span> - Cárcel/Establecimiento</li>
                        <li><span class="variable">${delito}</span> - Delito cometido</li>
                        <li><span class="variable">${fecha_inicio}</span> - Inicio de condena</li>
                        <li><span class="variable">${fecha_termino}</span> - Término de condena</li>
                        <li><span class="variable">${juzgado}</span> - Juzgado sentenciador</li>
                        <li><span class="variable">${rit}</span> - RIT del caso</li>
                        <li><span class="variable">${tiempo_condena}</span> - Tiempo de condena</li>
                    </ul>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>👥 Datos de Contacto:</h6>
                    <ul>
                        <li><span class="variable">${contacto1_nombre}</span> - Nombre contacto 1</li>
                        <li><span class="variable">${contacto1_parentesco}</span> - Parentesco</li>
                        <li><span class="variable">${contacto1_telefono}</span> - Teléfono</li>
                        <li><span class="variable">${contacto2_nombre}</span> - Nombre contacto 2</li>
                        <li><span class="variable">${contacto2_telefono}</span> - Teléfono</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>📅 Variables Automáticas:</h6>
                    <ul>
                        <li><span class="variable">${fecha_actual}</span> - Fecha de hoy</li>
                        <li><span class="variable">${abogado}</span> - Abogado asignado</li>
                        <li><span class="variable">${estado_procesal}</span> - Estado del caso</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <!-- Sección 4: Dónde se Alojan los Archivos -->
    <div class="card">
        <div class="card-header">
            📁 ESTRUCTURA DE ARCHIVOS
        </div>
        <div class="card-body">
            
            <h5>🗂️ Directorio Principal: <code>/var/www/html/</code></h5>
            
            <div class="code-block">
/var/www/html/<br>
├── 📄 creador_escritos.php          # Interfaz principal<br>
├── 🔧 generar_escrito.php           # Procesador de plantillas<br>
├── 📚 creador_info.php              # Este manual<br>
├── 📂 solicitudes/                  # Plantillas de abogados<br>
│   ├── Solicpenamixta.docx<br>
│   ├── acta de cierre pro.docx<br>
│   └── ... (21 plantillas)<br>
├── 📂 solicitudes_ts/               # Plantillas TS (futuro)<br>
└── 📂 vendor/                       # Librerías PhpWord
            </div>

            <h5>💾 Base de Datos: <code>penitenciario</code></h5>
            <ul>
                <li><strong>Tabla <code>solicitudes</code></strong> - Registro de plantillas disponibles</li>
                <li><strong>Tabla <code>internos</code></strong> - Datos de todos los reclusos</li>
                <li><strong>Tabla <code>escritos</code></strong> - Histórico (poco usado)</li>
            </ul>

        </div>
    </div>

    <!-- Sección 5: Crear Tus Propios Modelos -->
    <div class="card">
        <div class="card-header">
            🎨 CREAR TUS PROPIAS PLANTILLAS
        </div>
        <div class="card-body">
            
            <h5>Guía Paso a Paso:</h5>

            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <h5>Diseña en Word</h5>
                    <p class="mb-0">Crea el documento como siempre lo haces, pero donde quieras datos automáticos, usa <span class="variable">${variable}</span></p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <h5>Guarda en Carpeta Correcta</h5>
                    <p class="mb-0">Guarda el .docx en <code>solicitudes/</code> para abogados o <code>solicitudes_ts/</code> para trabajadoras sociales</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <div>
                    <h5>Registra en Base de Datos</h5>
                    <p class="mb-0">Ejecuta este comando SQL para que aparezca en el sistema:</p>
                    <div class="code-block">
-- Para abogados:<br>
INSERT INTO solicitudes (tipo_solicitud, archivo) <br>
VALUES ('Nombre de tu plantilla', 'solicitudes/tu_archivo.docx');<br>
<br>
-- Para trabajadoras sociales:<br>
INSERT INTO solicitudes_ts (tipo_solicitud, archivo, descripcion) <br>
VALUES ('Informe Social', 'solicitudes_ts/informe_social.docx', 'Descripción opcional');
                    </div>
                </div>
            </div>

            <div class="step">
                <div class="step-number">4</div>
                <div>
                    <h5>¡Probar!</h5>
                    <p class="mb-0">Ve al creador de escritos y verifica que tu plantilla aparece en la lista</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Sección 6: Ejemplo Práctico -->
    <div class="card">
        <div class="card-header">
            🧪 EJEMPLO PRÁCTICO COMPLETO
        </div>
        <div class="card-body">
            
            <h5>Creando una plantilla de "Solicitud de Traslado":</h5>

            <div class="code-block">
// 1. Contenido del documento Word (guardar como solicitudes/traslado.docx):<br>
<br>
SOLICITUD DE TRASLADO<br>
<br>
Al Señor Juez de Ejecución:<br>
<br>
Por medio del presente, ${nombres} ${apellidos}, <br>
RUT ${rut}, actualmente interno en ${unidad_penal}, <br>
solicita respetuosamente ser trasladado por las siguientes razones:<br>
<br>
[ESPACIO PARA TEXTO PERSONALIZADO]<br>
<br>
Fecha: ${fecha_actual}<br>
Unidad Penal: ${unidad_penal}<br>
RIT: ${rit}
            </div>

            <div class="code-block">
// 2. Comando SQL para registrar:<br>
<br>
INSERT INTO solicitudes (tipo_solicitud, archivo) <br>
VALUES ('Solicitud de Traslado', 'solicitudes/traslado.docx');
            </div>

            <div class="alert alert-success mt-3">
                <strong>✅ ¡Listo!</strong> La plantilla estará disponible inmediatamente para todos los usuarios.
            </div>

        </div>
    </div>

    <!-- Sección 7: Accesos Rápidos -->
    <h3 class="section-title">🚀 ACCESOS RÁPIDOS</h3>
    <div class="row text-center">
        <div class="col-md-4">
            <div class="feature-box">
                <h4>📝 Creador Principal</h4>
                <p>Interfaz para abogados</p>
                <a href="creador_escritos.php" class="btn btn-primary">Ir al Creador</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-box">
                <h4>👩‍💼 Creador TS</h4>
                <p>Para trabajadoras sociales</p>
                <a href="creador_escritos_ts.php" class="btn btn-success">Ir a TS</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-box">
                <h4>🔧 Administrar</h4>
                <p>Gestionar plantillas</p>
                <a href="gestionar_escritos.php" class="btn btn-info">Gestionar</a>
            </div>
        </div>
    </div>

</div>

<?php $conn->close(); ?>
</body>
</html>
