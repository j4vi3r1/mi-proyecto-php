<?php
include_once __DIR__ . '/../app/conexion.php';
include_once __DIR__ . '/../partials/header.php';

$rut = $_GET['rut'] ?? '';

// 1. Obtener datos actuales incluyendo la dirección
$stmt = $conn->prepare("
    SELECT p.*, d.calle, d.numero, d.ciudad, d.pais 
    FROM Persona p 
    LEFT JOIN Direccion d ON p.direccionid = d.direccionid 
    WHERE p.rut = :rut
");
$stmt->execute([':rut' => $rut]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("Usuario no encontrado");
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-[2rem] shadow-xl">
        <h2 class="text-2xl font-black mb-6">Editar Datos: <?= $user['nombre'] ?></h2>
        
        <form action="../app/actualizar_usuario.php" method="POST" class="grid grid-cols-2 gap-4">
            <input type="hidden" name="rut_original" value="<?= $user['rut'] ?>">
            
            <div class="col-span-2">
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Nombre</label>
                <input type="text" name="nombre" value="<?= $user['nombre'] ?>" class="w-full p-3 bg-slate-100 rounded-xl border-none">
            </div>

            <div class="col-span-2">
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Correo Electrónico</label>
                <input type="email" name="correo" value="<?= $user['correo'] ?>" class="w-full p-3 bg-slate-100 rounded-xl border-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Ciudad</label>
                <input type="text" name="ciudad" value="<?= $user['ciudad'] ?>" class="w-full p-3 bg-slate-100 rounded-xl border-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Calle y Número</label>
                <input type="text" name="calle" value="<?= $user['calle'] ?>" class="w-full p-3 bg-slate-100 rounded-xl border-none">
            </div>

            <div class="col-span-2 mt-4 flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-slate-900 text-white rounded-xl font-bold">Guardar Cambios</button>
                <a href="usuarios.php" class="flex-1 py-3 bg-slate-200 text-center rounded-xl font-bold">Cancelar</a>
            </div>
        </form>
    </div>
</main>