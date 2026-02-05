<?php
// ====================================================
// DASHBOARD DIVERSIDAD & KPI - SAAS EDITION
// ====================================================

session_start();
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }

// 1. CONEXIÓN CENTRALIZADA
require_once 'db.php'; 

// 2. DEFINIR FILTRO DE PRIVACIDAD
$mi_id  = $_SESSION['user_id'];
$mi_rol = $_SESSION['rol'];

// Si es admin ve todo (1=1), si no, solo lo suyo
$filtro_sql = ($mi_rol === 'admin') ? "1=1" : "usuario_id = '$mi_id'";

// -----------------------------------------------------------------------
// 3. CONSULTAS DE ESTADÍSTICAS (BLINDADAS)
// -----------------------------------------------------------------------
$stats = [];

// Total mujeres
$sql = "SELECT COUNT(*) as total FROM internos WHERE sexo = 'femenino' AND ($filtro_sql)";
$result = $conn->query($sql);
$stats['mujeres'] = $result ? $result->fetch_assoc()['total'] : 0;

// Total comunidad LGTBIQ+
$sql = "SELECT COUNT(*) as total FROM internos WHERE sexo IN ('hombre trans', 'mujer trans', 'no binario', 'intersex', 'otro') AND ($filtro_sql)";
$result = $conn->query($sql);
$stats['lgtbiq'] = $result ? $result->fetch_assoc()['total'] : 0;

// Total general de internos
$sql = "SELECT COUNT(*) as total FROM internos WHERE ($filtro_sql)";
$result = $conn->query($sql);
$stats['total_internos'] = $result ? $result->fetch_assoc()['total'] : 0;

// Todas las nacionalidades con conteo - NORMALIZADAS Y FILTRADAS
$sql_nacionalidades = "SELECT nacionalidad, COUNT(*) as cantidad 
                       FROM internos 
                       WHERE ($filtro_sql)
                       GROUP BY nacionalidad 
                       ORDER BY cantidad DESC, nacionalidad ASC";
                       
$result_nacionalidades = $conn->query($sql_nacionalidades);
$nacionalidades_brutas = [];
if ($result_nacionalidades) {
    while($row = $result_nacionalidades->fetch_assoc()) {
        $nacionalidades_brutas[] = $row;
    }
}

// -----------------------------------------------------------------------
// 4. FUNCIONES DE APOYO (LÓGICA DE NEGOCIO)
// -----------------------------------------------------------------------

function h($s){ return htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }

function normalizarNacionalidad($nacionalidad) {
    $nacionalidad = trim($nacionalidad);
    $lower = strtolower($nacionalidad);
    $mapeo = [
        'chileno' => 'Chile', 'chilena' => 'Chile', 'chile' => 'Chile',
        'boliviano' => 'Bolivia', 'boliviana' => 'Bolivia',
        'colombiano' => 'Colombia', 'colombiana' => 'Colombia',
        'peruano' => 'Perú', 'peruana' => 'Perú',
        'argentino' => 'Argentina', 'argentina' => 'Argentina',
        'venezolano' => 'Venezuela', 'venezolana' => 'Venezuela',
        'ecuatoriano' => 'Ecuador', 'ecuatoriana' => 'Ecuador',
        'haitiano' => 'Haití', 'haitiana' => 'Haití'
    ];
    if (isset($mapeo[$lower])) { return $mapeo[$lower]; }
    return ucwords($lower);
}

function primeras3Letras($texto) {
    if (empty($texto)) return 'N/A';
    $texto = trim($texto);
    if (strlen($texto) < 3) return strtoupper(str_pad($texto, 3, '_'));
    return strtoupper(substr($texto, 0, 3));
}

function esChileno($nacionalidad) {
    $nacionalidad = strtolower($nacionalidad);
    return (strpos($nacionalidad, 'chile') !== false);
}

// -----------------------------------------------------------------------
// 5. PROCESAMIENTO DE DATOS (NORMALIZACIÓN)
// -----------------------------------------------------------------------

$nacionalidades_normalizadas = [];
foreach ($nacionalidades_brutas as $nac) {
    $normalizada = normalizarNacionalidad($nac['nacionalidad']);
    if (!isset($nacionalidades_normalizadas[$normalizada])) {
        $nacionalidades_normalizadas[$normalizada] = [
            'nacionalidad' => $normalizada,
            'cantidad' => 0,
            'variantes' => []
        ];
    }
    $nacionalidades_normalizadas[$normalizada]['cantidad'] += $nac['cantidad'];
    $nacionalidades_normalizadas[$normalizada]['variantes'][] = [
        'original' => $nac['nacionalidad'],
        'cantidad' => $nac['cantidad']
    ];
}
$todas_nacionalidades = array_values($nacionalidades_normalizadas);

