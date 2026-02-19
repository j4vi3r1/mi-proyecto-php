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

    // Cargar Listas Existentes para pre-rellenar los badges
    $sociosActuales = $conn->prepare("SELECT * FROM Socios WHERE rut_contribuyente = ?");
    $sociosActuales->execute([$rut]);
    $listaSocios = $sociosActuales->fetchAll(PDO::FETCH_ASSOC);

    $clavesActuales = $conn->prepare("SELECT ca.*, tc.nombre_plataforma FROM ClavesAcceso ca JOIN TiposClave tc ON ca.id_tipo_clave = tc.id_tipo_clave WHERE ca.rut_contribuyente = ?");
    $clavesActuales->execute([$rut]);
    $listaClaves = $clavesActuales->fetchAll(PDO::FETCH_ASSOC);

    $propsActuales = $conn->prepare("SELECT p.*, td.nombre_tipo FROM PropiedadesContribuyente p JOIN TiposDomicilio td ON p.id_tipo_domicilio = td.id_tipo WHERE p.rut_contribuyente = ?");
    $propsActuales->execute([$rut]);
    $listaProps = $propsActuales->fetchAll(PDO::FETCH_ASSOC);

    // Cargar Maestros para Selects
    $tiposEmpresa = $conn->query("SELECT * FROM TiposEmpresa ORDER BY tipo ASC")->fetchAll(PDO::FETCH_ASSOC);
    $estados = $conn->query("SELECT * FROM EstadosContribuyente")->fetchAll(PDO::FETCH_ASSOC);
    $regimenes = $conn->query("SELECT * FROM Regimenes")->fetchAll(PDO::FETCH_ASSOC);
    $ivas = $conn->query("SELECT * FROM IVA")->fetchAll(PDO::FETCH_ASSOC);
    $tiposClave = $conn->query("SELECT * FROM TiposClave")->fetchAll(PDO::FETCH_ASSOC);
    $tiposDom = $conn->query("SELECT * FROM TiposDomicilio")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error cargando datos: " . $e->getMessage());
}
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-5xl mx-auto">
        <form id="form-editar-cliente" action="../app/actualizar_cliente.php" method="POST" class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
            
            <input type="hidden" name="rut_original" value="<?= htmlspecialchars($c['rut_contribuyente']) ?>">
            <input type="hidden" name="id_contacto_actual" value="<?= $c['id_contacto'] ?>">
            <input type="hidden" name="id_rep_actual" value="<?= $c['id_representante'] ?>">

            <div id="hidden-inputs-socios"></div>
            <div id="hidden-inputs-claves"></div>
            <div id="hidden-inputs-propiedades"></div>

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
                            <div id="lista-propiedades" class="flex flex-wrap gap-2 mb-4">
                                </div>
                            <button type="button" onclick="openModal('modal-propiedad')" class="w-full py-3 bg-white border-2 border-dashed border-violet-200 text-violet-600 font-bold rounded-xl hover:bg-violet-50 transition-all text-xs">+ Añadir Propiedad</button>
                        </div>
                    </section>

                    <section class="space-y-6">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-rose-600 flex items-center gap-2">
                            <span class="w-8 h-[2px] bg-rose-600"></span> Claves de Acceso
                        </h2>
                        <div class="p-6 bg-rose-50/30 border border-dashed border-rose-200 rounded-[2rem]">
                            <div id="lista-claves" class="flex flex-wrap gap-2 mb-4">
                                </div>
                            <button type="button" onclick="openModal('modal-clave')" class="w-full py-3 bg-white border-2 border-dashed border-rose-200 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition-all text-xs">+ Configurar Clave</button>
                        </div>
                    </section>
                </div>

                <section class="space-y-6">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600 flex items-center gap-2">
                        <span class="w-8 h-[2px] bg-emerald-600"></span> Socios y Participación
                    </h2>
                    <div class="p-6 bg-emerald-50/30 border border-dashed border-emerald-200 rounded-[2rem]">
                        <div id="lista-socios" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            </div>
                        <button type="button" onclick="openModal('modal-socio')" class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-emerald-200 text-emerald-600 font-bold rounded-xl hover:bg-emerald-600 hover:text-white transition-all text-xs">
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

