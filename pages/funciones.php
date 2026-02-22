<?php 
session_start();
include_once __DIR__ . '/../partials/header.php'; 

// Protección: Solo empleados entran aquí
if (!isset($_SESSION['es_empleado']) || $_SESSION['es_empleado'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-12">
            <span class="text-sky-600 font-bold uppercase tracking-widest text-xs font-black">Panel de Administración</span>
            <h1 class="text-5xl font-black text-slate-900 tracking-tighter mt-2">Funciones del Sistema</h1>
            <p class="text-slate-500 mt-2 text-lg">Bienvenido al centro operativo de ArriendoPro.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <a href="cobranza.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-sky-600 group-hover:text-white transition-all shadow-lg shadow-sky-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Cobranza</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Generación y seguimiento de cobros mensuales.</p>
            </a>

            <a href="clientes.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-lg shadow-emerald-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-7h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Contribuyentes</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Gestión de empresas, razones sociales y datos de contacto.</p>
            </a>

            <a href="representantes.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-lg shadow-indigo-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Representantes</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Directorio de apoderados legales y seguimiento de sus carteras de clientes.</p>
            </a>

        </div>
    </div>
</main>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
