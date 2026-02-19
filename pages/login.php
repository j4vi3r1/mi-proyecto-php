<?php include_once __DIR__ . '/../partials/header.php'; ?>

<main class="min-h-screen flex items-center justify-center bg-slate-50 px-4 py-12">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200 border border-slate-100 overflow-hidden">
            <div class="bg-sky-600 p-8 text-white text-center">
                <h2 class="text-3xl font-bold">Bienvenido</h2>
                <p class="opacity-80">Ingresa tus credenciales para acceder</p>
            </div>

            <form action="../app/procesar_login.php" method="POST" class="p-8 space-y-6">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Correo Electrónico</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                        </span>
                        <input type="email" name="correo" required placeholder="tu@correo.com" 
                               class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none transition-all">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Contraseña (RUT)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input type="password" name="rut" required placeholder="Ingresa tu RUT" 
                               class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none transition-all">
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-sky-600 text-white rounded-2xl font-bold text-lg hover:bg-sky-700 shadow-lg shadow-sky-100 transition-all transform hover:-translate-y-1">
                    Iniciar Sesión
                </button>

                <div class="text-center pt-4">
                    <p class="text-slate-400 text-sm">¿No tienes cuenta? 
                        <a href="registrarse.php" class="text-sky-600 font-bold hover:underline">Regístrate aquí</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>