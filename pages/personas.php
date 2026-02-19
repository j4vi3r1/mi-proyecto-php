<?php 
// 1. Gestión de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. IMPORTANTE: Rutas corregidas
// __DIR__ es "C:\xampp\htdocs\MiProyecto\pages"
// /../ sube un nivel a "MiProyecto" y luego entra a "app" o "partials"
include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 
?>

<main class="min-h-screen bg-slate-50">
    <section class="max-w-6xl mx-auto py-10 px-4">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Gestión de Personas</h1>
                <p class="text-slate-500">Administra clientes y empleados del sistema.</p>
            </div>

            <div class="flex items-center gap-3">
                <form method="GET" class="relative">
                    <input type="text" name="q" 
                           placeholder="Buscar por RUT o Nombre..." 
                           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                           class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm focus:ring-2 focus:ring-sky-500 outline-none w-64 transition-all">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </form>
                
                <button id="btn-abrir-modal" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-sky-200 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">RUT</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Nombre Completo</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Correo</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Rol</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $q = isset($_GET['q']) ? $_GET['q'] : '';
                    
                    // Nota: Si PostgreSQL te da error, intenta poner "persona" entre comillas dobles: FROM "persona"
                    $sql = "SELECT * FROM persona WHERE rut LIKE :q OR nombre LIKE :q OR apellido LIKE :q ORDER BY nombre ASC";
                    
                    try {
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([':q' => "%$q%"]);
                        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($resultados) > 0) {
                            foreach ($resultados as $row) {
                                $esEmpleado = ($row['rol'] == 1);
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-700"><?= htmlspecialchars($row['rut']) ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
                                    <td class="px-6 py-4 text-slate-500 text-sm"><?= htmlspecialchars($row['correo']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase <?= $esEmpleado ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                            <?= $esEmpleado ? 'Empleado' : 'Cliente' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <button class="p-2 text-slate-400 hover:text-sky-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                            <button class="p-2 text-slate-400 hover:text-red-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='px-6 py-10 text-center text-slate-400'>No se encontraron registros.</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='5' class='px-6 py-4 bg-red-50 text-red-600'>Error: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div id="modal-persona" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-slate-800">Nueva Persona</h3>
                <button id="btn-cerrar" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>

            <form action="../app/guardar_persona.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase ml-1">RUT</label>
                        <input type="text" name="rut" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase ml-1">Rol</label>
                        <select name="rol" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                            <option value="0">Cliente</option>
                            <option value="1">Empleado</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Nombre</label>
                    <input type="text" name="nombre" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Apellido</label>
                    <input type="text" name="apellido" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Correo</label>
                    <input type="email" name="correo" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div class="pt-6 flex gap-3">
                    <button type="button" id="btn-cancelar" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-3 bg-sky-600 text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-200 transition-all">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-persona');
    const btnAbrir = document.getElementById('btn-abrir-modal');
    const btnCerrar = document.getElementById('btn-cerrar');
    const btnCancelar = document.getElementById('btn-cancelar');

    const toggleModal = () => modal.classList.toggle('hidden');
    const flexModal = () => modal.classList.toggle('flex');

    btnAbrir.onclick = () => { toggleModal(); flexModal(); };
    btnCerrar.onclick = () => { toggleModal(); flexModal(); };
    btnCancelar.onclick = () => { toggleModal(); flexModal(); };
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>