<div id="modal-socio" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl">
        <h3 class="text-xl font-black text-slate-800 mb-6">Nuevo Socio</h3>
        <div class="space-y-4">
            <input type="text" id="s_rut" placeholder="RUT Socio" class="w-full p-3 border rounded-xl">
            <input type="text" id="s_nombre" placeholder="Nombre Completo" class="w-full p-3 border rounded-xl">
            <div class="grid grid-cols-2 gap-4">
                <input type="number" id="s_pct" placeholder="% Participación" class="p-3 border rounded-xl">
                <input type="number" id="s_acciones" placeholder="Cant. Acciones" class="p-3 border rounded-xl">
            </div>
            <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                <input type="checkbox" id="s_rep"> ¿Es Representante?
            </label>
            <div class="flex gap-2 pt-4">
                <button type="button" onclick="agregarSocio()" class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-xl">Añadir</button>
                <button type="button" onclick="closeModal('modal-socio')" class="px-6 py-3 bg-slate-100 rounded-xl">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-clave" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl">
        <h3 class="text-xl font-black text-slate-800 mb-6">Nueva Clave</h3>
        <div class="space-y-4">
            <select id="cl_tipo" class="w-full p-3 border rounded-xl">
                <?php foreach($tiposClave as $tc): ?>
                    <option value="<?= $tc['id_tipo_clave'] ?>"><?= $tc['nombre_plataforma'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="cl_user" placeholder="Usuario" class="w-full p-3 border rounded-xl">
            <input type="text" id="cl_pass" placeholder="Contraseña" class="w-full p-3 border rounded-xl">
            <input type="text" id="cl_obs" placeholder="Observaciones (opcional)" class="w-full p-3 border rounded-xl">
            <div class="flex gap-2 pt-4">
                <button type="button" onclick="agregarClave()" class="flex-1 py-3 bg-rose-600 text-white font-bold rounded-xl">Configurar</button>
                <button type="button" onclick="closeModal('modal-clave')" class="px-6 py-3 bg-slate-100 rounded-xl">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-propiedad" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] w-full max-w-lg p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
        <h3 class="text-xl font-black text-slate-800 mb-6">Nuevo Domicilio</h3>
        <div class="space-y-4">
            <select id="p_tipo" class="w-full p-3 border rounded-xl">
                <?php foreach($tiposDom as $td): ?>
                    <option value="<?= $td['id_tipo'] ?>"><?= $td['nombre_tipo'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="p_comuna" placeholder="Comuna" class="w-full p-3 border rounded-xl">
            <input type="text" id="p_rol" placeholder="ROL Propiedad (opcional)" class="w-full p-3 border rounded-xl">
            <div class="p-4 bg-slate-50 rounded-xl space-y-3">
                <p class="text-xs font-bold text-slate-500 uppercase">Si es arrendado:</p>
                <input type="text" id="p_rut_prop" placeholder="RUT Propietario" class="w-full p-2 border rounded-lg text-sm">
                <input type="number" id="p_monto" placeholder="Monto Arriendo" class="w-full p-2 border rounded-lg text-sm">
            </div>
            <div class="flex gap-2 pt-4">
                <button type="button" onclick="agregarPropiedad()" class="flex-1 py-3 bg-violet-600 text-white font-bold rounded-xl">Añadir</button>
                <button type="button" onclick="closeModal('modal-propiedad')" class="px-6 py-3 bg-slate-100 rounded-xl">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Arrays globales para manejar la data
let socios = <?= json_encode($listaSocios) ?>;
let claves = <?= json_encode($listaClaves) ?>;
let propiedades = <?= json_encode($listaProps) ?>;

// Al cargar la página, dibujar lo existente
window.onload = () => {
    actualizarVistas();
};

function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }

function actualizarVistas() {
    const sCont = document.getElementById('lista-socios');
    const sHidden = document.getElementById('hidden-inputs-socios');
    sCont.innerHTML = ''; sHidden.innerHTML = '';
    socios.forEach((s, i) => {
        sCont.innerHTML += `<div class="p-3 bg-white border border-emerald-100 rounded-xl shadow-sm flex flex-col relative group">
            <button type="button" onclick="eliminarSocio(${i})" class="absolute top-1 right-1 text-rose-300 hover:text-rose-500 opacity-0 group-hover:opacity-100">×</button>
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Socio</span>
            <span class="text-sm font-bold text-slate-700">${s.nombre_socio}</span>
            <span class="text-xs text-slate-400">${s.rut_socio} (${s.porcentaje_participacion}%)</span>
        </div>`;
        sHidden.innerHTML += `<input type="hidden" name="socios[${i}][rut]" value="${s.rut_socio}">
            <input type="hidden" name="socios[${i}][nombre]" value="${s.nombre_socio}">
            <input type="hidden" name="socios[${i}][pct]" value="${s.porcentaje_participacion}">
            <input type="hidden" name="socios[${i}][acciones]" value="${s.cantidad_acciones || 0}">
            <input type="hidden" name="socios[${i}][rep]" value="${s.es_representante ? 1 : 0}">`;
    });

    // Repetir lógica para claves y propiedades...
    const cCont = document.getElementById('lista-claves');
    const cHidden = document.getElementById('hidden-inputs-claves');
    cCont.innerHTML = ''; cHidden.innerHTML = '';
    claves.forEach((cl, i) => {
        const plat = cl.nombre_plataforma || document.querySelector(`#cl_tipo option[value="${cl.id_tipo_clave}"]`).text;
        cCont.innerHTML += `<div class="px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-[10px] font-bold flex items-center gap-2">
            ${plat} <button type="button" onclick="eliminarClave(${i})">×</button>
        </div>`;
        cHidden.innerHTML += `<input type="hidden" name="claves[${i}][id_tipo]" value="${cl.id_tipo_clave}">
            <input type="hidden" name="claves[${i}][user]" value="${cl.usuario}">
            <input type="hidden" name="claves[${i}][pass]" value="${cl.contrasena}">
            <input type="hidden" name="claves[${i}][obs]" value="${cl.observaciones || ''}">`;
    });

    const pCont = document.getElementById('lista-propiedades');
    const pHidden = document.getElementById('hidden-inputs-propiedades');
    pCont.innerHTML = ''; pHidden.innerHTML = '';
    propiedades.forEach((p, i) => {
        const tDom = p.nombre_tipo || document.querySelector(`#p_tipo option[value="${p.id_tipo_domicilio}"]`).text;
        pCont.innerHTML += `<div class="px-3 py-1 bg-violet-100 text-violet-700 rounded-full text-[10px] font-bold flex items-center gap-2">
            ${tDom}: ${p.comuna} <button type="button" onclick="eliminarProp(${i})">×</button>
        </div>`;
        pHidden.innerHTML += `<input type="hidden" name="propiedades[${i}][id_tipo]" value="${p.id_tipo_domicilio}">
            <input type="hidden" name="propiedades[${i}][comuna]" value="${p.comuna}">
            <input type="hidden" name="propiedades[${i}][rol]" value="${p.rol_propiedad || ''}">
            <input type="hidden" name="propiedades[${i}][rut_prop]" value="${p.rut_propietario || ''}">
            <input type="hidden" name="propiedades[${i}][monto]" value="${p.monto_arriendo || 0}">`;
    });
}

// Funciones para añadir (disparadas desde los modales)
function agregarSocio() {
    socios.push({
        nombre_socio: document.getElementById('s_nombre').value,
        rut_socio: document.getElementById('s_rut').value,
        porcentaje_participacion: document.getElementById('s_pct').value,
        cantidad_acciones: document.getElementById('s_acciones').value,
        es_representante: document.getElementById('s_rep').checked
    });
    actualizarVistas(); closeModal('modal-socio');
}

function agregarClave() {
    claves.push({
        id_tipo_clave: document.getElementById('cl_tipo').value,
        usuario: document.getElementById('cl_user').value,
        contrasena: document.getElementById('cl_pass').value,
        observaciones: document.getElementById('cl_obs').value
    });
    actualizarVistas(); closeModal('modal-clave');
}

function agregarPropiedad() {
    propiedades.push({
        id_tipo_domicilio: document.getElementById('p_tipo').value,
        comuna: document.getElementById('p_comuna').value,
        rol_propiedad: document.getElementById('p_rol').value,
        rut_propietario: document.getElementById('p_rut_prop').value,
        monto_arriendo: document.getElementById('p_monto').value
    });
    actualizarVistas(); closeModal('modal-propiedad');
}

// Funciones para eliminar del array
function eliminarSocio(i) { socios.splice(i, 1); actualizarVistas(); }
function eliminarClave(i) { claves.splice(i, 1); actualizarVistas(); }
function eliminarProp(i) { propiedades.splice(i, 1); actualizarVistas(); }
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