// Cálculo de Extranjeros
$stats['extranjeros'] = 0;
$nacionalidades_extranjeras_consolidadas = []; 
foreach ($nacionalidades_normalizadas as $nac) {
    if (!esChileno($nac['nacionalidad'])) {
        $stats['extranjeros'] += $nac['cantidad'];
        $nacionalidades_extranjeras_consolidadas[] = $nac;
    }
}

// Generar códigos únicos
$codigos_nacionalidades = [];
foreach ($todas_nacionalidades as $nacionalidad) {
    $codigo = primeras3Letras($nacionalidad['nacionalidad']);
    $codigos_nacionalidades[$codigo] = $nacionalidad['nacionalidad'];
}

// -----------------------------------------------------------------------
// 6. CONSULTAS DETALLADAS (LISTADOS INDIVIDUALES BLINDADOS)
// -----------------------------------------------------------------------

// A. Lista Detallada de MUJERES
$sql_lista_mujeres = "SELECT nombres, apellidos, rut, carcel, nacionalidad 
                      FROM internos 
                      WHERE sexo = 'femenino' AND ($filtro_sql)
                      ORDER BY apellidos, nombres";
$result_lista_mujeres = $conn->query($sql_lista_mujeres);
$mujeres_completas = []; 
if ($result_lista_mujeres) {
    while($row = $result_lista_mujeres->fetch_assoc()) $mujeres_completas[] = $row;
}

// B. Lista Detallada LGTBIQ+
$sql_lista_lgtbiq = "SELECT nombres, apellidos, rut, carcel, nacionalidad, sexo 
                     FROM internos 
                     WHERE sexo IN ('hombre trans', 'mujer trans', 'no binario', 'intersex', 'otro') AND ($filtro_sql)
                     ORDER BY sexo, apellidos";
$result_lista_lgtbiq = $conn->query($sql_lista_lgtbiq);
$lgtbiq_completos = []; 
if ($result_lista_lgtbiq) {
    while($row = $result_lista_lgtbiq->fetch_assoc()) $lgtbiq_completos[] = $row;
}

// C. Lista Detallada de EXTRANJEROS
// Nota: La lógica SQL LIKE aquí debe coincidir con la lógica PHP de esChileno para consistencia visual
$sql_lista_extranjeros = "SELECT nombres, apellidos, rut, carcel, nacionalidad 
                          FROM internos 
                          WHERE (LOWER(nacionalidad) NOT LIKE '%chile%' 
                          AND LOWER(nacionalidad) NOT LIKE '%chilena%'
                          AND LOWER(nacionalidad) NOT LIKE '%chileno%')
                          AND ($filtro_sql)
                          ORDER BY nacionalidad, apellidos";
