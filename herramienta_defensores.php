<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Defensoría Escritos | Gold Edition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --accent: #fd79a8;
            --danger: #ff7675;
            --bg-color: #f8f9fd;
            --text-main: #2d3436;
        }
       
        body { background-color: var(--bg-color); font-family: 'Montserrat', sans-serif; color: var(--text-main); padding-bottom: 60px; }

        .app-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
            color: white; padding: 25px 40px; border-radius: 0 0 30px 30px;
            box-shadow: 0 10px 25px rgba(108, 92, 231, 0.25); margin-bottom: 40px;
        }

        .input-pro {
            border: 2px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.15);
            color: white; border-radius: 30px; padding: 10px 25px; font-weight: 500; outline: none; width: 350px;
            backdrop-filter: blur(5px);
        }

        .drop-zone {
            background: white; border: 2px dashed #b2bec3; border-radius: 25px;
            padding: 40px 20px; text-align: center; cursor: pointer; transition: 0.3s;
            height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }
        .drop-zone:hover { border-color: var(--primary); background: #fdfdff; transform: translateY(-5px); }
       
        .id-card {
            background: white; padding: 30px; border-radius: 25px; min-height: 520px;
            box-shadow: 0 15px 35px rgba(108, 92, 231, 0.1); border: 1px solid #f1f2f6; position: relative;
        }

        .label-text { color: #b2bec3; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 2px; display: block; }
        .value-text { color: #2d3436; font-size: 1rem; font-weight: 600; }

        .btn-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }

        .btn-action {
            background: white; border: none; border-radius: 20px; padding: 25px;
            text-align: left; transition: 0.3s; cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-left: 6px solid #6c5ce7;
        }
        .btn-action:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(108, 92, 231, 0.15); }
        .btn-urgent { border-left-color: #ff7675; }

        .btn-action h6 { font-weight: 700; margin-bottom: 8px; color: #2d3436; font-size: 1rem; }
        .btn-action p { font-size: 0.8rem; color: #636e72; margin: 0; line-height: 1.4; }
        .btn-action i { font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.9; }

        .btn-reset { position: absolute; top: 20px; right: 20px; background: #f1f2f6; border: none; color: #636e72; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; }
    </style>
</head>
<body>

<div class="app-header d-flex justify-content-between align-items-center">
    <div>
        <h3 class="m-0 fw-bold"><i class="fas fa-file-contract me-2"></i>Defensoría Escritos</h3>
        <small style="opacity: 0.9;">Gestor Documental de Calidad Jurídica</small>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="fw-bold text-uppercase small">Abogado:</span>
        <input type="text" id="abogadoInput" class="input-pro" placeholder="Nombre del Defensor...">
    </div>
</div>

<div class="container-fluid px-5">
    <div class="row g-5">
        <div class="col-lg-4">
            <div id="uploadState">
                <div class="drop-zone" id="dropZone">
                    <i class="fas fa-cloud-upload-alt fa-4x text-muted mb-3" style="color: #a29bfe !important;"></i>
                    <h4 class="fw-bold" style="color: #6c5ce7;">Cargar Ficha</h4>
                    <p class="text-muted small mb-0">Arrastra el PDF aquí</p>
                    <input type="file" id="fileInput" accept="application/pdf" style="display:none;">
                </div>
            </div>
            <div id="cardState" style="display:none;">
                <div class="id-card" id="output"></div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="btn-grid">
                <div class="btn-action btn-urgent" onclick="app.action('cautela')"><i class="fas fa-shield-alt"></i><h6>Cautela de Garantías</h6><p>Escrito extenso por aislamiento y segmentación agotada.</p></div>
                <div class="btn-action" onclick="app.action('penamixta')"><i class="fas fa-layer-group"></i><h6>Pena Mixta (Art. 33)</h6><p>Solicita remisión de oficios para monitoreo telemático.</p></div>
                <div class="btn-action" onclick="app.action('abonos')"><i class="fas fa-calculator"></i><h6>Certificación Abonos</h6><p>Oficio y certificación para abono heterogéneo.</p></div>
                <div class="btn-action btn-urgent" onclick="app.action('trasladojuez')"><i class="fas fa-truck-moving"></i><h6>Traslado ante Juez</h6><p>Por falta de respuesta administrativa y falta de arraigo.</p></div>
                <div class="btn-action" onclick="app.action('suma')"><i class="fas fa-gavel"></i><h6>Suma Judicial</h6><p>Escrito formal para el Tribunal con hechos y conducta.</p></div>
                <div class="btn-action" onclick="app.action('resumen')"><i class="fas fa-align-left"></i><h6>Resumen Ejecutivo</h6><p>Narrativa completa con fechas en palabras.</p></div>
                <div class="btn-action" onclick="app.action('rebaja')"><i class="fas fa-hourglass-half"></i><h6>Solicita Rebaja</h6><p>Oficio para informar sobre beneficio de rebaja de condena.</p></div>
                <div class="btn-action" onclick="app.action('modulo')"><i class="fas fa-dungeon"></i><h6>Cambio de Módulo</h6><p>Solicitud de traslado interno por seguridad o cupo.</p></div>
                <div class="btn-action" onclick="app.action('salud')"><i class="fas fa-heartbeat"></i><h6>Atención Médica</h6><p>Solicita medidas de salud para el interno.</p></div>
                <div class="btn-action" onclick="app.action('laboral')"><i class="fas fa-tools"></i><h6>Actividad Laboral</h6><p>Reconocimiento como trabajador independiente.</p></div>
                <div class="btn-action" onclick="app.action('talleres')"><i class="fas fa-graduation-cap"></i><h6>Talleres y Cursos</h6><p>Acceso a oferta programática según P.I.I.</p></div>
            </div>
        </div>
    </div>
</div>

<form id="downloadForm" action="descargar_word.php" method="POST" target="_blank" style="display:none;">
    <textarea name="content" id="docContent"></textarea>
    <input type="text" name="filename" id="docName">
</form>

<script>
const app = {
    data: { rut: null },
    regiones: { "ARICA": "DE ARICA Y PARINACOTA", "IQUIQUE": "DE TARAPACÁ", "ANTOFAGASTA": "DE ANTOFAGASTA", "SERENA": "DE COQUIMBO", "OVALLE": "DE COQUIMBO", "VALPARAISO": "DE VALPARAÍSO", "SANTIAGO": "METROPOLITANA", "RANCAGUA": "DEL LIBERTADOR B. O'HIGGINS", "TALCA": "DEL MAULE", "CONCEPCION": "DEL BIOBÍO", "TEMUCO": "DE LA ARAUCANÍA", "VALDIVIA": "DE LOS RÍOS", "PUERTO MONTT": "DE LOS LAGOS", "COYHAIQUE": "DE AYSÉN", "PUNTA ARENAS": "DE MAGALLANES" },

    init: function() {
        const saved = localStorage.getItem('defensor_name');
        document.getElementById('abogadoInput').value = saved || "GABRIEL MAURICIO CALDERON LEWIN";
        const dz = document.getElementById('dropZone');
        const inp = document.getElementById('fileInput');
        dz.onclick = () => inp.click();
        inp.onchange = (e) => this.readPDF(e.target.files[0]);
        dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.style.borderColor = '#6c5ce7'; dz.style.backgroundColor = '#f8f7ff'; });
        dz.addEventListener('drop', (e) => { e.preventDefault(); if(e.dataTransfer.files[0]?.type === "application/pdf") this.readPDF(e.dataTransfer.files[0]); });
    },

    readPDF: async function(file) {
        document.getElementById('uploadState').style.opacity = '0.5';
        try {
            const buffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument(buffer).promise;
            let fullText = "";
            for(let i=1; i<=pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const content = await page.getTextContent();
                fullText += content.items.map(s => s.str).join("\n") + "\n";
            }
            this.extract(fullText);
        } catch (e) { alert("Error al leer PDF."); }
    },

    extract: function(txt) {
        const rutMatch = txt.match(/(\d{1,2}[\. ]?\d{3}[\. ]?\d{3}-[\dkK])/);
        const rut = rutMatch ? rutMatch[0].replace(/\s/g, '') : "NO DETECTADO";
        let apellidos = "", nombres = "";
        if (rut !== "NO DETECTADO") {
            const lines = txt.split(/\n/);
            for(let i=0; i<lines.length; i++) {
                if(lines[i].replace(/\s/g,'').includes(rut)) {
                    let valid = [];
                    for(let k=1; k<=6; k++) {
                        let l = lines[i+k]?.trim();
                        let clean = l.replace(/^(Nombres|Apellidos|RUN|Fecha de)[:\s]*/i, "").trim();
                        if(clean && !clean.match(/^[\d\/\-\.]+$/) && clean.length > 2) valid.push(clean);
                    }
                    if(valid.length >= 1) apellidos = valid[0];
                    if(valid.length >= 2) nombres = valid[1];
                    break;
                }
            }
        }
        const causes = txt.split(/Causa sistema antiguo:/i);
        let tribunal = "S/I", rit = "S/I", delito = "S/I";
        if(causes.length > 1) {
            const b = causes[1];
            rit = (b.match(/RIT tribunal:\s*([^\n\r]+)/i) || ["","S/I"])[1].trim();
            let finder = b.match(/((?:[0-9]+[º°]?\s*|PRIMER\s*|SEGUNDO\s*|TERCER\s*)?(?:JUZGADO|TRIBUNAL)?\s*(?:DE)?\s*(?:GARANTIA|ORAL|LETRAS|TOP)\s*(?:DE)?\s*[A-Z\s\.]+)/i);
            tribunal = finder ? finder[0].trim().replace(/^Tribunal:\s*/i, "") : "S/I";
            tribunal = tribunal.replace(/\s*(Delitos|RIT|Rol).*$/i, "").trim();
            delito = (b.match(/Delitos:\s*([\s\S]*?)(?=\n|$|Condenas)/i) || ["","S/I"])[1].replace(/\n/g, " ").trim();
        }
        let unidadRaw = (txt.match(/Unidad:\s*([^\n]+)/i) || ["","NO INDICA"])[1].split("DNI")[0].replace("Unidad:","").trim();
        let region = "REGIÓN DE COQUIMBO";
        for (const [key, value] of Object.entries(this.regiones)) {
            if (unidadRaw.toUpperCase().includes(key)) { region = "REGIÓN " + value; break; }
        }
        const findDate = (label) => {
            const regex = new RegExp(label + ".*?(\\d{2}/\\d{2}/\\d{4})", "i");
            let m = txt.match(regex);
            if(!m) {
                const lines = txt.split(/\n/);
                for(let i=0; i<lines.length; i++) {
                    if(lines[i].includes(label) && lines[i+1]) {
                        let m2 = lines[i+1].match(/(\d{2}\/\d{2}\/\d{4})/);
                        if(m2) return m2[1];
                    }
                }
                return "S/I";
            }
            return m[1];
        };
        const conductaMatches = Array.from(txt.matchAll(/Fecha Conducta:.*Evaluación: ([^\n]+)/g));
        this.data = {
            nombres, apellidos, rut, nacionalidad: (txt.match(/Nacionalidad\n?([A-Z]+)/i) || ["","CHILENA"])[1],
            tribunal, rit, delito, unidad: unidadRaw, region,
            tiempo_condena: (txt.match(/Tiempo condena\s*:?\s*([^\n]+)/i) || ["","S/I"])[1].trim(),
            fecha_inicio: findDate("Fecha inicio"), fecha_termino: findDate("Fecha termino"),
            min_lib: findDate("Fecha mínima libertad"), min_per: findDate("Fecha mínima permiso"),
            conducta_actual: conductaMatches.length > 0 ? conductaMatches[0][1].trim() : "SIN CALIFICACIÓN"
        };
        this.renderCard();
    },

    renderCard: function() {
        const d = this.data;
        document.getElementById('output').innerHTML = `
            <button class="btn-reset" onclick="app.reset()"><i class="fas fa-trash-alt"></i></button>
            <div class="id-header"><i class="fas fa-user-circle fa-2x text-secondary"></i><div><span class="label-text">INTERNO</span><span class="value-text" style="color:#6c5ce7;">${d.rut}</span></div></div>
            <div class="data-row"><span class="label-text">NOMBRE</span><span class="value-text">${d.nombres} ${d.apellidos}</span></div>
            <div class="data-row"><span class="label-text">UNIDAD</span><span class="value-text">${d.unidad}</span><div style="font-size:0.75rem; color:#fd79a8; font-weight:600;">${d.region}</div></div>
            <div class="row data-row"><div class="col-6"><span class="label-text">RIT</span><span class="value-text value-highlight">${d.rit}</span></div><div class="col-6"><span class="label-text">CONDUCTA</span><span class="value-text value-good">${d.conducta_actual}</span></div></div>
            <div class="data-row"><span class="label-text">TRIBUNAL</span><div class="value-text" style="font-size:0.9rem;">${d.tribunal}</div></div>
            <div class="row mt-3 pt-3 border-top"><div class="col-6"><span class="label-text">INICIO</span><span class="value-text">${d.fecha_inicio}</span></div><div class="col-6"><span class="label-text">TÉRMINO</span><span class="value-text">${d.fecha_termino}</span></div></div>
        `;
        document.getElementById('uploadState').style.display = 'none';
        document.getElementById('cardState').style.display = 'block';
    },

    reset: function() { location.reload(); },

    action: function(type) {
        if(!this.data.rut) return alert("Carga un PDF.");
        const d = this.data; const ab = document.getElementById('abogadoInput').value; localStorage.setItem('defensor_name', ab);
        const hoy = this.fmtDate(new Date().toLocaleDateString('es-CL'));
        let txt = "";

        if (type === 'cautela') {
            txt = `EN LO PRINCIPAL: CAUTELA DE GARANTÍAS; EN EL PRIMER OTROSÍ: SE OFICIE A GENCHI; EN EL SEGUNDO OTROSÍ: SE OFICIE A GENCHI; EN EL TERCER OTROSÍ: SOLICITA COMPARECENCIA POR VIA REMOTA; EN EL CUATRO OTROSÍ: FORMA DE NOTIFICACIÓN.\n\nJUZGADO DE GARANTÍA DE ${d.tribunal.toUpperCase()}\n\n${ab.toUpperCase()}, abogado, Defensor Público Penitenciario, en representación de ${d.nombres} ${d.apellidos}, Cédula Nacional de Identidad Nº ${d.rut}, condenado, actualmente privado de libertad en el ${d.unidad}, en causa RIT ${d.rit}, a SS respetuosamente digo:\n\nQue en virtud de lo prescrito en los artículos 6º, 7º y 73 de nuestra Constitución Política de la República, en concordancia con lo expresado en los artículos 14 letra f) del Código Orgánico de Tribunales y 10, 466 y siguientes del Código Procesal Penal; vengo en solicitar se cite a audiencia de cautela de garantías en etapa de ejecución de la pena, para que conforme con los hechos que a continuación se exponen, se adopten las medidas necesarias para resguardar los derechos constitucionales afectados de mi representado, en concreto su derecho a la integridad física y psíquica.\n\nQue mi representado actualmente se encuentra cumpliendo condena, en el ${d.unidad}.\nQue, en la entrevista sostenida con el interno, señaló que se encuentra aislado en el módulo 91 (celdas de aislamiento). Indica que en la actualidad se encuentra con segmentación agotada, luego de ser clasificado en diversos módulos a los que no pudo ingresar por mantener problemas con la población penal, entre ellos, los módulos 51, 52, 53, 54, 55, 56, 57, 58, 41, 42, 43, 44, 45 y 46.\nAsí las cosas, ha debido permanecer en el módulo 91, módulo que solo tiene celdas de aislamiento para el cumplimiento de las sanciones que impone Genchi a los internos por faltas al régimen interno. Por lo que, su permanencia en dicho módulo constituye una irregularidad motivada porque se encuentra con segmentación agotada. \nPara superar la situación de aislamiento que sufre, solicitamos que sea trasladado a alguno de los siguientes penales: PUERTO MONTT, VALDIVIA, BIOBIO o TEMUCO.\n\nQue, por los antecedentes expuestos, estimando que la integridad física y psíquica del amparado se encuentra en riesgo, y en virtud de lo establecido en el art. 19 Nº 1 de la Constitución Política de la República en relación al art. 10 del Código Procesal Penal, vengo en solicitar se tomen las medidas necesarias para salvaguardar la integridad física y psíquica del sentenciado; solicitando se fije audiencia de cautela de garantías a fin de que S.S. disponga entre otras medidas, que debe ser trasladado de unidad penal.\n\nPOR TANTO,\nA US. PIDO: Acceder a lo solicitado, fijando audiencia de cautela de garantías en etapa de ejecución de la pena.\n\nPRIMER OTROSÍ: A fin de una adecuada resolución de la petición de la defensa, solicito se oficie al Departamento de Control Penitenciario de Gendarmería de Chile para que informe acerca de la factibilidad del traslado del interno al penal de PUERTO MONTT, VALDIVIA, BIOBIO o TEMUCO. En caso de que no fuese factible el traslado a dichos penales, informe los penales que serían alternativas para trasladarlo como medida de seguridad.\n\nSEGUNDO OTROSÍ: Solicito a SS ordene oficiar a Gendarmería de Chile, Centro de Cumplimiento Penitenciario de ${d.unidad}, a fin de que remita:\na-. Informe sobre los hechos referidos en lo principal de esta presentación.\n\nTERCER OTROSÍ: Que, atendido a que el penado actualmente cumple condena en el penal de ${d.unidad}, en mi calidad de defensor público penitenciario de la Región de Coquimbo, se me ha encomendado su representación en la presente causa, razón por la cual, SOLICITO A US., que se me autorice para comparecer vía remota a la audiencia de cautela de garantías que hemos solicitado en lo principal de esta presentación, toda vez que por mis funciones me encuentro radicado en la ciudad de Coquimbo; todo de conformidad con lo dispuesto en el artículo 107 bis del Código Orgánico de Tribunales.\n\nCUARTO OTROSÍ: Solicito a SS, que las resoluciones recaídas sobre la presente solicitud y las posteriores de estos autos, sean notificados a esta parte al correo electrónico gabriel.calderon@dpp.cl`;

        } else if (type === 'penamixta') {
            txt = `EN LO PRINCIPAL: SOLICITA REMISIÓN DE OFICIOS QUE INDICA PARA OTORGAMIENTO DE PENA MIXTA. \nEN EL PRIMER OTROSÍ: SOLICITA INCORPORACIÓN DEL EXTRACTO AL SIAJG Y COPIA.\nEN EL SEGUNDO OTROSÍ: SEÑALA FORMA DE NOTIFICACIÓN.\n\nJUZGADO ${d.tribunal.toUpperCase()}\n\n${ab.toUpperCase()}, Defensor Público Penitenciario, en representación de ${d.nombres} ${d.apellidos}, cédula nacional de identidad ${d.rut}, condenado de esta causa, RIT ${d.rit} a SS. Respetuosamente digo:\n\nMi representado cumple actualmente una pena de “${d.tiempo_condena}”. El condenado comenzó a cumplir con fecha ${this.fmtDate(d.fecha_inicio)} y finaliza en ${this.fmtDate(d.fecha_termino)}, en virtud de sentencia condenatoria impuesta en estos autos, encontrándose actualmente recluido en ${d.unidad}.\n\nReuniendo mi representado los requisitos legales para que se le conceda la pena mixta, regulada en el artículo 33 de la Ley 18.216, modificada mediante Ley 20.603, vengo en solicitar a SS. disponer se despachen los siguientes oficios a las instituciones que se indican y en los siguientes términos:\n\nI.- DIRECCION REGIONAL DE GENDARMERÍA DE CHILE, ${d.region}, ordenando que se evacué informe relativo a la factibilidad técnica del uso de monitoreo telemático.\n\nII.- CENTRO DE REINSERCION SOCIAL, correspondiente al ${d.unidad} dependiente de Gendarmería de Chile, a fin de que emita informe técnico favorable que permita orientar sobre los factores de riesgo de reincidencia a fin de conocer las posibilidades del condenado para reinsertarse adecuadamente en la sociedad, debiendo incorporar los antecedentes sociales y las características de personalidad del condenado y una propuesta de plan de intervención individual a cumplirse en libertad.\n\nSolicito respetuosamente a S.S., Se ordene al CRS respectivo, que recabe al efecto desde la unidad penitenciaria, el plan de intervención individual elaborado respecto del condenado, así como los antecedentes psicosociales que obran al interior del penal a su respecto, la participación del interno en cursos, talleres, escuela y/o trabajo, que den cuenta de las actuales motivaciones de resocialización y reinserción del interno.\n\nIII.- CENTRO DE CUMPLIMIENTO PENITENCIARIO CORRESPONDIENTE A LA UNIDAD PENAL ${d.unidad} a fin de que informe: \n1) Sobre la fecha de inicio y término de la pena impuesta a mi representado(a), así como la fecha en que conforme a dichos registros puede postular a la pena sustitutiva por haber cumplido más de un tercio de la pena corporal impuesta. \n2) Sobre el Comportamiento de ${d.nombres} ${d.apellidos} RUT: ${d.rut} de conformidad con lo dispuesto en el Decreto Supremo N° 2442 de 1926 del Ministerio de Justica, Reglamento de la Ley de Libertad Condicional y de la calificación de su conducta desde que ingresó a cumplir la pena de autos.\n\nPOR TANTO, en mérito de lo expuesto y de lo dispuesto en las disposiciones legales invocadas, RUEGO A US. Acceder a lo solicitado.\n\nPRIMER OTROSÍ: A fin de verificar que mi representado(a) no ha sido condenado por otro crimen o simple delito, solicito a S.S ordene la incorporación a la carpeta judicial del extracto de filiación y antecedentes de ${d.nombres} ${d.apellidos} cédula nacional de identidad número ${d.rut} y se me otorgue copia de este.\n\nSEGUNDO OTROSI: De conformidad con lo prescrito en el artículo 31 del Código Procesal Penal, señalo como forma especial de notificación, el correo electrónico penitenciarioiv@dpp.cl solicitando que las resoluciones recaídas en la presente causa me sean notificadas a dicha cuenta de correo electrónico.`;

        } else if (type === 'abonos') {
            txt = `EN LO PRINCIPAL: SOLICITA OFICIO Y CERTIFICACION QUE INDICA\nOTROSÍ: SEÑALA FORMA DE NOTIFICACIÓN.\n\n[AVISO: PRESENTAR EN CAUSA EN LA QUE SE BUSCA EL ABONO - COMPLETAR DATOS FALTANTES]\nJUZGADO: ____________________\n\n${ab.toUpperCase()}, Defensor Público Penitenciario de la ${d.region}, domiciliado en Avenida Waldo Alcalde N°640 casa 25, Comuna de Coquimbo, en representación de ${d.nombres} ${d.apellidos}, cédula nacional de identidad ${d.rut}, en causa RIT: ___________, a SS. muy respetuosamente digo:\n\nMi representado cumple actualmente una pena de “${d.tiempo_condena}”, por condena del ${d.tribunal}, RIT ${d.rit}.-\nEl condenado comenzó a cumplir ${this.fmtDate(d.fecha_inicio)}, y finaliza en ${this.fmtDate(d.fecha_termino)}, encontrándose actualmente recluido en el establecimiento penitenciario de la ciudad de ${d.unidad}.\n\nCon el objeto de verificar eventual procedencia de abono heterogéneo desde esta causa a la que actualmente purga, y comprobar si se realizaron o no los abonos de días que mi representado estuvo en prisión preventiva en la presente causa, solicito a SS. lo siguiente:\n\n1. Ofíciese al establecimiento penitenciario, a fin de que se informe el total de días que cumplió en prisión preventiva el imputado ${d.nombres} ${d.apellidos}, C.I. ${d.rut}, en causa RUC ________ RIT _______ de este juzgado, y si estos días se encuentran abonados.\n2. Certifique el funcionario que corresponda la forma de término de la presente causa.\n3. Certifique medidas cautelares personales que existieron en esta causa, fecha de inicio y termino.\n\nPOR TANTO, RUEGO A SS. ACCEDER A LO SOLICITADO.\nOTROSI: De conformidad con lo prescrito en el artículo 31 del Código Procesal Penal, señalo como forma especial de notificación, los correos electrónicos penitenciarioiv@dpp.cl y gabriel.calderon@dpp.cl , solicitando que las resoluciones recaídas en la presente causa me sean notificadas a dicha cuenta de correo electrónico.`;

        } else if (type === 'trasladojuez') {
            txt = `EN LO PRINCIPAL: SOLICITUD QUE INDICA.\nEN EL PRIMER OTROSÍ: FORMA DE NOTIFICACIÓN.\nEN EL SEGUNDO OTROSÍ: TENGA PRESENTE.\n\nJUZGADO ${d.tribunal.toUpperCase()}\n\n${ab.toUpperCase()}, defensor Penitenciario, con domicilio en CALLE WALDO ALCALDE 640 CASA 25, comuna de Coquimbo, en representación del interno ${d.nombres} ${d.apellidos}, RUN: ${d.rut}, calidad procesal condenado, en esta causa RIT ${d.rit}, a SS., muy respetuosamente digo:\n\nHECHOS:\n1.- Que don ${d.nombres} ${d.apellidos} se encuentra actualmente cumpliendo condena con motivo de esta causa, en el establecimiento penitenciario “${d.unidad}”.- Actualmente purga condena total de : ${d.tiempo_condena}, por el delito: ${d.delito}.-\n2.- Que inicio su condena en ${this.fmtDate(d.fecha_inicio)} y finalizará con fecha ${this.fmtDate(d.fecha_termino)}.-\n3.- Que, según los registros de la autoridad penitenciaria, el interno ha mantenido una conducta de carácter "${d.conducta_actual}" de manera consistente durante el último período.\n4.- Que el interno no mantiene arraigo en la región de Coquimbo.\n5.- Que, esta defensa ha solicitado administrativamente a gendarmería en reiteradas oportunidades el traslado de unidad penal. De estas solicitudes presentadas directamente al alcaide del establecimiento “${d.unidad}”, no se ha obtenido respuesta, a pesar de haber insistido pidiendo cuenta como se acreditara. Es decir la defensa ha agotado la vía administrativa.\n6° Que, conforme a su situación penitenciaria, se solicita la evaluación de la factibilidad de su traslado hacia _________________________, a fin de asegurar mejores condiciones de cumplimiento.\n\nFUNDAMENTOS DE DERECHO:\nEl artículo 19 N°1 y N°7 de la CPR; El artículo 5 del Decreto Ley N° 321 y el artículo 53 del Reglamento de estatutos penitenciarios: “En resguardo del derecho a visitas, los condenados deberán permanecer recluidos preferentemente cerca de su lugar habitual de residencia”.\n\nPOR TANTO, SOLICITO A SS., OFICIAR A DEPARTAMENTO DE CONTROL PENITENCIARIO, GENDARMERÍA DE CHILE, para que informe sobre la factibilidad de materializar traslado del interno ${d.nombres} ${d.apellidos}, RUN: ${d.rut}, desde el establecimiento penitenciario “${d.unidad}” hacia alguna unidad penal de las REGIONES DE ______________.\n\nPRIMER OTROSÍ: Notificaciones a gabriel.calderon@dpp.cl y penitenciarioiv@dpp.cl\nSEGUNDO OTROSÍ: Tenga por acompañados documentos ofrecidos.`;

        } else if (type === 'suma') {
            txt = `EN LO PRINCIPAL: SOLICITUD QUE INDICA.\nOTROSÍ: FORMA DE NOTIFICACIÓN.\n\n${d.tribunal.toUpperCase()}\n\n${ab.toUpperCase()}, defensor Penitenciario, con domicilio en Waldo Alcalde 360, Coquimbo, en representación del interno ${d.nombres} ${d.apellidos}, RUT ${d.rut}, calidad procesal condenado, en esta causa RIT ${d.rit}, a SS., muy respetuosamente digo:\n\nHECHOS:\n1.- Que el interno se encuentra actualmente cumpliendo condena con motivo de esta causa, en el establecimiento penitenciario "${d.unidad}".- Actualmente purga una pena de ${d.tiempo_condena} por el delito de ${d.delito}.\n2.- Que inició su condena el ${this.fmtDate(d.fecha_inicio)} y finalizará con fecha ${this.fmtDate(d.fecha_termino)}.\n3.- Que, según los registros de la autoridad penitenciaria, el interno ${d.nombres} ${d.apellidos} mantiene una conducta "${d.conducta_actual}" de manera consistente durante el último período evaluado.`;

        } else if (type === 'modulo') {
            txt = `OFICIO Nº [AUTO]\nMAT. CAMBIO MODULO\nCoquimbo, ${hoy}\n\nEN LO PRINCIPAL: SOLICITO DEJAR SIN EFECTO CAMBIO DE MODULO SOLICITADO.\nOTROSI: PROPONE FORMA DE NOTIFICACION\n\nA: JEFE (A) DE COMPLEJO PENITENCIARIO ${d.unidad.toUpperCase()}.\nGENDARMERÍA DE CHILE\n\nDE: ${ab.toUpperCase()}\nDEFENSOR PENAL PUBLICO PENITENCIARIO\n${d.region}\n\nJunto con saludar cordialmente, vengo respetuosamente a solicitar cambio de módulo del siguiente interno:\nINTERNO: ${d.nombres} ${d.apellidos}\nRUT: ${d.rut}\nTRASLADO HACIA: MÓDULO N°______ . Tendría cupo.\n\nPOR TANTO, SOLICITO A USTED, que en mérito de lo expuesto y a las disposiciones contenidas en el Reglamento Penitenciario, pueda remitir respuesta de lo solicitado.\n\nOTROSI: Solicito respetuosamente a Ud., que se remita lo resuelto al siguiente correo electrónico: gabriel.calderon@dpp.cl, con copia a daniela.valdivia@dpp.cl o en su defecto, se notifique de conformidad a lo dispuesto en art. 46 de la Ley 19.880.`;

        } else if (type === 'salud') {
            txt = `OFICIO N° [AUTO]\nMAT.: ATENCIÓN MÉDICA\nCoquimbo, ${hoy}\n\nEN LO PRINCIPAL: SOLICITA ATENCIÓN MÉDICA PARA INTERNO QUE INDICA.\nOTROSÍ: PROPONE FORMA DE NOTIFICACIÓN.\n\nSR. ALCAIDE ${d.unidad.toUpperCase()}\n\n${ab.toUpperCase()}, Defensor Penal Público Penitenciario de la ${d.region}, en representación de don ${d.nombres} ${d.apellidos}, cédula de identidad N°${d.rut}, a Ud., respetuosamente digo:\n\nQue, en entrevista sostenida en la unidad penal con el condenado antes individualizado, éste solicitó la intervención de la Defensoría Penal Pública Penitenciaria, a fin de gestionar ante Gendarmería de Chile la atención médica correspondiente, debido a que refiere presentar síntomas asociados a [INDICAR SÍNTOMAS].\n\nPOR TANTO, SOLICITO A UD., tener bien adoptar las medidas que correspondan a fin de que al interno se le otorgue la atención médica necesaria para evaluar y tratar oportunamente su condición.\n\nOTROSÍ: Notificaciones al correo gabriel.calderon@dpp.cl con copia a daniela.valdivia@dpp.cl.`;

        } else if (type === 'laboral') {
            txt = `OFICIO N° [AUTO]\nMAT.: INGRESO ACTIVIDAD LABORAL.\nCoquimbo, ${hoy}\n\nEN LO PRINCIPAL: SOLICITA INGRESO A ACTIVIDAD LABORAL.\nOTROSÍ: PROPONE FORMA DE NOTIFICACIÓN\n\nSR. ALCAIDE ${d.unidad.toUpperCase()}\n\n${ab.toUpperCase()}, Defensor Penal Público Penitenciario, en representación de don(a) ${d.nombres} ${d.apellidos}, cédula de identidad N°${d.rut}, respetuosamente digo:\n\nQue, por medio del presente, vengo en solicitar que se reconozca a mi representado como TRABAJADOR INDEPENDIENTE en el área de [INDICAR ÁREA], atendido que, según lo manifestado en entrevista, mantiene en su posesión diversas herramientas que le permitirían desempeñar dicha labor.\nMi representado tiene el sincero deseo de utilizar el tiempo de cumplimiento de condena desempeñando una actividad laboral productiva.\n\nPOR TANTO, solicito a usted se sirva emitir pronunciamiento favorable sobre esta solicitud conforme al art. 61 del Reglamento Penitenciario.\n\nOTROSÍ: Notificaciones al correo gabriel.calderon@dpp.cl con copia a daniela.valdivia@dpp.cl.`;

        } else if (type === 'talleres') {
            txt = `OFICIO N° [AUTO]\nMAT.: ACCESO A TALLERES Y CURSOS SEGÚN P.I.I.\nCoquimbo, ${hoy}\n\nEN LO PRINCIPAL: SOLICITA INGRESO A TALLERES Y CURSOS SEGÚN SU PII.\nOTROSÍ: PROPONE FORMA DE NOTIFICACIÓN\n\nSR.(A) ALCAIDE ${d.unidad.toUpperCase()}\n\n${ab.toUpperCase()}, Defensor Penal Público Penitenciario, en representación de ${d.nombres} ${d.apellidos}, cédula de identidad N°${d.rut}, me dirijo a Ud. respetuosamente:\n\nEl interno ha solicitado a esta Defensoría Penitenciaria la gestión para SER CONSIDERADO A PARTICIPAR EN TALLERES Y CURSOS DE REINSERCIÓN SOCIAL, conforme a lo indicado en su PLAN DE INTERVENCIÓN INDIVIDUAL. Esta solicitud se sustenta en los artículos 58, 60 y 92 del Reglamento de Establecimientos Penitenciarios.\n\nOTROSÍ: Notificaciones al correo gabriel.calderon@dpp.cl con copia a daniela.valdivia@dpp.cl.`;

        } else if (type === 'rebaja') {
            txt = `OFICIO N° [AUTO]\nMAT.: SOLICITUD QUE INDICA\nCoquimbo, ${hoy}\n\nPARA : \tSR.(A) ALCAIDE ${d.unidad.toUpperCase()}\n\tGENDARMERIA DE CHILE\n\nDE : \t${ab.toUpperCase()}\n\tDEFENSOR PENAL PÚBLICO PENITENCIARIO,\n\t${d.region}.\n\nEste defensor penal público ha tomado conocimiento del caso de ${d.nombres} ${d.apellidos}, cédula nacional de identidad número ${d.rut}, actualmente privado de libertad en el establecimiento penitenciario de ${d.unidad}.\n\nSobre el particular, vengo en solicitar a Ud. se sirva informar si el interno precedentemente individualizado le ha sido otorgado el beneficio de rebaja de condena contemplado en la normativa vigente. En caso de que no hubiere accedido a dicho beneficio, solicito se indiquen los motivos.\n\nPor último, solicito que lo resuelto sea informado a esta Defensoría al correo daniela.valdivia@dpp.cl y gabriel.calderon@dpp.cl`;

        } else if (type === 'resumen') {
            txt = `El interno ${d.nombres} ${d.apellidos}, RUT ${d.rut}, de nacionalidad ${d.nacionalidad}, se encuentra recluido en ${d.unidad} cumpliendo una condena de ${d.tiempo_condena} por el delito de ${d.delito} (Causa RIT ${d.rit} - ${d.tribunal}). Su cumplimiento inició el ${this.fmtDate(d.fecha_inicio)} y tiene fecha de término para el ${this.fmtDate(d.fecha_termino)}. Registra fecha mínima para permiso de salida el ${this.fmtDate(d.min_per)} y para libertad condicional el ${this.fmtDate(d.min_lib)}. Actualmente presenta una conducta calificada como ${d.conducta_actual}.`;
       
        } else if (type === 'oficio') {
            txt = `OFICIO N°: [AUTO]\nAsunto: Solicitud de Información / Beneficio\n\nPARA : \tSR.(A) ALCAIDE ${d.unidad.toUpperCase()}\n\tGENDARMERIA DE CHILE\n\nDE : \t${ab.toUpperCase()}\n\tDEFENSOR PENAL PÚBLICO\n\n${ab}, defensor penal público penitenciario, en representación de don(a) ${d.nombres} ${d.apellidos}, ${d.nacionalidad}, C.I. ${d.rut}, quien actualmente cumple condena en ${d.unidad}, por causa RIT: ${d.rit} del ${d.tribunal}, a UD. respetuosamente digo:\n\nPor la presente, solicito a UD. informar situación de...`;
        }

        document.getElementById('docContent').value = txt;
        let filename = type.charAt(0).toUpperCase() + type.slice(1) + "_" + d.apellidos.replace(/\s+/g, '_');
        document.getElementById('docName').value = filename;
        document.getElementById('downloadForm').submit();
    },

    fmtDate: function(str) {
        if(!str || str.length < 10) return str;
        const meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
        const parts = str.split('/');
        if(parts.length !== 3) return str;
        return `${parts[0]} de ${meses[parseInt(parts[1])-1]} de ${parts[2]}`;
    }
};
app.init();
</script>
</body>
</html>
