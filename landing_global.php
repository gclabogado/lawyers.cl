<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lawyers.cl | Suite Legal Inteligente</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .hero-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background-image: radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.1) 0%, transparent 20%);
            pointer-events: none;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">L</div>
                <span class="font-bold text-xl tracking-tight text-slate-900">LAWYERS<span class="text-indigo-600">.CL</span></span>
            </div>
            <div>
                <a href="index.php?page=acceso" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-full text-sm font-semibold transition shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-right-to-bracket me-2"></i> Acceso Clientes
                </a>
            </div>
        </div>
    </nav>

    <header class="hero-bg text-white pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
        <div class="container mx-auto max-w-6xl">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <div class="text-center lg:text-left space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass-card text-indigo-300 text-xs font-bold uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Sistema Operativo 2026
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-extrabold leading-tight tracking-tight">
                        Menos Burocracia.<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">Más Defensa.</span>
                    </h1>
                    <p class="text-lg text-slate-400 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        La plataforma definitiva para abogados penalistas. Centraliza tus causas, automatiza escritos y calcula penas con precisión matemática. 
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                        <a href="index.php?page=acceso" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-xl shadow-indigo-900/50 transition transform hover:-translate-y-1 flex items-center justify-center gap-3">
                            <i class="fab fa-google"></i> Comenzar Gratis
                        </a>
                        <a href="#features" class="glass-card hover:bg-white/10 text-white px-8 py-4 rounded-xl font-bold text-lg transition flex items-center justify-center">
                            Explorar Herramientas
                        </a>
                    </div>
                    <p class="text-xs text-slate-500 pt-2">Registro gratuito con cuenta Google • Sin tarjeta de crédito</p>
                </div>

                <div class="relative hidden lg:block">
                    <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur opacity-30 animate-pulse"></div>
                    <div class="relative bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-6">
                        <div class="flex items-center gap-4 mb-6 border-b border-slate-800 pb-4">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <div class="ml-auto text-xs text-slate-500 font-mono">dashboard.php</div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="w-1/3 h-24 bg-slate-800 rounded-lg animate-pulse"></div>
                                <div class="w-1/3 h-24 bg-slate-800 rounded-lg animate-pulse delay-75"></div>
                                <div class="w-1/3 h-24 bg-slate-800 rounded-lg animate-pulse delay-150"></div>
                            </div>
                            <div class="h-40 bg-slate-800 rounded-lg opacity-50"></div>
                            <div class="space-y-2">
                                <div class="h-4 bg-slate-800 rounded w-3/4"></div>
                                <div class="h-4 bg-slate-800 rounded w-1/2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Tu Oficina Digital Blindada</h2>
                <p class="text-slate-600">
                    Al registrarte con tu cuenta Google, accedes inmediatamente a un entorno privado y seguro con herramientas diseñadas para la litigación real.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                
                <div class="feature-card bg-slate-50 p-8 rounded-2xl border border-slate-100">
                    <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Gestión de Casos (CRM)</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Olvídate de las carpetas físicas. Crea fichas digitales de tus internos, monitorea tiempos de condena y fechas clave de beneficios intrapenitenciarios.
                    </p>
                    <ul class="text-sm text-slate-500 space-y-2">
                        <li><i class="fas fa-check text-green-500 me-2"></i> Base de datos privada</li>
                        <li><i class="fas fa-check text-green-500 me-2"></i> Alertas de vencimiento</li>
                    </ul>
                </div>

                <div class="feature-card bg-slate-50 p-8 rounded-2xl border border-slate-100">
                    <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Redacción IA</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Genera escritos complejos en segundos. Selecciona tu cliente, elige la plantilla (Cautela, Libertad, Traslado) y el sistema redacta por ti.
                    </p>
                    <ul class="text-sm text-slate-500 space-y-2">
                        <li><i class="fas fa-check text-green-500 me-2"></i> Plantillas probadas</li>
                        <li><i class="fas fa-check text-green-500 me-2"></i> Descarga inmediata</li>
                    </ul>
                </div>

                <div class="feature-card bg-slate-50 p-8 rounded-2xl border border-slate-100">
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Matemática Penal</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Calculadoras de prescripción, abonos y cómputo de penas. Herramientas exactas para fundamentar tus alegatos con números sólidos.
                    </p>
                    <ul class="text-sm text-slate-500 space-y-2">
                        <li><i class="fas fa-check text-green-500 me-2"></i> Sin errores humanos</li>
                        <li><i class="fas fa-check text-green-500 me-2"></i> Actualizado a la norma</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <section class="py-20 bg-slate-900 text-white">
        <div class="container mx-auto px-6 text-center">
            <div class="w-16 h-1 bg-indigo-500 mx-auto mb-8 rounded-full"></div>
            <h2 class="text-3xl font-bold mb-6">El Proyecto Lawyers.cl</h2>
            <p class="text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                Nacimos de la necesidad de modernizar la práctica penal en Chile. <br>
                Nuestra misión es democratizar el acceso a herramientas de gestión de alto nivel, permitiendo que los abogados dediquen su tiempo a la estrategia, no a la burocracia.
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto border-t border-slate-800 pt-10">
                <div>
                    <div class="text-3xl font-bold text-white mb-1">100%</div>
                    <div class="text-xs text-indigo-400 uppercase tracking-widest">Privacidad</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white mb-1">24/7</div>
                    <div class="text-xs text-indigo-400 uppercase tracking-widest">Disponibilidad</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white mb-1">SaaS</div>
                    <div class="text-xs text-indigo-400 uppercase tracking-widest">Tecnología</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white mb-1">CLT</div>
                    <div class="text-xs text-indigo-400 uppercase tracking-widest">Adaptado Chile</div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-slate-200 pt-12 pb-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <span class="font-bold text-lg text-slate-900">LAWYERS<span class="text-indigo-600">.CL</span></span>
                    <p class="text-slate-500 text-sm mt-1">Ingeniería Legal Aplicada.</p>
                </div>
                <div class="flex gap-6">
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition"><i class="fab fa-linkedin fa-lg"></i></a>
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition"><i class="fab fa-instagram fa-lg"></i></a>
                </div>
            </div>
            <div class="border-t border-slate-100 mt-8 pt-8 text-center md:text-left text-xs text-slate-400">
                &copy; 2026 Lawyers.cl. Todos los derechos reservados. | <a href="#" class="hover:underline">Privacidad</a> | <a href="#" class="hover:underline">Términos</a>
            </div>
        </div>
    </footer>

</body>
</html>
