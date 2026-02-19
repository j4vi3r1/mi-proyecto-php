<?php 
// Incluimos el header (Asegúrate de que la ruta sea correcta según tu estructura)
include_once __DIR__ . '/../partials/header.php'; 
?>

<main class="min-h-screen bg-slate-50 py-12 px-4">
    <div class="max-w-2xl mx-auto bg-white shadow-2xl shadow-slate-200 rounded-3xl overflow-hidden border border-slate-100">
        
        <div class="bg-sky-600 p-8 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl font-bold">Crea tu cuenta</h2>
                <p class="opacity-90">Ingresa tus datos para registrarte en el sistema.</p>
            </div>
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-sky-500 rounded-full opacity-50"></div>
        </div>

        <form action="../app/procesar_registro.php" method="POST" class="p-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">RUT</label>
                    <input type="text" name="rut" required placeholder="12.345.678-9" 
                           class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Correo Electrónico</label>
                    <input type="email" name="correo" required placeholder="correo@ejemplo.com" 
                           class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Nombre</label>
                    <input type="text" name="nombre" required placeholder="Tu nombre" 
                           class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Apellido</label>
                    <input type="text" name="apellido" required placeholder="Tu apellido" 
                           class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none transition-all">
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Teléfono de Contacto</label>
                    <input type="text" name="contacto" required placeholder="+56 9 1234 5678" 
                           class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none transition-all">
                </div>
            </div>

            <hr class="border-slate-100">

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Dirección Particular</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <input type="text" id="display_direccion" readonly required 
                           placeholder="Presiona aquí para ingresar tu dirección..." 
                           class="w-full pl-10 pr-4 py-4 bg-slate-100 border border-slate-200 rounded-2xl cursor-pointer hover:bg-sky-50 hover:border-sky-300 transition-all outline-none font-medium text-slate-700">
                    
                    <input type="hidden" name="pais" id="hidden_pais">
                    <input type="hidden" name="ciudad" id="hidden_ciudad">
                    <input type="hidden" name="calle" id="hidden_calle">
                    <input type="hidden" name="numero" id="hidden_numero">
                </div>
            </div>

            <div class="pt-4 flex flex-col gap-3">
                <button type="submit" class="w-full py-4 bg-sky-600 text-white rounded-2xl font-bold text-lg hover:bg-sky-700 shadow-xl shadow-sky-100 transition-all transform hover:-translate-y-1">
                    Completar Registro
                </button>
                <a href="login.php" class="text-center text-sm font-semibold text-slate-400 hover:text-slate-600 transition-colors">
                    ¿Ya tienes cuenta? Inicia sesión
                </a>
            </div>
        </form>
    </div>
</main>

<div id="modal-direccion" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-md p-4">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transform transition-all">
        <div class="p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-sky-100 rounded-2xl">
                    <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Ubicación</h3>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="temp_pais" placeholder="País" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                    <input type="text" id="temp_ciudad" placeholder="Ciudad" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <input type="text" id="temp_calle" placeholder="Calle / Avenida" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                <input type="text" id="temp_numero" placeholder="Número (ej: 123 o Depto 4B)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                
                <div class="pt-4 flex gap-2">
                    <button type="button" id="btn-cerrar-modal" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all">Cancelar</button>
                    <button type="button" id="btn-guardar-dir" class="flex-1 py-3 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-900 transition-all shadow-lg shadow-slate-200">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-direccion');
    const inputDisplay = document.getElementById('display_direccion');
    const btnGuardar = document.getElementById('btn-guardar-dir');
    const btnCerrar = document.getElementById('btn-cerrar-modal');

    // Función para abrir modal
    inputDisplay.onclick = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    // Función para cerrar
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    btnCerrar.onclick = closeModal;

    // Lógica para capturar los datos del modal
    btnGuardar.onclick = () => {
        const pais = document.getElementById('temp_pais').value.trim();
        const ciudad = document.getElementById('temp_ciudad').value.trim();
        const calle = document.getElementById('temp_calle').value.trim();
        const num = document.getElementById('temp_numero').value.trim();

        if(pais && ciudad && calle && num) {
            // Asignar a los campos ocultos que se enviarán por POST
            document.getElementById('hidden_pais').value = pais;
            document.getElementById('hidden_ciudad').value = ciudad;
            document.getElementById('hidden_calle').value = calle;
            document.getElementById('hidden_numero').value = num;

            // Mostrar resumen en el campo principal
            inputDisplay.value = `${calle} ${num}, ${ciudad}`;
            inputDisplay.classList.add('border-emerald-400', 'bg-emerald-50');
            
            closeModal();
        } else {
            alert("Por favor, completa todos los campos de la dirección.");
        }
    };

    // Cerrar si hacen clic fuera del contenido blanco
    window.onclick = (event) => {
        if (event.target == modal) closeModal();
    };
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>