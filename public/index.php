<?php 
session_start();
// Salimos de public para buscar los partials
include_once __DIR__ . '/../partials/header.php'; 
?>

<section class="relative bg-white py-20 px-6">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-12">
        
        <div class="flex-1 text-left space-y-6">
            <h1 class="text-6xl font-black text-slate-900 tracking-tighter leading-none">
                El equipo que necesitas, <br>
                <span class="text-sky-600">cuando lo necesitas.</span>
            </h1>
            <p class="text-slate-500 text-xl max-w-lg leading-relaxed">
                Soluciones de arriendo para construcción, eventos y proyectos industriales. Gestión rápida, segura y 100% digital.
            </p>
            
            <div class="flex flex-wrap gap-4 pt-4">
                <a href="/MiProyecto/pages/en_desarrollo.php" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                    Explorar Equipos
                </a>
                <a href="#como-funciona" class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition-all">
                    Saber más
                </a>
            </div>
        </div>

        <div class="flex-1 relative">
            <div class="w-full h-[400px] bg-sky-100 rounded-[3rem] overflow-hidden relative border-4 border-white shadow-2xl">
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-40 h-40 text-sky-300 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
            <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-3xl shadow-xl border border-slate-50">
                <p class="text-sky-600 font-black text-2xl">+50</p>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Equipos Listos</p>
            </div>
        </div>
    </div>
</section>

<section id="como-funciona" class="py-20 bg-slate-50 px-6">
    <div class="max-w-7xl mx-auto text-center">
        <h2 class="text-3xl font-black text-slate-900 mb-12 italic">Pasos para tu arriendo</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="space-y-4">
                <div class="text-5xl font-black text-sky-200">01</div>
                <h3 class="text-xl font-bold text-slate-800">Crea tu cuenta</h3>
                <p class="text-slate-500 text-sm">Regístrate en segundos con tus datos básicos para acceder a los precios.</p>
            </div>
            <div class="space-y-4">
                <div class="text-5xl font-black text-sky-200">02</div>
                <h3 class="text-xl font-bold text-slate-800">Elige el equipo</h3>
                <p class="text-slate-500 text-sm">Busca en nuestro catálogo el equipo ideal para tu proyecto actual.</p>
            </div>
            <div class="space-y-4">
                <div class="text-5xl font-black text-sky-200">03</div>
                <h3 class="text-xl font-bold text-slate-800">Arrienda</h3>
                <p class="text-slate-500 text-sm">Gestiona el tiempo de uso y nosotros nos encargamos del resto.</p>
            </div>
        </div>
    </div>
</section>

<?php 
include_once __DIR__ . '/../partials/footer.php'; 

?>
