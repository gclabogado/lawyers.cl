<?php
/**
 * PERFIL PROFESIONAL DISCRETO - GABRIEL CALDERÓN
 * Estilo: Minimalismo Ejecutivo / Perfil Bajo.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gabriel Calderón Lewin | Consultoría Jurídica</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --bg: #ffffff;
            --text-main: #1a1a1a;
            --text-muted: #666666;
            --border: #eeeeee;
            --accent: #1e40af; /* Azul marino discreto */
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Inter', sans-serif; 
            color: var(--text-main);
            line-height: 1.7;
        }

        /* --- Estructura Minimalista --- */
        .profile-container {
            max-width: 850px;
            margin: 80px auto;
            padding: 0 25px;
        }

        header {
            margin-bottom: 60px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 40px;
        }

        .name-title {
            font-weight: 600;
            font-size: 2.2rem;
            letter-spacing: -0.02em;
            margin-bottom: 5px;
        }

        .headline {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-weight: 300;
        }

        /* --- Secciones --- */
        .section-title {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin-bottom: 30px;
            border-left: 3px solid var(--accent);
            padding-left: 15px;
        }

        .experience-item {
            margin-bottom: 45px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 8px;
        }

        .role-title {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .date-label {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .description {
            font-size: 0.95rem;
            color: var(--text-muted);
            text-align: justify;
        }

        /* --- Sidebar Info --- */
        .edu-item {
            margin-bottom: 25px;
        }

        .edu-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .edu-inst {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* --- Botón Discreto --- */
        .btn-contact {
            background: #1a1a1a;
            color: #fff;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-block;
            transition: 0.2s;
        }
        .btn-contact:hover {
            background: #333;
            color: #fff;
        }

        .ethics-disclaimer {
            font-size: 0.8rem;
            color: var(--text-muted);
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin-top: 50px;
            border: 1px solid var(--border);
        }

        @media (max-width: 768px) {
            .profile-container { margin: 40px auto; }
            .item-header { flex-direction: column; }
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <header>
            <h1 class="name-title">Gabriel Calderón Lewin</h1>
            <p class="headline">Abogado · Consultor en Gestión Procesal Técnica</p>
        </header>

        <div class="row g-5">
            <div class="col-md-7">
                <h2 class="section-title">Trayectoria</h2>

                <div class="experience-item">
                    <div class="item-header">
                        <span class="role-title">Fundador Lawyers.cl</span>
                        <span class="date-label">2024 — Actualidad</span>
                    </div>
                    <p class="description">
                        Desarrollo e implementación de sistemas automatizados para la optimización de flujos procesales. Especialización en arquitectura de datos aplicada al cálculo de tiempos legales y gestión técnica penitenciaria.
                    </p>
                </div>

                <div class="experience-item">
                    <div class="item-header">
                        <span class="role-title">Defensor Penal Público (L)</span>
                        <span class="date-label">2023 — Actualidad</span>
                    </div>
                    <p class="description">
                        Defensa penal especializada en etapa de ejecución de penas. Litigación ante Juzgados de Garantía con enfoque en el control de legalidad del cumplimiento y beneficios normativos.
                    </p>
                </div>

                <div class="experience-item">
                    <div class="item-header">
                        <span class="role-title">Litigación Penal Privada</span>
                    </div>
                    <p class="description">
                        Estrategia de defensa criminal judicial en etapas de investigación y juicio oral. Análisis técnico de evidencia y representación estructural en audiencias de control y medidas cautelares.
                    </p>
                </div>
            </div>

            <div class="col-md-5">
                <h2 class="section-title">Enlace</h2>
                <a href="https://wa.me/56956065745" class="btn-contact mb-4 w-100 text-center">Consultoría Directa</a>
                
                <div class="mt-4">
                    <h2 class="section-title">Formación</h2>
                    
                    <div class="edu-item">
                        <p class="edu-title">Abogado</p>
                        <p class="edu-inst">Excma. Corte Suprema de Chile · 2018</p>
                    </div>

                    <div class="edu-item">
                        <p class="edu-title">Postítulo en Derecho Penal</p>
                        <p class="edu-inst">Universidad Alberto Hurtado</p>
                    </div>

                    <div class="edu-item">
                        <p class="edu-title">Licenciado en Ciencias Jurídicas</p>
                        <p class="edu-inst">Universidad de Tarapacá</p>
                    </div>
                </div>

                <div class="ethics-disclaimer">
                    <strong>Nota de Probidad:</strong><br>
                    Por transparencia institucional, mi práctica privada se limita exclusivamente a la litigación judicial, sin intervención en materias de beneficios penitenciarios directos.
                </div>
            </div>
        </div>

        <footer class="mt-5 pt-5 text-center">
            <p class="small text-muted">
                © <?php echo date("Y"); ?> Gabriel Calderón Lewin · Lawyers.cl<br>
                La Serena, Chile.
            </p>
        </footer>
    </div>

</body>
</html>