$result_lista_extranjeros = $conn->query($sql_lista_extranjeros);
$extranjeros_completos = [];
if ($result_lista_extranjeros) {
    while($row = $result_lista_extranjeros->fetch_assoc()) $extranjeros_completos[] = $row;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Diversidad - Lawyers.cl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-header { background-color: #f8f9fa; color: #212529; padding: 25px 0; margin-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .stat-card-kpi { background: white; border-radius: 8px; padding: 15px; text-align: center; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); margin-bottom: 20px; border-left: 5px solid; }
        .stat-number { font-size: 1.8rem; font-weight: bold; margin-bottom: 0px; }
        
        .mujeres { border-color: #e91e63; color: #e91e63; }
        .lgtbiq { border-color: #9b59b6; color: #9b59b6; }
        .extranjeros { border-color: #3498db; color: #3498db; }
        .total { border-color: #27ae60; color: #27ae60; }

        .codigo-nacionalidad { font-family: 'Courier New', monospace; background: #f8f9fa; padding: 4px 8px; border-radius: 4px; border: 1px solid #dee2e6; font-weight: bold; font-size: 0.8rem; min-width: 45px; text-align: center; color: #2c3e50; }
        .leyenda-codigos { background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .codigo-leyenda { display: inline-block; margin: 5px 10px 5px 0; font-family: 'Courier New', monospace; background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #dee2e6; font-size: 0.8rem; }
        .table-kpi tbody tr td { vertical-align: middle; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <h1 class="text-center mb-0">
                <i class="fas fa-chart-line me-2"></i>Dashboard Diversidad
                <span class="fs-6 d-block text-muted mt-2 fw-normal">
                    <?= ($mi_rol === 'admin') ? '(Vista Global Administrador)' : '(Mis Estadísticas)'; ?>
                </span>
            </h1>
        </div>
    </div>

    <div class="container">
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card-kpi mujeres">
                    <div class="stat-number text-dark"><?= $stats['mujeres'] ?></div>
                    <div class="fw-bold"><i class="fas fa-venus me-1"></i>Mujeres</div>
                    <small class="text-muted"><?= ($stats['total_internos'] > 0) ? round(($stats['mujeres'] / $stats['total_internos']) * 100, 1) : 0 ?>% del total</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-kpi lgtbiq">
                    <div class="stat-number text-dark"><?= $stats['lgtbiq'] ?></div>
                    <div class="fw-bold"><i class="fas fa-rainbow me-1"></i>LGTBIQ+</div>
                    <small class="text-muted"><?= ($stats['total_internos'] > 0) ? round(($stats['lgtbiq'] / $stats['total_internos']) * 100, 1) : 0 ?>% del total</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-kpi extranjeros">
                    <div class="stat-number text-dark"><?= $stats['extranjeros'] ?></div>
                    <div class="fw-bold"><i class="fas fa-passport me-1"></i>Extranjeros</div>
                    <small class="text-muted"><?= ($stats['total_internos'] > 0) ? round(($stats['extranjeros'] / $stats['total_internos']) * 100, 1) : 0 ?>% del total</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-kpi total">
                    <div class="stat-number text-dark"><?= $stats['total_internos'] ?></div>
                    <div class="fw-bold"><i class="fas fa-users me-1"></i>Total Casos</div>
                    <small class="text-muted">En tu base activa</small>
                </div>
            </div>
        </div>
        
        <div class="leyenda-codigos">
            <h6 class="mb-3 fw-bold">
                <i class="fas fa-key me-2"></i>Leyenda de Códigos - Nacionalidades Normalizadas
            </h6>
            <div>
                <?php foreach ($codigos_nacionalidades as $codigo => $nacionalidad): ?>
                    <span class="codigo-leyenda">
                        <strong><?= h($codigo) ?></strong>: <?= h($nacionalidad) ?>
                    </span>
                <?php endforeach; ?>
                <?php if(empty($codigos_nacionalidades)): ?>
                    <span class="text-muted small">No hay datos suficientes para generar estadísticas.</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
             <div class="card-header fw-bold">
                <i class="fas fa-list-ul me-2"></i>Consolidado General de Nacionalidades
                <span class="badge bg-secondary"><?= count($todas_nacionalidades) ?> países</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (empty($todas_nacionalidades)): ?>
                        <div class="col-12 text-center text-muted py-3">Sin registros de nacionalidad.</div>
                    <?php else: ?>
                        <?php foreach ($todas_nacionalidades as $nacionalidad): ?>
                            <div class="col-md-4 mb-3">
                                 <div class="p-2 border rounded d-flex justify-content-between align-items-center">
                                     <span class="codigo-nacionalidad me-2">
                                         <?= primeras3Letras($nacionalidad['nacionalidad']) ?>
                                     </span>
                                    <strong class="text-primary"><?= h($nacionalidad['nacionalidad']) ?></strong>
                                    <span class="badge bg-primary rounded-pill"><?= $nacionalidad['cantidad'] ?></span>
                                 </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (count($mujeres_completas) > 0): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span class="text-danger"><i class="fas fa-venus me-2"></i>Lista Detallada de Mujeres Internas</span>
                <span class="badge bg-danger"><?= count($mujeres_completas) ?></span>
            </div>
            
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped table-kpi align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código Nac.</th>
                            <th>Nombre Completo</th>
                            <th>RUT</th>
                            <th>Establecimiento</th>
                            <th>Nacionalidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mujeres_completas as $index => $interna): 
                            $nacionalidad_normalizada = normalizarNacionalidad($interna['nacionalidad']);
                        ?>
                        <tr>
                            <td class="text-muted"><?= $index + 1 ?></td>
                            <td><span class="codigo-nacionalidad"><?= primeras3Letras($nacionalidad_normalizada) ?></span></td>
                            <td><strong><?= h($interna['nombres'] . ' ' . $interna['apellidos']) ?></strong></td>
                            <td><?= h($interna['rut']) ?></td>
                            <td><?= h($interna['carcel']) ?></td>
                            <td>
                                <strong><?= h($nacionalidad_normalizada) ?></strong>
                                <?php if ($interna['nacionalidad'] !== $nacionalidad_normalizada): ?>
                                <br><small class="text-muted">(Original: <?= h($interna['nacionalidad']) ?>)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($lgtbiq_completos) > 0): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span class="text-purple"><i class="fas fa-rainbow me-2"></i>Lista Detallada LGTBIQ+</span>
                <span class="badge bg-secondary"><?= count($lgtbiq_completos) ?></span>
            </div>
            
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped table-kpi align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código Nac.</th>
                            <th>Sexo Identificado</th>
                            <th>Nombre Completo</th>
                            <th>RUT</th>
                            <th>Establecimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($lgtbiq_completos as $index => $lgtbiq_interno): 
                            $nacionalidad_normalizada = normalizarNacionalidad($lgtbiq_interno['nacionalidad']);
                        ?>
                        <tr>
                            <td class="text-muted"><?= $index + 1 ?></td>
                            <td><span class="codigo-nacionalidad"><?= primeras3Letras($nacionalidad_normalizada) ?></span></td>
                            <td><strong class="text-primary"><?= h(ucwords($lgtbiq_interno['sexo'])) ?></strong></td>
                            <td><strong><?= h($lgtbiq_interno['nombres'] . ' ' . $lgtbiq_interno['apellidos']) ?></strong></td>
                            <td><?= h($lgtbiq_interno['rut']) ?></td>
                            <td><?= h($lgtbiq_interno['carcel']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($nacionalidades_extranjeras_consolidadas) > 0 && $stats['extranjeros'] > 0): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">
                <i class="fas fa-chart-pie me-2"></i>Consolidado de Extranjeros por Nacionalidad
                <span class="badge bg-info"><?= count($nacionalidades_extranjeras_consolidadas) ?> países</span>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <?php foreach ($nacionalidades_extranjeras_consolidadas as $nacionalidad): 
                        $porcentaje_sobre_extranjeros = ($nacionalidad['cantidad'] / $stats['extranjeros']) * 100;
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded shadow-sm">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center">
                                    <span class="codigo-nacionalidad me-3"><?= primeras3Letras($nacionalidad['nacionalidad']) ?></span>
                                    <div style="min-width: 150px;">
                                        <div class="fw-bold text-info"><?= h($nacionalidad['nacionalidad']) ?></div>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $porcentaje_sobre_extranjeros > 3 ? $porcentaje_sobre_extranjeros : 3 ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-info fs-6"><?= $nacionalidad['cantidad'] ?></span>
                                    <div class="text-muted small mt-1"><?= round($porcentaje_sobre_extranjeros, 1) ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($extranjeros_completos) > 0): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span class="text-primary"><i class="fas fa-passport me-2"></i>Lista Detallada de Extranjeros</span>
                <span class="badge bg-primary"><?= count($extranjeros_completos) ?></span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped table-kpi align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código Nac.</th>
                            <th>Nombre Completo</th>
                            <th>RUT</th>
                            <th>Establecimiento</th>
                            <th>Nacionalidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($extranjeros_completos as $index => $extranjero): 
                            $nacionalidad_normalizada = normalizarNacionalidad($extranjero['nacionalidad']);
                        ?>
                        <tr>
                            <td class="text-muted"><?= $index + 1 ?></td>
                            <td><span class="codigo-nacionalidad"><?= primeras3Letras($nacionalidad_normalizada) ?></span></td>
                            <td><strong><?= h($extranjero['nombres'] . ' ' . $extranjero['apellidos']) ?></strong></td>
                            <td><?= h($extranjero['rut']) ?></td>
                            <td><?= h($extranjero['carcel']) ?></td>
                            <td>
                                <strong><?= h($nacionalidad_normalizada) ?></strong>
                                <?php if ($extranjero['nacionalidad'] !== $nacionalidad_normalizada): ?>
                                <br><small class="text-muted">(Original: <?= h($extranjero['nacionalidad']) ?>)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mt-4 mb-5">
            <div class="col-12">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="index.php?page=agregar_internosv3" class="btn btn-primary shadow-sm">
                        <i class="fas fa-user-plus me-2"></i>Nuevo Ingreso
                    </a>
                    <a href="index.php?page=listar_internos" class="btn btn-outline-primary shadow-sm">
                        <i class="fas fa-list me-2"></i>Ver Todos los Internos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
