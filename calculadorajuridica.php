<?php
/**
 * LAWYERS.CL - Calculadora Jurídica v12.0 (Premium Harmony)
 * Lógica de Abonos y Plazos.
 */

$resultado_plazo = "";
$resultado_abono = "";
$mostrar_plazo = false;
$mostrar_abono = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $f_inicio = new DateTime($_POST['inicio']);
    $f_fin = new DateTime($_POST['fin']);
    $diff = $f_inicio->diff($f_fin);
    $action = $_POST['action'];

    if ($action == 'plazo') {
        $mostrar_plazo = true;
        $resultado_plazo = "📅 *CÓMPUTO DE PLAZOS CORRIDOS*\n" .
                           "• Fecha Inicial: " . $f_inicio->format('d/m/Y') . "\n" .
                           "• Fecha Término: " . $f_fin->format('d/m/Y') . "\n" .
                           "• TOTAL: " . $diff->days . " días corridos.\n" .
                           "• DESGLOSE: {$diff->y} años, {$diff->m} meses, {$diff->d} días.\n" .
                           "Calculado en Lawyers.cl ⚖️";
    } else {
        $mostrar_abono = true;
        $hrs_diarias = intval($_POST['horas']);
        $dias_naturales = $diff->days + 1;

        if ($hrs_diarias >= 24) {
            $abono_final = $dias_naturales;
            $explicacion = "Regla 1:1 (Arresto Domiciliario Total / PP)";
        } else {
            $total_horas = $dias_naturales * $hrs_diarias;
            $abono_final = floor($total_horas / 12);
            $explicacion = "Fórmula: ($dias_naturales días × $hrs_diarias hrs) = $total_horas hrs totales. \n" .
                           "Cómputo: $total_horas / 12 hrs (Art. 348 CPP).";
        }

        $resultado_abono = "⚖️ *MINUTA DE ABONO PROCESAL*\n" .
                           "• Periodo: " . $f_inicio->format('d/m/Y') . " al " . $f_fin->format('d/m/Y') . "\n" .
                           "• Días cumplidos: $dias_naturales\n" .
                           "• Régimen: $hrs_diarias hrs diarias.\n" .
                           "• Cálculo: $explicacion\n" .
                           "• ABONO RESULTANTE: $abono_final DÍAS.\n\n" .
                           "Referencia: Excma. Corte Suprema, Rol N°22.539-2014.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora Jurídica | Lawyers.cl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --slate-950: #020617;
            --slate-900: #0f172a; 
            --slate-800: #1e293b;
            --indigo-500: #6366f1;
            --indigo-400: #818cf8;
            --gold-grad: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            --bg-body: #f8fafc;
        }
        
        body { background-color: var(--bg-body); color: var(--slate-900); font-family: 'Inter', sans-serif; }
        h1, h2, h3, .brand-font { font-family: 'Montserrat', sans-serif; font-weight: 800; }

        .container-custom { max-width: 1300px; margin: auto; padding: 60px 20px; }

        /* Header */
        .hero-title { font-size: 3.5rem; letter-spacing: -2px; color: var(--slate-950); }
        .hero-subtitle { color: var(--indigo-500); font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-size: 0.9rem; }

        /* Cards */
        .calc-card { 
            background: white; border-radius: 24px; border: 1px solid #e2e8f0; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.04); padding: 40px !important; height: 100%;
            transition: transform 0.3s ease;
        }
        .calc-card:hover { transform: translateY(-5px); }
        .card-title { font-size: 1.4rem; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }
        .card-title i { color: var(--indigo-500); }

        /* Inputs */
        .label-mini { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 8px; display: block; }
        .form-control, .form-select { 
            border: 2px solid #f1f5f9; background: #f8fafc; padding: 14px; border-radius: 12px; font-weight: 500;
        }
        .form-control:focus, .form-select:focus { 
            border-color: var(--indigo-400); background: white; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); 
        }

        /* Buttons */
        .btn-calc { 
            background: var(--slate-900); border: none; font-weight: 700; padding: 16px; 
            border-radius: 12px; transition: all 0.3s; color: white;
        }
        .btn-calc:hover { background: var(--indigo-500); transform: scale(1.02); color: white; }
        
        .btn-copy { 
            background: white; color: var(--slate-900); border: 2px solid var(--slate-900); 
            font-weight: 700; padding: 12px; border-radius: 10px; margin-top: 10px;
        }
        .btn-copy:hover { background: var(--slate-900); color: white; }

        /* Resultados */
        .result-box { 
            background: #f1f5f9; color: var(--slate-900); padding: 25px; border-radius: 16px; 
            font-family: 'Courier New', monospace; font-size: 1rem; border-left: 5px solid var(--indigo-500); 
            white-space: pre-wrap;
        }

        /* Doctrina Section */
        .doctrine-card { 
            background: var(--slate-900); color: white; padding: 40px; border-radius: 24px; 
            margin-top: 60px; position: relative; overflow: hidden;
        }
        .doctrine-card::after {
            content: "\f02d"; font-family: "Font Awesome 6 Free"; font-weight: 900;
            position: absolute; right: -20px; bottom: -20px; font-size: 10rem; opacity: 0.05;
        }
        .doc-link { 
            background: var(--indigo-500); color: white; text-decoration: none; 
            padding: 14px 28px; border-radius: 50px; font-weight: 700; display: inline-flex;
            align-items: center; gap: 10px; transition: 0.3s;
        }
        .doc-link:hover { background: white; color: var(--slate-950); transform: translateY(-3px); }

        .footer-legal { margin-top: 50px; color: #94a3b8; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="container-custom">
    
    <div class="header-title text-center mb-5">
        <p class="hero-subtitle">Módulo de ingeniería jurídica</p>
        <h2 class="hero-title">Calculadora<span style="color: var(--indigo-500)">.</span></h2>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card calc-card">
                <h3 class="card-title"><i class="far fa-calendar-check"></i> Cómputo de Plazos</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="plazo">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="label-mini">Fecha Inicial</label>
                            <input type="date" name="inicio" class="form-control" onclick="this.showPicker()" required value="<?= $_POST['inicio'] ?? '' ?>">
                        </div>
                        <div class="col-6">
                            <label class="label-mini">Fecha Final</label>
                            <input type="date" name="fin" class="form-control" onclick="this.showPicker()" required value="<?= $_POST['fin'] ?? date('Y-m-d') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-calc w-100 mt-4 shadow">CALCULAR DÍAS CORRIDOS</button>
                </form>

                <?php if ($mostrar_plazo): ?>
                <div class="mt-4 animate-in">
                    <div class="result-box" id="txt-plazo"><?= $resultado_plazo ?></div>
                    <button onclick="copiar('txt-plazo', this)" class="btn btn-copy w-100"><i class="fas fa-copy me-2"></i>COPIAR MINUTA</button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card calc-card">
                <h3 class="card-title"><i class="fas fa-scale-unbalanced"></i> Abonos (Art. 348 CPP)</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="abono">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="label-mini">Desde</label>
                            <input type="date" name="inicio" class="form-control" onclick="this.showPicker()" required value="<?= $_POST['inicio'] ?? '' ?>">
                        </div>
                        <div class="col-6">
                            <label class="label-mini">Hasta</label>
                            <input type="date" name="fin" class="form-control" onclick="this.showPicker()" required value="<?= $_POST['fin'] ?? date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="label-mini">Régimen de cumplimiento</label>
                        <select name="horas" class="form-select">
                            <option value="8" <?= (isset($_POST['horas']) && $_POST['horas'] == '8') ? 'selected' : '' ?>>8 hrs (Nocturno / Art. 155 letra e)</option>
                            <option value="12" <?= (isset($_POST['horas']) && $_POST['horas'] == '12') ? 'selected' : '' ?>>12 hrs (Arresto Parcial Diario)</option>
                            <option value="24" <?= (isset($_POST['horas']) && $_POST['horas'] == '24') ? 'selected' : '' ?>>24 hrs (Arresto Total / Prisión Preventiva)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-calc w-100 mt-4 shadow">GENERAR CÓMPUTO DE ABONO</button>
                </form>

                <?php if ($mostrar_abono): ?>
                <div class="mt-4 animate-in">
                    <div class="result-box" id="txt-abono" style="border-left-color: #10b981;"><?= $resultado_abono ?></div>
                    <button onclick="copiar('txt-abono', this)" class="btn btn-copy w-100"><i class="fas fa-copy me-2"></i>COPIAR MINUTA</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="doctrine-card shadow-lg">
                <h4 class="fw-bold mb-3"><i class="fas fa-scroll text-indigo-400 me-2"></i> Fundamentos del Abono</h4>
                <p class="opacity-75 mb-4">
                    La imputación de abonos es un derecho del sentenciado. Según el <strong>Art. 348 del CPP</strong>, se debe abonar a la pena todo el tiempo privado de libertad. La Excma. Corte Suprema ha ratificado el abono de medidas cautelares parciales bajo la regla de proporcionalidad de 12 horas.
                </p>
                
                <a href="https://www.librotecnia.cl/ckfinder/userfiles/files/RJP_dpp_04Elabonoalapenadelasprivacionesdelibertad_p159-172.pdf" target="_blank" class="doc-link">
                    <i class="fas fa-file-pdf"></i>
                    Descargar Doctrina Especializada (Troncoso, M.)
                </a>
            </div>
        </div>
    </div>

    <div class="footer-legal text-center">
        Jurisprudencia: Excma. Corte Suprema, Rol N°22.539-2014. | LAWYERS.CL &copy; 2026<br>
        Herramienta técnica para uso exclusivo de abogados y operadores del sistema penal.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copiar(id, btn) {
    const text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text);
    
    const originalText = btn.innerHTML;
    const originalClass = btn.className;

    btn.className = 'btn btn-success w-100 fw-bold shadow-sm';
    btn.innerHTML = '<i class="fas fa-check me-2"></i>¡COPIADO AL PORTAPAPELES!';
     
    setTimeout(() => {
        btn.className = originalClass;
        btn.innerHTML = originalText;
    }, 2000);
}
</script>

</body>
</html>
