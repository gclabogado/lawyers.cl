<?php
/**
 * LAWYERS.CL - SMART INTAKE v3.3 (FULL FORM - RECOVERY)
 * Motor optimizado para RUN y Tribunal (Lógica de líneas Genchi)
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['loggedin'])) { header('Location: index.php?page=acceso'); exit; }
require_once 'db.php';

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)$_SESSION['user_id'];
    
    $sql = "INSERT INTO internos (
        usuario_id, nombres, apellidos, rut, sexo, nacionalidad, delito, 
        fecha_inicio, fecha_termino, fecha_entrevista, juzgado, rit, ruc, 
        tiempo_condena, carcel, solicitudes, abogado, estado_procesal, 
        beneficios, observaciones, nivel_riesgo, prioridad, defensor_id, 
        fecha_cumplimiento_rebaja, fecha_min_libertad, fecha_min_permiso,
        contacto1_nombre, contacto1_parentesco, contacto1_telefono,
        contacto2_nombre, contacto2_parentesco, contacto2_telefono
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssssssssssssssssssssssssss", 
        $uid, $_POST['nombres'], $_POST['apellidos'], $_POST['rut'], $_POST['sexo'], 
        $_POST['nacionalidad'], $_POST['delito'], $_POST['fecha_inicio'], $_POST['fecha_termino'], 
        $_POST['fecha_entrevista'], $_POST['juzgado'], $_POST['rit'], $_POST['ruc'], 
        $_POST['tiempo_condena'], $_POST['carcel'], $_POST['solicitudes'], $_POST['abogado'], 
        $_POST['estado_procesal'], $_POST['beneficios'], $_POST['observaciones'], 
        $_POST['nivel_riesgo'], $_POST['prioridad'], $_POST['defensor_id'], 
        $_POST['fecha_rebaja'], $_POST['fecha_min_lib'], $_POST['fecha_min_per'],
        $_POST['c1_nombre'], $_POST['c1_parentesco'], $_POST['c1_tel'],
        $_POST['c2_nombre'], $_POST['c2_parentesco'], $_POST['c2_tel']
    );

    if ($stmt->execute()) {
        $message = ['type' => 'success', 'text' => '✅ Expediente de ' . $_POST['apellidos'] . ' guardado con éxito.'];
    } else {
        $message = ['type' => 'error', 'text' => '❌ Error DB: ' . $conn->error];
    }
    $stmt->close();
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] === 'success' ? 'success' : 'danger' ?> shadow-sm"><?= $message['text'] ?></div>
            <?php endif; ?>

            <div id="dropZone" style="border: 3px dashed #6366f1; background: #fff; border-radius: 20px; padding: 40px; text-align: center; cursor: pointer;" class="shadow-sm mb-4">
                <div style="font-size: 45px;">📄🤖</div>
                <h4 class="fw-bold">Smart Intake v3.3 Final</h4>
                <p class="text-muted">Extracción de RUN y Tribunal optimizada (Ficha Genchi)</p>
                <div id="scanStatus" style="display:none;" class="text-primary fw-bold">✨ ESCANEANDO TABLAS DE DATOS...</div>
                <input type="file" id="fileInput" accept="application/pdf" style="display:none;">
            </div>

            <form method="POST" id="formV3" class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-lg-5">
                    
                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">1. ANTECEDENTES PERSONALES</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><label class="small fw-bold">RUN / RUT</label><input type="text" name="rut" id="f_rut" class="form-control" required></div>
                        <div class="col-md-4"><label class="small fw-bold">Nombres</label><input type="text" name="nombres" id="f_nombres" class="form-control" required></div>
                        <div class="col-md-4"><label class="small fw-bold">Apellidos</label><input type="text" name="apellidos" id="f_apellidos" class="form-control" required></div>
                        <div class="col-md-3"><label class="small fw-bold">Género</label><select name="sexo" id="f_sexo" class="form-select"><option value="masculino">Masculino</option><option value="femenino">Femenino</option></select></div>
                        <div class="col-md-3"><label class="small fw-bold">Nacionalidad</label><input type="text" name="nacionalidad" id="f_nacionalidad" class="form-control"></div>
                        <div class="col-md-3"><label class="small fw-bold">Unidad Penal</label><input type="text" name="carcel" id="f_carcel" class="form-control"></div>
                        <div class="col-md-3"><label class="small fw-bold">Nivel Riesgo</label><input type="text" name="nivel_riesgo" id="f_riesgo" class="form-control"></div>
                    </div>

                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">2. DATOS DE LA CAUSA</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><label class="small fw-bold">Tribunal / Juzgado</label><input type="text" name="juzgado" id="f_juzgado" class="form-control fw-bold bg-light"></div>
                        <div class="col-md-3"><label class="small fw-bold">RIT</label><input type="text" name="rit" id="f_rit" class="form-control"></div>
                        <div class="col-md-3"><label class="small fw-bold">RUC</label><input type="text" name="ruc" id="f_ruc" class="form-control"></div>
                        <div class="col-md-8"><label class="small fw-bold">Delito(s)</label><input type="text" name="delito" id="f_delito" class="form-control"></div>
                        <div class="col-md-4"><label class="small fw-bold">Estado Procesal</label><input type="text" name="estado_procesal" id="f_estado_proc" class="form-control" value="CONDENADO"></div>
                    </div>

                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">3. CÓMPUTOS Y FECHAS</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><label class="small fw-bold">Fecha Inicio</label><input type="date" name="fecha_inicio" id="f_inicio" class="form-control"></div>
                        <div class="col-md-3"><label class="small fw-bold">Fecha Término</label><input type="date" name="fecha_termino" id="f_termino" class="form-control"></div>
                        <div class="col-md-3"><label class="small fw-bold">Fecha Rebaja</label><input type="date" name="fecha_rebaja" id="f_rebaja" class="form-control"></div>
                        <div class="col-md-3"><label class="small fw-bold">Mínima Libertad</label><input type="date" name="fecha_min_lib" id="f_min_lib" class="form-control"></div>
                        <div class="col-md-3"><label class="small fw-bold">Mínima Permiso</label><input type="date" name="fecha_min_per" id="f_min_per" class="form-control"></div>
                        <div class="col-md-6"><label class="small fw-bold">Tiempo Condena</label><input type="text" name="tiempo_condena" id="f_tiempo" class="form-control"></div>
                        <div class="col-md-3"><label class="small fw-bold">Fecha Entrevista</label><input type="date" name="fecha_entrevista" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    </div>

                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">4. GESTIÓN Y CONTACTOS</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><label class="small fw-bold">Abogado Asignado</label><input type="text" name="abogado" class="form-control"></div>
                        <div class="col-md-4"><label class="small fw-bold">Solicitudes</label><input type="text" name="solicitudes" class="form-control"></div>
                        <div class="col-md-4"><label class="small fw-bold">Beneficios</label><input type="text" name="beneficios" class="form-control"></div>
                        <div class="col-md-4"><label class="small fw-bold">Contacto 1 Nombre</label><input type="text" name="c1_nombre" class="form-control"></div>
                        <div class="col-md-4"><label class="small fw-bold">Parentesco</label><input type="text" name="c1_parentesco" class="form-control"></div>
                        <div class="col-md-4"><label class="small fw-bold">Teléfono</label><input type="text" name="c1_tel" class="form-control"></div>
                        <div class="col-md-12"><label class="small fw-bold">Observaciones / Notas</label><textarea name="observaciones" class="form-control" rows="2">Extracción automática Genchi v3.3</textarea></div>
                    </div>

                    <input type="hidden" name="prioridad" value="normal">
                    <input type="hidden" name="defensor_id" value="0">
                    <input type="hidden" name="c2_nombre" value=""><input type="hidden" name="c2_parentesco" value=""><input type="hidden" name="c2_tel" value="">

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow">GUARDAR EXPEDIENTE COMPLETO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const smartGenchi = {
    init() {
        const dz = document.getElementById('dropZone');
        const inp = document.getElementById('fileInput');
        dz.onclick = () => inp.click();
        inp.onchange = (e) => this.read(e.target.files[0]);
    },
    async read(file) {
        if(!file) return;
        document.getElementById('scanStatus').style.display = 'block';
        const buf = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument({data: buf}).promise;
        let txt = "";
        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const content = await page.getTextContent();
            txt += content.items.map(s => s.str).join("\n") + "\n";
        }
        this.extract(txt);
        document.getElementById('scanStatus').style.display = 'none';
        alert("📊 Extracción completada. Verifique el RUN y el Juzgado.");
    },
    extract(txt) {
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = (v || "").trim(); };
        const lines = txt.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        
        // --- 1. RUN (PRECISIÓN POR TABLA) ---
        // Buscamos "RUN:" y el valor que suele estar en la línea siguiente o misma línea
        const runMatch = txt.match(/RUN:\s*([0-9]{1,2}\.[0-9]{3}\.[0-9]{3}-[0-9Kk])/i);
        if(runMatch) {
            set("f_rut", runMatch[1]);
        } else {
            const idxRun = lines.findIndex(l => l.toUpperCase().startsWith("RUN:"));
            if(idxRun !== -1) set("f_rut", lines[idxRun+1] || lines[idxRun].split(':').pop());
        }

        // --- 2. TRIBUNAL (LÓGICA DE 1 LÍNEA SOBRE DELITOS) ---
        const idxDelitos = lines.findIndex(l => l.toUpperCase().startsWith("DELITOS:"));
        if(idxDelitos !== -1) {
            set("f_juzgado", lines[idxDelitos - 1].toUpperCase()); // Captura el tribunal 1 línea arriba
            set("f_delito", lines[idxDelitos].split(':').pop().toUpperCase());
        }

        // --- 3. DATOS DE LA CAUSA ---
        const block = (txt.match(/Datos de la Causa([\s\S]*?)Condenas/i) || ["",""])[1];
        if(block) {
            const rucB = (block.match(/Rol_ruc:\s*(\d+)/i) || ["",""])[1];
            const rucG = (block.match(/Gu[ií]ón:\s*(\w+)/i) || ["",""])[1];
            set("f_ruc", rucB ? `${rucB}-${rucG}` : "");
            set("f_rit", (block.match(/RIT tribunal:\s*([^\n]+)/i) || ["",""])[1]);
            set("f_estado_proc", (block.match(/Estado procesal.*?:\s*([^\n]+)/i) || ["","CONDENADO"])[1].toUpperCase());
        }

        // --- 4. DATOS PERSONALES ---
        const findVal = (label) => {
            const i = lines.findIndex(l => l.toUpperCase().includes(label.toUpperCase()));
            if (i === -1) return "";
            let val = lines[i].split(':').pop().trim();
            if (val === "" || val.toUpperCase() === label.toUpperCase()) val = lines[i+1] || "";
            return val;
        };

        set("f_nombres", findVal("Nombres").toUpperCase());
        set("f_apellidos", findVal("Apellidos").toUpperCase());
        set("f_nacionalidad", findVal("Nacionalidad").toUpperCase());
        set("f_carcel", findVal("Unidad").split('DNI')[0].trim());

        // --- 5. FECHAS (ISO) ---
        const toISO = (label) => {
            const m = txt.match(new RegExp(label + ".*?(\\d{2}/\\d{2}/\\d{4})", "i"));
            if (!m) return "";
            const [d, mon, y] = m[1].split('/'); return `${y}-${mon}-${d}`;
        };

        set("f_inicio", toISO("Fecha inicio"));
        set("f_termino", toISO("Fecha termino"));
        set("f_rebaja", toISO("Fecha cumplimiento rebaja"));
        set("f_min_lib", toISO("Fecha mínima libertad"));
        set("f_min_per", toISO("Fecha mínima permiso"));
        set("f_tiempo", (txt.match(/Tiempo condena\s*:?\s*([^\n]+)/i) || ["",""])[1]);
        set("f_riesgo", (txt.match(/Compromiso Delictual:\s*([A-Z]+)/i) || ["",""])[1]);

        if (txt.toLowerCase().includes("femenino")) set("f_sexo", "femenino");
    }
};
smartGenchi.init();
</script>
