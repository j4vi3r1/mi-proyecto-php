<?php 
session_start();
include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

$esEmpleado = isset($_SESSION['es_empleado']) && $_SESSION['es_empleado'] === true;

// 1. Lógica del Buscador
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-12 gap-8">
            <div>
                <span class="text-sky-600 font-bold uppercase tracking-widest text-xs">Inventario de Equipos</span>
                <h1 class="text-5xl font-black text-slate-900 tracking-tighter mt-2">Explorar Catálogo</h1>
            </div>

            <div class="flex flex-col md:flex-row gap-4 w-full lg:w-auto">
                <form action="" method="GET" class="relative group flex-1 md:w-80">
                    <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" 
                           placeholder="Nombre, marca o serie..." 
                           class="w-full pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-sky-500 transition-all shadow-sm">
                    <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </form>

                <?php if ($esEmpleado): ?>
                    <a href="en_desarrollo.php" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-slate-900 text-white rounded-2xl font-bold hover:bg-sky-600 transition-all shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nuevo Registro
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            try {
                $query = "SELECT * FROM Equipos";
                if (!empty($busqueda)) {
                    $query .= " WHERE nombre ILIKE :bus OR marca ILIKE :bus OR numeroserie ILIKE :bus";
                }
                $query .= " ORDER BY estado DESC, nombre ASC";
                
                $stmt = $conn->prepare($query);
                if (!empty($busqueda)) $stmt->execute([':bus' => "%$busqueda%"]);
                else $stmt->execute();
                
                while ($equipo = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $disponible = $equipo['estado'] === 'Disponible';
                    $colorEstado = $disponible ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600';
                    $nombreFoto = !empty($equipo['imagen']) ? $equipo['imagen'] : 'default.webp';
                    ?>
                    
                    <div class="group bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/60 overflow-hidden hover:-translate-y-2 transition-all duration-300">
                        <div class="h-64 bg-slate-100 relative flex items-center justify-center overflow-hidden">
                            <img src="../img/<?= $nombreFoto ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                 onerror="this.src='../img/default.webp';">
                            
                            <div class="absolute top-6 left-6">
                                <span class="px-4 py-1.5 <?= $colorEstado ?> text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm">
                                    <?= htmlspecialchars($equipo['estado']) ?>
                                </span>
                            </div>

                            <?php if ($esEmpleado): ?>
                            <div class="absolute top-6 right-6 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="editar_equipo.php?id=<?= $equipo['numeroserie'] ?>" 
                                   class="p-3 bg-white text-slate-700 rounded-xl shadow-lg hover:bg-sky-500 hover:text-white transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-8">
                            <div class="mb-6">
                                <p class="text-sky-600 text-xs font-black uppercase tracking-tighter mb-1">
                                    <?= htmlspecialchars($equipo['marca']) ?> • <?= htmlspecialchars($equipo['modelo']) ?>
                                </p>
                                <h3 class="text-2xl font-bold text-slate-800 tracking-tight leading-tight">
                                    <?= htmlspecialchars($equipo['nombre']) ?>
                                </h3>
                                
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Valor Reposición:</span>
                                    <span class="text-xs font-bold text-slate-600">$<?= number_format($equipo['valor_comercial'], 0, ',', '.') ?></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Arriendo / Día</p>
                                    <p class="text-2xl font-black text-slate-900">
                                        $<?= number_format($equipo['preciounitario'], 0, ',', '.') ?>
                                    </p>
                                </div>
                                
                                <?php if ($disponible): ?>
                                    <a href="crearContrato.php?serie=<?= $equipo['numeroserie'] ?>" 
                                       class="h-14 px-6 bg-slate-900 text-white rounded-2xl flex items-center justify-center hover:bg-sky-600 transition-colors shadow-lg font-bold text-sm">
                                        Arrendar
                                    </a>
                                <?php else: ?>
                                    <button disabled class="h-14 px-6 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center cursor-not-allowed font-bold text-sm">
                                        Reservado
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php 
                } 
            } catch (Exception $e) {
                echo "<div class='bg-red-50 text-red-500 p-4 rounded-xl'>Error de conexión.</div>";
            }
            ?>
        </div>
    </div>
</main>


<?php include_once __DIR__ . '/../partials/footer.php'; ?>
