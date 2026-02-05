<?php
/**
 * LAWYERS.CL - Portal de Partners (Expansión Nacional)
 * Objetivo: Venta de Slots publicitarios para estudios jurídicos.
 */
$regiones_chile = ["Arica", "Iquique", "Antofagasta", "Copiapó", "La Serena", "Valparaíso", "Santiago", "Rancagua", "Talca", "Chillán", "Concepción", "Temuco", "Valdivia", "Puerto Montt", "Coyhaique", "Punta Arenas"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partners | Lawyers.cl - Expansión Nacional</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { 
            --slate-950: #020617;
            --slate-900: #0f172a; 
            --indigo-500: #6366f1;
            --gold-grad: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
        }

        body { 
            background-color: #f8fafc; 
            font-family: 'Inter', sans-serif; 
            color: var(--slate-900);
        }

        header {
            background: var(--slate-950);
            padding: 80px 20px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0);
            background-size: 40px 40px;
        }

        .hero-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 3rem;
            background: var(--gold-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .nav-status {
            background: rgba(255,255,255,0.1);
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }

        /* FILTROS PREVIEW */
        .filter-preview {
            max-width: 900px;
            margin: -40px auto 40px;
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            display: flex;
            gap: 15px;
            align-items: center;
            position: relative;
            z-index: 10;
            border: 1px solid #e2e8f0;
        }

        /* SLOTS NACIONALES */
        .slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 80px;
        }

        .card-partner {
            background: white;
            border-radius: 24px;
            padding: 40px 30px;
            border: 2px dashed #e2e8f0;
            transition: 0.3s;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-partner:hover {
            border: 2px solid var(--indigo-500);
            transform: translateY(-10px);
            background: #fcfcff;
            box-shadow: 0 25px 50px -12px rgba(99, 102, 241, 0.15);
        }

        .region-tag {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--indigo-500);
            background: #f1f0ff;
            padding: 4px 12px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .btn-reserve {
            background: var(--slate-950);
            color: white;
            padding: 15px 30px;
            border-radius: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
            margin-top: 20px;
            border: none;
        }

        .btn-reserve:hover {
            background: var(--indigo-500);
            color: white;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .upcoming-badge {
            background: var(--slate-900);
            color: #fbbf24;
            padding: 2px 8px;
            font-size: 0.65rem;
            border-radius: 4px;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<header>
    <div class="container position-relative">
        <span class="nav-status">Apertura Nacional 2026</span>
        <h1 class="hero-title">Domina tu Jurisdicción.</h1>
        <p class="text-white-50 fs-5 mx-auto" style="max-width: 700px;">
            Lawyers.cl se expande a todo Chile. Reserva tu espacio exclusivo y posiciona tu estudio en la vitrina legal más tecnológica del país.
        </p>
    </div>
</header>

<div class="container">
    
    <div class="filter-preview animate__animated animate__fadeInUp">
        <div class="flex-grow-1">
            <label class="small fw-bold text-muted text-uppercase mb-1 d-block">Región</label>
            <select class="form-select border-0 bg-light fw-bold" disabled>
                <option>Seleccione una región...</option>
                <?php foreach($regiones_chile as $r) echo "<option>$r</option>"; ?>
            </select>
        </div>
        <div class="flex-grow-1 border-start ps-4">
            <label class="small fw-bold text-muted text-uppercase mb-1 d-block">Especialidad</label>
            <select class="form-select border-0 bg-light fw-bold" disabled>
                <option>Todas las áreas</option>
                <option>Penal</option>
                <option>Familia</option>
                <option>Laboral</option>
            </select>
        </div>
        <div class="ps-3">
            <button class="btn btn-primary rounded-3 px-4 py-2" disabled><i class="fas fa-search"></i></button>
        </div>
    </div>

    <div class="text-center mb-5">
        <h2 class="fw-800">Espacios Disponibles por Lanzamiento</h2>
        <p class="text-muted">Solo 3 cupos por región para asegurar máxima visibilidad.</p>
    </div>

    <div class="slot-grid">
        <?php for($i=1; $i<=6; $i++): ?>
        <div class="card-partner">
            <div class="region-tag">Cupo Disponible <i class="fas fa-globe-americas ms-1"></i></div>
            <i class="fas fa-balance-scale fa-3x mb-4 text-light"></i>
            <h3 class="fw-bold h5">Tu Marca Aquí</h3>
            <p class="small text-muted mb-4">
                Aparece en los primeros resultados de búsqueda regional y destaca con el sello de partner tecnológico de Lawyers.cl.
            </p>
            <a href="https://wa.me/56956065745?text=Hola,%20me%20interesa%20reservar%20un%20cupo%20nacional%20en%20Lawyers.cl" 
               class="btn-reserve">
                <i class="fas fa-id-badge me-2"></i> Solicitar Reserva
            </a>
        </div>
        <?php endfor; ?>
    </div>

    <div class="alert bg-white border rounded-4 p-5 text-center shadow-sm">
        <span class="badge bg-indigo-100 text-indigo-600 mb-2">PRÓXIMAMENTE</span>
        <h4 class="fw-800">Red de Defensa Experta Nacional <span class="upcoming-badge">BETA</span></h4>
        <p class="text-muted mx-auto mb-0" style="max-width: 600px;">
            Estamos integrando una infraestructura de red donde abogados de todo Chile podrán compartir diligencias, coberturas y gestión técnica procesal en una sola plataforma unificada.
        </p>
    </div>

</div>

<footer class="py-5 text-center">
    <p class="text-muted small">© 2026 Lawyers.cl | Gestión de Redes Legales</p>
</footer>

</body>
</html>
