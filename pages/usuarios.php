<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. SEGURIDAD: Solo usuarios con rol 1 (Empleado) o 2 (Admin) pueden entrar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] < 1) {
    header("Location: ../public/index.php?err=acceso_denegado");
    exit();
}

include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

// Variables de entorno
$esAdmin = ($_SESSION['rol'] === 2);
$vista = $_GET['vista'] ?? 'clientes';
$miRut = $_SESSION['usuario_rut'] ?? ''; 

// Si un empleado (rol 1) intenta entrar a la vista de staff, lo forzamos a clientes
if ($vista === 'empleados' && !$esAdmin) {
    $vista = 'clientes';
}
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

        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 <?= $vista == 'clientes' ? 'bg-sky-100 text-sky-600' : 'bg-indigo-100 text-indigo-600' ?> rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 italic">
                        <?= $vista == 'clientes' ? 'Directorio de Clientes' : 'Cuerpo Administrativo' ?>
                    </h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-4">Información de Usuario</th>
                            <th class="px-8 py-4">RUT</th>
                            <th class="px-8 py-4">Nivel / Rol</th>
                            <th class="px-8 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        try {
                            if ($vista == 'empleados') {
                                $sql = "SELECT * FROM Persona WHERE rol >= 1 ORDER BY rol DESC, nombres ASC";
                            } else {
                                $sql = "SELECT * FROM Persona WHERE rol = 0 ORDER BY nombres ASC";
                            }

                            $stmt = $conn->query($sql);
                            if ($stmt->rowCount() > 0):
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
                                            <span class="px-2 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-lg uppercase border border-indigo-100">Administrador</span>
                                        <?php elseif($u['rol'] == 1): ?>
                                            <span class="px-2 py-1 bg-sky-50 text-sky-700 text-[10px] font-black rounded-lg uppercase border border-sky-100">Empleado</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-slate-50 text-slate-500 text-[10px] font-black rounded-lg uppercase border border-slate-200">Cliente</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-8 py-5 text-right flex justify-end gap-2 items-center">
                                        <?php if($esAdmin || $u['rut'] == $miRut): ?>
                                        <button onclick="abrirModal('<?= $u['rut'] ?>', '<?= addslashes($u['nombres']) ?>', '<?= addslashes($u['apellidos']) ?>', '<?= $u['correo'] ?>', '<?= $u['contacto'] ?>')" 
                                                class="p-2 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-sky-600 hover:text-white transition-all shadow-sm" title="Editar Perfil">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <?php endif; ?>

                                        <?php if ($esAdmin && $u['rut'] !== $miRut): ?>
                                            <?php if ($u['rol'] == 0): // Es Cliente ?>
                                                <a href="../app/gestionar_rol.php?rut=<?= urlencode($u['rut']) ?>&accion=promover" 
                                                   class="px-3 py-2 bg-sky-50 text-sky-600 rounded-xl text-[10px] font-black uppercase tracking-tight hover:bg-sky-600 hover:text-white transition-all">
                                                    Hacer Empleado
                                                </a>
                                            <?php elseif($u['rol'] == 1): // Es Empleado ?>
                                                <a href="../app/gestionar_rol.php?rut=<?= urlencode($u['rut']) ?>&accion=ascender_admin" 
                                                   onclick="return confirm('¿Seguro que quieres otorgar permisos de ADMINISTRADOR a esta persona?')"
                                                   class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded-xl text-[10px] font-black uppercase tracking-tight hover:bg-indigo-700 hover:text-white transition-all">
                                                    Ascender a Admin
                                                </a>
                                                <a href="../app/gestionar_rol.php?rut=<?= urlencode($u['rut']) ?>&accion=degradar" 
                                                   class="px-3 py-2 bg-amber-50 text-amber-600 rounded-xl text-[10px] font-black uppercase tracking-tight hover:bg-amber-600 hover:text-white transition-all">
                                                    Bajar a Cliente
                                                </a>
                                            <?php elseif($u['rol'] == 2): // Es otro Admin ?>
                                                <a href="../app/gestionar_rol.php?rut=<?= urlencode($u['rut']) ?>&accion=degradar" 
                                                   class="px-3 py-2 bg-rose-50 text-rose-600 rounded-xl text-[10px] font-black uppercase tracking-tight hover:bg-rose-600 hover:text-white transition-all">
                                                    Quitar Admin
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($u['rut'] === $miRut): ?>
                                            <span class="px-4 py-2 text-sky-600 italic text-[10px] font-black uppercase tracking-widest bg-sky-50 rounded-xl border border-sky-100">Mi Perfil</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php 
                                endwhile; 
                            else: 
                        ?>
                            <tr>
                                <td colspan="4" class="px-8 py-20 text-center text-slate-400 italic font-medium">
                                    No se encontraron registros en esta categoría.
                                </td>
                            </tr>
                        <?php 
                            endif;
                        } catch (Exception $e) {
                            echo "<tr><td colspan='4' class='p-4 text-red-500 text-center'>Error: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

...
