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
            
            <a href="en_desarrollo.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-sky-600 group-hover:text-white transition-all shadow-lg shadow-sky-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Inventario (NO APLICA)</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Control total de maquinaria: registro de series, marcas y el nuevo campo de valor comercial.</p>
            </a>

            <a href="clientes.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-lg shadow-emerald-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2-2z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Contribuyentes</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Gestión de empresas, representantes legales y claves.</p>
            </a>

            <a href="representantes.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-violet-100 text-violet-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-violet-600 group-hover:text-white transition-all shadow-lg shadow-violet-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Representantes</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Gestión de representantes legales y sus empresas asociadas.</p>
            </a>

        </div>
    </div>
</main>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
