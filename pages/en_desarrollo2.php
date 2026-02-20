<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Opcional: Verificar sesión si quieres que solo usuarios logueados lo vean
include_once __DIR__ . '/../partials/header.php'; 
?>

<main class="min-h-[80-screen] flex items-center justify-center bg-slate-50 py-20">
    <div class="max-w-md w-full text-center space-y-8 px-6">
        
        <div class="relative inline-block">
            <div class="absolute -inset-1 bg-gradient-to-r from-sky-400 to-indigo-500 rounded-full blur opacity-25"></div>
            <div class="relative bg-white p-4 rounded-[2.5rem] shadow-2xl border border-slate-100">
                <img src="../img/gato.gif" alt="Trabajando en ello" class="w-64 h-64 object-cover rounded-3xl mx-auto">
            </div>
        </div>

        <div class="space-y-3">
            <h1 class="text-3xl font-black text-slate-800 tracking-tight italic">¡Miau! Estamos trabajando...</h1>
            <p class="text-slate-500 font-medium">Esta sección aún está bajo supervisión felina. Pronto estará disponible para ayudarte con tu contabilidad.</p>
        </div>

        <div class="pt-4">
            <a href="javascript:history.back()" 
               class="inline-flex items-center gap-2 px-8 py-3 bg-sky-600 text-white font-bold rounded-2xl hover:bg-sky-700 shadow-xl shadow-sky-200 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver atrás
            </a>
        </div>

    </div>
</main>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>