<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS Contadores</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50">

<nav class="bg-white border-b border-slate-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            
            <div class="flex items-center gap-10">
                <a href="../public/index.php" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                    <div class="w-10 h-10 bg-sky-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-sky-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-black text-slate-800 tracking-tighter">SVS Contadores<span class="text-sky-600">Pro</span></span>
                </a>

                <?php if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true): ?>
                <div class="hidden lg:flex items-center gap-8 border-l border-slate-100 pl-8">
                    <a href="../pages/catalogo.php" class="text-sm font-bold text-slate-500 hover:text-sky-600 transition-all">Catálogo</a>
                    
                    <?php if (isset($_SESSION['es_empleado']) && $_SESSION['es_empleado'] === true): ?>
                        <a href="../pages/funciones.php" class="text-sm font-bold text-slate-500 hover:text-sky-600 transition-all">Funciones</a>
                        <a href="../pages/usuarios.php" class="text-sm font-bold text-slate-500 hover:text-sky-600 transition-all">Usuarios</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true): ?>
                    <div class="flex flex-col items-end hidden sm:flex">
                        <span class="text-[10px] font-black uppercase text-slate-400 leading-none">
                            <?= (isset($_SESSION['es_empleado']) && $_SESSION['es_empleado'] === true) ? 'Personal Administrativo' : 'Cliente' ?>
                        </span>
                        <span class="text-sm font-bold text-slate-900"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                    </div>
                    <a href="../app/logout.php" class="px-5 py-2.5 bg-red-50 text-red-600 rounded-xl text-sm font-bold hover:bg-red-600 hover:text-white transition-all">
                        Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="../pages/login.php" class="text-sm font-bold text-slate-600 hover:text-sky-600 transition-all">
                        Iniciar Sesión
                    </a>
                    <a href="../pages/registrarse.php" class="px-5 py-2.5 bg-sky-600 text-white rounded-xl text-sm font-bold hover:bg-sky-700 shadow-lg shadow-sky-100 transition-all">
                        Crear Cuenta
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</nav>