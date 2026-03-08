<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

// Capturar búsqueda si existe
$search = $_GET['search'] ?? '';
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <?php if(isset($_GET['res']) && $_GET['res'] === 'deleted'): ?>
        <div class="max-w-7xl mx-auto mb-6 flex items-center gap-3 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl font-bold animate-bounce">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            El contribuyente ha sido eliminado permanentemente.
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="mb-6">
            <a href="funciones.php" class="inline-flex items-center gap-2 text-slate-400 hover:text-[#7c83e5] transition-all group font-bold text-xs uppercase tracking-widest">
                <div class="w-8 h-8 rounded-full bg-white border border-slate-100 shadow-sm flex items-center justify-center group-hover:shadow-md group-hover:-translate-x-1 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path>
                    </svg> 
                </div>
                Volver a Funciones
            </a>
        </div>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tighter italic">Cartera de <span class="text-sky-600">Clientes</span></h1>
                <p class="text-slate-500 font-medium">Gestión integral de contribuyentes y obligaciones.</p>
            </div>
            <a href="registrar_clientes.php" class="group px-6 py-3 bg-sky-600 text-white font-bold rounded-2xl hover:bg-sky-700 transition-all flex items-center gap-2 shadow-lg shadow-sky-200">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Registrar Cliente
            </a>
        </div>

        <div class="bg-white p-4 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
            <form action="" method="GET" class="flex-1 flex gap-4">
                <div class="flex-1 relative">
                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Buscar por RUT, Razón Social o Representante..." 
                           class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-sky-500 outline-none text-sm font-medium">
                </div>
                <button type="submit" class="px-8 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all text-sm uppercase tracking-widest">
                    Filtrar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-5">Contribuyente</th>
                            <th class="px-8 py-5">Identificación</th>
                            <th class="px-8 py-5">Tratamiento IVA / PPM</th>
                            <th class="px-8 py-5">Estado</th>
                            <th class="px-8 py-5">Honorarios</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        try {
                            $where = $search ? "WHERE c.razon_social ILIKE :s OR c.rut_contribuyente ILIKE :s" : "";
                            $sql = "SELECT c.*, ec.nombre_estado, i.tipo_iva, r.nombre_regimen 
                                    FROM Contribuyentes c
                                    LEFT JOIN EstadosContribuyente ec ON c.id_estado = ec.id_estado
                                    LEFT JOIN IVA i ON c.id_iva = i.id_iva
                                    LEFT JOIN Regimenes r ON c.id_regimen = r.id_regimen
                                    $where
                                    ORDER BY c.razon_social ASC";
                            
                            $stmt = $conn->prepare($sql);
                            if($search) $stmt->bindValue(':s', "%$search%");
                            $stmt->execute();

                            if ($stmt->rowCount() > 0):
                                while ($c = $stmt->fetch(PDO::FETCH_ASSOC)): 
                        ?>
                                <tr class="group hover:bg-slate-50/80 transition-all">
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-700 leading-tight uppercase"><?= htmlspecialchars($c['razon_social']) ?></span>
                                            <span class="text-[10px] font-bold text-sky-600 uppercase mt-1 tracking-tighter italic"><?= htmlspecialchars($c['nombre_regimen'] ?? 'Sin Régimen') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="text-sm font-mono font-bold text-slate-500"><?= $c['rut_contribuyente'] ?></div>
                                        <div class="text-[10px] text-slate-400">Inicio: <?= $c['inicio_actividades'] ?? 'N/A' ?></div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center w-fit px-2 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-md uppercase">
                                                IVA: <?= $c['tipo_iva'] ?>
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 w-fit px-2 py-0.5 rounded-md italic">
                                                PPM: <?= $c['tasa_ppm'] ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <?php 
                                            $esVigente = strtolower($c['nombre_estado'] ?? '') == 'vigente';
                                            $colorClass = $esVigente ? 'emerald' : 'amber';
                                        ?>
                                        <span class="flex items-center gap-1.5 text-<?= $colorClass ?>-600 text-[11px] font-black uppercase">
                                            <span class="w-2 h-2 bg-<?= $colorClass ?>-500 rounded-full <?= $esVigente ? 'animate-pulse' : '' ?>"></span>
                                            <?= $c['nombre_estado'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="text-sm font-black text-slate-700">$<?= number_format($c['honorario_mensual'], 0, ',', '.') ?></div>
                                        <div class="text-[10px] text-sky-600 font-bold uppercase">Renta: $<?= number_format($c['honorario_renta'] ?? 0, 0, ',', '.') ?></div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 2): ?>
                                            <button type="button" 
                                                onclick="abrirModalBorrar('<?= $c['rut_contribuyente'] ?>', '<?= addslashes($c['razon_social']) ?>')"
                                                class="inline-flex p-3 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-all shadow-sm"
                                                title="Eliminar Cliente">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <a href="perfil_cliente.php?rut=<?= $c['rut_contribuyente'] ?>" 
                                               class="inline-flex p-3 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-900 hover:text-white transition-all shadow-sm" title="Ver Perfil">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php 
                                endwhile; 
                            else: 
                        ?>
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center opacity-20">
                                        <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-7h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <p class="text-xl font-black italic uppercase">No hay resultados</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; } catch (Exception $e) { echo "Error: " . $e->getMessage(); } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div id="modalBorrarCliente" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden p-4">
    <div class="w-[350px] flex flex-col p-6 relative items-center justify-center bg-gray-800 border border-gray-700 shadow-2xl rounded-3xl animate-in fade-in zoom-in duration-200">
        <div class="text-center p-3 flex-auto justify-center">
            <div class="w-16 h-16 bg-rose-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-rose-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h2 class="text-xl font-black py-2 text-gray-100 italic">¿Eliminar Cliente?</h2>
            <p class="text-sm text-gray-400 px-2 font-medium leading-relaxed">
                Estás a punto de borrar a: <br>
                <span id="nombreClienteModal" class="text-rose-400 font-bold uppercase"></span><br>
                <span class="text-[10px] text-gray-500 uppercase font-black">Esta acción borrará claves, socios y propiedades asociadas.</span>
            </p>
        </div>
        
        <div class="w-full mt-6 flex gap-3">
            <button type="button" onclick="cerrarModalBorrar()" 
                class="flex-1 bg-gray-700 py-3 text-xs font-bold tracking-wider border border-gray-600 text-gray-300 rounded-2xl hover:bg-gray-600 transition-all">
                CANCELAR
            </button>
            <a id="btnConfirmarBorrar" href="#" 
                class="flex-1 bg-rose-500 py-3 text-xs font-bold tracking-wider text-center text-white rounded-2xl hover:bg-rose-600 shadow-lg shadow-rose-900/20 transition-all">
                ELIMINAR
            </a>
        </div>
    </div>
</div>

<script>
function abrirModalBorrar(rut, nombre) {
    document.getElementById('nombreClienteModal').innerText = nombre;
    document.getElementById('btnConfirmarBorrar').href = `../app/borrar_cliente.php?rut=${rut}`;
    document.getElementById('modalBorrarCliente').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function cerrarModalBorrar() {
    document.getElementById('modalBorrarCliente').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Cerrar si hace clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('modalBorrarCliente');
    if (event.target == modal) cerrarModalBorrar();
}
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
