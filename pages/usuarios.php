<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. SEGURIDAD: Solo empleados (rol 1) y admins (rol 2) entran aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] < 1) {
    header("Location: ../public/index.php?err=acceso_denegado");
    exit();
}

include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

$esAdmin = ($_SESSION['rol'] === 2);
$vista = $_GET['vista'] ?? 'clientes';
$miRut = $_SESSION['usuario_rut'] ?? ''; 

if ($vista === 'empleados' && !$esAdmin) { $vista = 'clientes'; }
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <div class="text-center space-y-6">
            <h1 class="text-4xl font-black text-slate-900 tracking-tighter italic">Gestión de Usuarios</h1>
            
            <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-slate-200 flex max-w-sm mx-auto">
                <a href="?vista=clientes" class="flex-1 py-3 text-center rounded-xl text-sm font-bold transition-all <?= $vista == 'clientes' ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50' ?>">
                    Clientes
                </a>
                <?php if ($esAdmin): ?>
                <a href="?vista=empleados" class="flex-1 py-3 text-center rounded-xl text-sm font-bold transition-all <?= $vista == 'empleados' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50' ?>">
                    Staff (RR.HH)
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if(isset($_GET['res'])): ?>
            <div class="max-w-md mx-auto bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-center text-sm font-bold animate-pulse">
                ¡Operación realizada con éxito!
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-4">Usuario</th>
                            <th class="px-8 py-4">RUT</th>
                            <th class="px-8 py-4">Nivel / Rol</th>
                            <th class="px-8 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        try {
                            $condicion = ($vista == 'empleados') ? "p.rol >= 1" : "p.rol = 0";
                            $sql = "SELECT p.*, d.pais, d.ciudad, d.calle, d.numero, p.direccionID as dirid
                                    FROM Persona p 
                                    LEFT JOIN Direccion d ON p.direccionID = d.direccionID 
                                    WHERE $condicion ORDER BY p.rol DESC, p.nombres ASC";

                            $stmt = $conn->query($sql);
                            while ($u = $stmt->fetch(PDO::FETCH_ASSOC)): 
                        ?>
                                <tr class="group hover:bg-slate-50/80 transition-all">
                                    <td class="px-8 py-5">
                                        <p class="font-bold text-slate-700"><?= htmlspecialchars($u['nombres'] . " " . $u['apellidos']) ?></p>
                                        <p class="text-xs text-slate-400 font-medium"><?= htmlspecialchars($u['correo']) ?></p>
                                    </td>
                                    <td class="px-8 py-5 font-mono text-sm text-slate-600"><?= htmlspecialchars($u['rut']) ?></td>
                                    <td class="px-8 py-5">
                                        <?php if($u['rol'] == 2): ?>
                                            <span class="px-2 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-lg border border-indigo-100 uppercase">Administrador</span>
                                        <?php elseif($u['rol'] == 1): ?>
                                            <span class="px-2 py-1 bg-sky-50 text-sky-700 text-[10px] font-black rounded-lg border border-sky-100 uppercase">Empleado</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-slate-50 text-slate-500 text-[10px] font-black rounded-lg border border-slate-200 uppercase">Cliente</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-8 py-5 text-right flex justify-end gap-2 items-center">
                                        <?php if($esAdmin || $u['rut'] == $miRut): ?>
                                        <button onclick="abrirModal(
                                            '<?= $u['rut'] ?>', '<?= addslashes($u['nombres']) ?>', '<?= addslashes($u['apellidos']) ?>', 
                                            '<?= $u['correo'] ?>', '<?= $u['contacto'] ?>', '<?= addslashes($u['pais'] ?? '') ?>',
                                            '<?= addslashes($u['ciudad'] ?? '') ?>', '<?= addslashes($u['calle'] ?? '') ?>',
                                            '<?= $u['numero'] ?? '' ?>', '<?= $u['dirid'] ?? '' ?>' 
                                        )" class="p-2 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-sky-600 hover:text-white transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <?php endif; ?>

                                        <?php if ($esAdmin && $u['rut'] !== $miRut): ?>
                                            <div class="flex gap-1">
                                                <?php if ($u['rol'] == 0): ?>
                                                    <a href="../app/gestionar_rol.php?rut=<?= urlencode($u['rut']) ?>&accion=promover" class="px-3 py-2 bg-sky-50 text-sky-600 rounded-xl text-[10px] font-black uppercase hover:bg-sky-600 hover:text-white transition-all">Promover</a>
                                                <?php elseif($u['rol'] == 1): ?>
                                                    <a href="../app/gestionar_rol.php?rut=<?= urlencode($u['rut']) ?>&accion=ascender_admin" onclick="return confirm('¿Ascender a ADMIN?')" class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded-xl text-[10px] font-black uppercase hover:bg-indigo-700 hover:text-white transition-all">Admin</a>
                                                    <a href="../app/gestionar_rol.php?rut=<?= urlencode($u['rut']) ?>&accion=degradar" class="px-3 py-2 bg-amber-50 text-amber-600 rounded-xl text-[10px] font-black uppercase hover:bg-amber-600 hover:text-white transition-all">Bajar</a>
                                                <?php elseif($u['rol'] == 2): ?>
                                                    <a href="../app/gestionar_rol.php?rut=<?= urlencode($u['rut']) ?>&accion=degradar" class="px-3 py-2 bg-rose-50 text-rose-600 rounded-xl text-[10px] font-black uppercase hover:bg-rose-600 hover:text-white transition-all">Quitar Admin</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php endwhile; } catch (Exception $e) { echo "Error: " . $e->getMessage(); } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div id="modalEdicion" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-100">
        <div class="p-6 bg-slate-50 border-b flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 italic tracking-tight">Actualizar Información</h3>
            <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form action="../app/actualizar_usuario.php" method="POST" class="p-8">
            <input type="hidden" name="rut" id="edit_rut">
            <input type="hidden" name="direccionid" id="edit_direccionid">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <h4 class="text-[10px] font-black uppercase text-sky-600 tracking-widest border-b border-sky-50 pb-2">Datos de Contacto</h4>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 ml-1">NOMBRES</label>
                        <input type="text" name="nombres" id="edit_nombres" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 ml-1">APELLIDOS</label>
                        <input type="text" name="apellidos" id="edit_apellidos" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 ml-1">CORREO</label>
                        <input type="email" name="correo" id="edit_correo" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 ml-1">TELÉFONO</label>
                        <input type="text" name="contacto" id="edit_contacto" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-[10px] font-black uppercase text-indigo-600 tracking-widest border-b border-indigo-50 pb-2">Ubicación</h4>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 ml-1">PAÍS</label>
                        <input type="text" name="pais" id="edit_pais" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 ml-1">CIUDAD</label>
                        <input type="text" name="ciudad" id="edit_ciudad" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="text-[10px] font-bold text-slate-400 ml-1">CALLE / PASAJE</label>
                            <input type="text" name="calle" id="edit_calle" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="col-span-1">
                            <label class="text-[10px] font-bold text-slate-400 ml-1">NÚMERO</label>
                            <input type="text" name="numero" id="edit_numero" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-center font-bold">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 flex gap-3">
                <button type="button" onclick="cerrarModal()" class="flex-1 py-3 font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all">Cancelar</button>
                <button type="submit" class="flex-1 py-3 bg-slate-900 text-white rounded-xl font-bold shadow-xl hover:bg-black transform active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal(rut, nom, ape, cor, tel, pai, ciu, cal, num, dirid) {
    document.getElementById('edit_rut').value = rut;
    document.getElementById('edit_nombres').value = nom;
    document.getElementById('edit_apellidos').value = ape;
    document.getElementById('edit_correo').value = cor;
    document.getElementById('edit_contacto').value = tel;
    document.getElementById('edit_pais').value = pai;
    document.getElementById('edit_ciudad').value = ciu;
    document.getElementById('edit_calle').value = cal;
    document.getElementById('edit_numero').value = num;
    document.getElementById('edit_direccionid').value = dirid;
    document.getElementById('modalEdicion').classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('modalEdicion').classList.add('hidden');
}

window.onclick = function(event) {
    if (event.target == document.getElementById('modalEdicion')) cerrarModal();
}
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
