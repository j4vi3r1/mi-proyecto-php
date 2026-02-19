<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Seguridad básica
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], [1, 2])) {
    header("Location: ../public/index.php?error=acceso_denegado");
    exit();
}

include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

// 2. Obtener RUT y Cargar Datos Actuales
$rut = $_GET['rut'] ?? null;
if (!$rut) die("Error: RUT no especificado.");

try {
    // Consulta Maestra
    $stmt = $conn->prepare("
        SELECT c.*, 
               rep.rut_representante, rep.nombre as nombre_rep, rep.clave_sii as clave_rep,
               con.nombre_contacto, con.telefono as tel_con, con.correo as mail_con
        FROM Contribuyentes c
        LEFT JOIN Representantes rep ON c.id_representante = rep.id_representante
        LEFT JOIN Contacto con ON c.id_contacto = con.id_contacto
        WHERE c.rut_contribuyente = ?
    ");
    $stmt->execute([$rut]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$c) die("Error: Cliente no encontrado.");

    // Cargar Listas para los Badges (Relaciones 1:N)
    $stmtSocios = $conn->prepare("SELECT * FROM Socios WHERE rut_contribuyente = ?");
    $stmtSocios->execute([$rut]);
    $sociosActuales = $stmtSocios->fetchAll(PDO::FETCH_ASSOC);

    // Cargar Maestros para Selects
    $tiposEmpresa = $conn->query("SELECT * FROM TiposEmpresa ORDER BY tipo ASC")->fetchAll(PDO::FETCH_ASSOC);
    $estados = $conn->query("SELECT * FROM EstadosContribuyente")->fetchAll(PDO::FETCH_ASSOC);
    $regimenes = $conn->query("SELECT * FROM Regimenes")->fetchAll(PDO::FETCH_ASSOC);
    $ivas = $conn->query("SELECT * FROM IVA")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error cargando datos: " . $e->getMessage());
}
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-5xl mx-auto">
        <form action="../app/actualizar_cliente.php" method="POST" class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
            
            <input type="hidden" name="rut_original" value="<?= htmlspecialchars($c['rut_contribuyente']) ?>">
            <input type="hidden" name="id_contacto_actual" value="<?= $c['id_contacto'] ?>">
            <input type="hidden" name="id_rep_actual" value="<?= $c['id_representante'] ?>">

            <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center gap-4">
                <div class="p-3 bg-amber-500 text-white rounded-2xl shadow-lg shadow-amber-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Editar Contribuyente</h1>
                    <p class="text-sm text-slate-400 font-medium font-mono"><?= $c['rut_contribuyente'] ?></p>
                </div>
            </div>

            <div class="p-10 space-y-12">
                
                <section class="space-y-6">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-sky-600 flex items-center gap-2">
                        <span class="w-8 h-[2px] bg-sky-600"></span> Identificación Básica
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">RUT Contribuyente</label>
                            <input type="text" name="rut_contribuyente" value="<?= htmlspecialchars($c['rut_contribuyente']) ?>" required class="w-full p-3 bg-slate-100 border border-slate-200 rounded-xl outline-none">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700">Razón Social</label>
                            <input type="text" name="razon_social" value="<?= htmlspecialchars($c['razon_social']) ?>" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <section class="space-y-6">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-violet-600 flex items-center gap-2">
                            <span class="w-8 h-[2px] bg-violet-600"></span> Propiedades / Domicilios
                        </h2>
                        <div class="p-6 bg-violet-50/30 border border-dashed border-violet-200 rounded-[2rem]">
                            <div id="lista-propiedades" class="flex flex-wrap gap-2 mb-4"></div>
                            <button type="button" onclick="openModalPropiedad()" class="w-full py-3 bg-white border-2 border-dashed border-violet-200 text-violet-600 font-bold rounded-xl hover:bg-violet-50 transition-all text-xs">+ Añadir Propiedad</button>
                        </div>
                    </section>

                    <section class="space-y-6">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-rose-600 flex items-center gap-2">
                            <span class="w-8 h-[2px] bg-rose-600"></span> Claves de Acceso
                        </h2>
                        <div class="p-6 bg-rose-50/30 border border-dashed border-rose-200 rounded-[2rem]">
                            <div id="lista-claves" class="flex flex-wrap gap-2 mb-4"></div>
                            <button type="button" onclick="openModalClave()" class="w-full py-3 bg-white border-2 border-dashed border-rose-200 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition-all text-xs">+ Configurar Clave</button>
                        </div>
                    </section>
                </div>

                <section class="space-y-6">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600 flex items-center gap-2">
                        <span class="w-8 h-[2px] bg-emerald-600"></span> Socios y Participación
                    </h2>
                    <div class="p-6 bg-emerald-50/30 border border-dashed border-emerald-200 rounded-[2rem]">
                        <div id="lista-socios" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <?php foreach($sociosActuales as $socio): ?>
                                <div class="p-3 bg-white border border-emerald-100 rounded-xl shadow-sm flex flex-col">
                                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Socio</span>
                                    <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($socio['nombre_socio']) ?></span>
                                    <span class="text-xs text-slate-400"><?= $socio['rut_socio'] ?> (<?= $socio['porcentaje_participacion'] ?>%)</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" onclick="openModalSocio()" class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-emerald-200 text-emerald-600 font-bold rounded-xl hover:bg-emerald-600 hover:text-white transition-all text-xs">
                            + Añadir Socio
                        </button>
                    </div>
                </section>

                <section class="space-y-6">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-600 flex items-center gap-2">
                        <span class="w-8 h-[2px] bg-indigo-600"></span> Perfil Tributario y Cobranza
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Estado</label>
                            <select name="id_estado" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <?php foreach($estados as $e): ?>
                                    <option value="<?= $e['id_estado'] ?>" <?= $c['id_estado'] == $e['id_estado'] ? 'selected' : '' ?>><?= $e['nombre_estado'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Tipo Empresa</label>
                            <select name="id_tipo_empresa" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <?php foreach($tiposEmpresa as $te): ?>
                                    <option value="<?= $te['id_tipo'] ?>" <?= $c['id_tipo_empresa'] == $te['id_tipo'] ? 'selected' : '' ?>><?= $te['tipo'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Régimen</label>
                            <select name="id_regimen" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <?php foreach($regimenes as $r): ?>
                                    <option value="<?= $r['id_regimen'] ?>" <?= $c['id_regimen'] == $r['id_regimen'] ? 'selected' : '' ?>><?= $r['nombre_regimen'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">IVA</label>
                            <select name="id_iva" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <?php foreach($ivas as $i): ?>
                                    <option value="<?= $i['id_iva'] ?>" <?= $c['id_iva'] == $i['id_iva'] ? 'selected' : '' ?>><?= $i['tipo_iva'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Software Contable</label>
                            <input type="text" name="software" value="<?= htmlspecialchars($c['software'] ?? '') ?>" placeholder="Ej: Nubox" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Inicio Actividades</label>
                            <input type="date" name="inicio_actividades" value="<?= $c['inicio_actividades'] ?>" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Tasa PPM (%)</label>
                            <input type="number" step="0.01" name="tasa_ppm" value="<?= $c['tasa_ppm'] ?>" class="w-full p-3 bg-sky-50 border border-sky-100 rounded-xl font-bold text-sky-600">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Hon. Mensual ($)</label>
                            <input type="number" name="honorario_mensual" value="<?= $c['honorario_mensual'] ?>" class="w-full p-3 bg-emerald-50 border border-emerald-100 rounded-xl font-black text-emerald-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Hon. Renta Mensual ($)</label>
                            <input type="number" name="honorario_renta" value="<?= $c['honorario_renta'] ?? 0 ?>" class="w-full p-3 bg-amber-50 border border-amber-100 rounded-xl font-black text-amber-700">
                        </div>
                    </div>

                    <div class="flex gap-8 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="remuneracion" <?= ($c['remuneracion'] == 't' || $c['remuneracion'] == 1) ? 'checked' : '' ?> class="w-5 h-5 text-sky-600 rounded">
                            <span class="text-sm font-bold text-slate-700">Maneja Remuneraciones</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="facturacion" <?= ($c['facturacion'] == 't' || $c['facturacion'] == 1) ? 'checked' : '' ?> class="w-5 h-5 text-sky-600 rounded">
                            <span class="text-sm font-bold text-slate-700">Maneja Facturación</span>
                        </label>
                    </div>
                </section>

                <section class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="p-6 bg-emerald-50/50 rounded-3xl border border-emerald-100 space-y-4">
                        <h3 class="font-bold text-emerald-800 text-sm italic">Contacto Principal (Cliente)</h3>
                        <input type="text" name="nombre_contacto" value="<?= htmlspecialchars($c['nombre_contacto'] ?? '') ?>" placeholder="Nombre" class="w-full p-3 bg-white border border-emerald-200 rounded-xl">
                        <input type="text" name="telefono_contacto" value="<?= htmlspecialchars($c['tel_con'] ?? '') ?>" placeholder="Teléfono" class="w-full p-3 bg-white border border-emerald-200 rounded-xl">
                        <input type="email" name="correo_contacto" value="<?= htmlspecialchars($c['mail_con'] ?? '') ?>" placeholder="Email" class="w-full p-3 bg-white border border-emerald-200 rounded-xl">
                    </div>
                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-200 space-y-4">
                        <h3 class="font-bold text-slate-800 text-sm italic">Representante Legal (SII)</h3>
                        <input type="text" name="rut_representante" value="<?= htmlspecialchars($c['rut_representante'] ?? '') ?>" placeholder="RUT" class="w-full p-3 bg-white border border-slate-200 rounded-xl">
                        <input type="text" name="nombre_representante" value="<?= htmlspecialchars($c['nombre_rep'] ?? '') ?>" placeholder="Nombre" class="w-full p-3 bg-white border border-slate-200 rounded-xl">
                        <input type="text" name="clave_sii_representante" value="<?= htmlspecialchars($c['clave_rep'] ?? '') ?>" placeholder="Clave SII" class="w-full p-3 bg-white border border-slate-200 rounded-xl">
                    </div>
                </section>

                <section class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Observaciones</label>
                    <textarea name="observaciones" rows="3" placeholder="Notas internas..." class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-slate-300"><?= htmlspecialchars($c['observaciones'] ?? '') ?></textarea>
                </section>

                <div class="flex gap-4 pt-6 border-t border-slate-100">
                    <button type="submit" class="flex-1 py-4 bg-amber-600 text-white font-black rounded-2xl hover:bg-amber-700 shadow-xl uppercase tracking-widest text-sm transition-all">
                        Guardar Cambios
                    </button>
                    <a href="perfil_cliente.php?rut=<?= urlencode($rut) ?>" class="px-10 py-4 bg-slate-100 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 uppercase text-sm flex items-center">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</main>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
