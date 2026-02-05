<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juris-Engine | Descargador de Fichas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --primary: #0f172a; 
            --accent: #2563eb; 
            --bg: #f8fafc; 
        }
        body { 
            background-color: var(--bg); 
            font-family: 'Inter', sans-serif; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card-download { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
            background: #ffffff;
            max-width: 450px;
            width: 90%;
            padding: 40px;
            text-align: center;
        }
        .brand-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 20px;
        }
        h2 { 
            color: var(--primary);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            font-weight: 600;
            text-align: center;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .btn-download {
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 700;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .btn-download:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }
        .error-msg {
            margin-top: 15px;
            font-size: 0.85rem;
            color: #dc2626;
            font-weight: 600;
            min-height: 20px;
        }
        .footer-note {
            margin-top: 30px;
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }
        .alert-contingencia {
            background-color: #fff7ed;
            border: 1px solid #ffedd5;
            color: #9a3412;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="card-download">
        <div class="brand-icon">
            <i class="fas fa-file-pdf"></i>
        </div>
        <h2>Juris-Ficha</h2>
        <p class="subtitle">Recuperación técnica de Fichas de Entrevistas para el Defensor Penitenciario.</p>
        
        <div class="alert-contingencia">
            <i class="fas fa-exclamation-triangle me-1"></i> Protocolo de contingencia ante fallos en SIGDP.
        </div>

        <div class="form-group">
            <label class="d-block text-start label-mini mb-2" style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Número de Petición</label>
            <input type="number" id="numeroArchivo" class="form-control" placeholder="Ej: 145982" required>
        </div>

        <button class="btn-download" onclick="descargarFicha()">
            <i class="fas fa-cloud-download-alt"></i> DESCARGAR DOCUMENTO
        </button>

        <div id="mensajeError" class="error-msg"></div>

        <div class="footer-note">
            <i class="fas fa-shield-alt"></i> Uso Exclusivo Institucional
        </div>
    </div>

    <script>
        async function descargarFicha() {
            const numero = document.getElementById('numeroArchivo').value;
            const mensajeError = document.getElementById('mensajeError');
            const btn = document.querySelector('.btn-download');
            
            mensajeError.textContent = '';

            if (!numero) {
                mensajeError.textContent = "Ingrese un número de ficha válido.";
                return;
            }

            // Cambiamos el estado del botón
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PROCESANDO...';
            btn.style.opacity = '0.7';
            btn.disabled = true;

            const url = `https://sigdp.dpp.cl/WebDefensoria/penitenciario/BajarArchivo.jsp?archivo=archivos/peticion_(${numero}).pdf`;

            try {
                // Debido a políticas de CORS en navegadores, el fetch directo podría fallar si el servidor no lo permite.
                // Sin embargo, el método de creación de link directo suele saltarse esta restricción para descargas.
                
                const link = document.createElement('a');
                link.href = url;
                link.target = "_blank"; // Abrir en nueva pestaña por seguridad
                link.download = `ficha_entrevista_${numero}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                setTimeout(() => {
                    btn.innerHTML = originalContent;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                }, 1000);

            } catch (error) {
                mensajeError.textContent = "Error de conexión con el servidor SIGDP.";
                btn.innerHTML = originalContent;
                btn.style.opacity = '1';
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
