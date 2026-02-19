<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Seguridad básica
if (!isset($_SESSION['es_empleado']) || $_SESSION['es_empleado'] !== true) {
    header("Location: ../public/index.php");
    exit();
}

include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

// 2. Cargar opciones de tablas maestras
try {
    $tiposEmpresa = $conn->query("SELECT * FROM TiposEmpresa ORDER BY tipo ASC")->fetchAll(PDO::FETCH_ASSOC);
    $estados = $conn->query("SELECT * FROM EstadosContribuyente")->fetchAll(PDO::FETCH_ASSOC);
    $regimenes = $conn->query("SELECT * FROM Regimenes")->fetchAll(PDO::FETCH_ASSOC);
    $ivas = $conn->query("SELECT * FROM IVA")->fetchAll(PDO::FETCH_ASSOC);
    $tiposClave = $conn->query("SELECT * FROM TiposClave")->fetchAll(PDO::FETCH_ASSOC);
    $tiposDomicilio = $conn->query("SELECT * FROM TiposDomicilio")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error cargando maestros: " . $e->getMessage());
}
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-5xl mx-auto">
        <form action="../app/insertar_cliente.php" method="POST" class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
            
            <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center gap-4">
                <div class="p-3 bg-sky-600 text-white rounded-2xl shadow-lg shadow-sky-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-7h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Registro de Nuevo Contribuyente</h1>
                    <p class="text-sm text-slate-400 font-medium">Complete el perfil tributario y legal del cliente.</p>
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
                            <input type="text" name="rut_contribuyente" placeholder="12.345.678-k" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700">Razón Social</label>
                            <input type="text" name="razon_social" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
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
                        <div id="lista-socios" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4"></div>
                        <button type="button" onclick="openModalSocio()" class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-emerald-200 text-emerald-600 font-bold rounded-xl hover:bg-emerald-600 hover:text-white transition-all text-xs">
                            + Añadir Socio
                        </button>
                    </div>
                </section>

                <section class="space-y-6">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-600 flex items-center gap-2">
                        <span class="w-8 h-[2px] bg-indigo-600"></span> Perfil Tributario y Software
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Estado</label>
                            <select name="id_estado" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <?php foreach($estados as $e): ?>
                                    <option value="<?= $e['id_estado'] ?>"><?= $e['nombre_estado'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Tipo Empresa</label>
                            <select name="id_tipo_empresa" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <?php foreach($tiposEmpresa as $te): ?>
                                    <option value="<?= $te['id_tipo'] ?>"><?= $te['tipo'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Régimen</label>
                            <select name="id_regimen" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <?php foreach($regimenes as $r): ?>
                                    <option value="<?= $r['id_regimen'] ?>"><?= $r['nombre_regimen'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">IVA</label>
                            <select name="id_iva" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <?php foreach($ivas as $i): ?>
                                    <option value="<?= $i['id_iva'] ?>"><?= $i['tipo_iva'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Software Contable</label>
                            <input type="text" name="software" placeholder="Ej: Nubox, Defontana" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Inicio Actividades</label>
                            <input type="date" name="inicio_actividades" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Tasa PPM (%)</label>
                            <input type="number" step="0.01" name="tasa_ppm" placeholder="0.00" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Hon. Mensual ($)</label>
                            <input type="number" name="honorario_mensual" placeholder="0" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Hon. Renta ($)</label>
                            <input type="number" name="honorario_renta" placeholder="0" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        </div>
                    </div>

                    <div class="flex gap-8 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="remuneracion" class="w-5 h-5 text-sky-600 rounded">
                            <span class="text-sm font-bold text-slate-700">Maneja Remuneraciones</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="facturacion" class="w-5 h-5 text-sky-600 rounded">
                            <span class="text-sm font-bold text-slate-700">Maneja Facturación</span>
                        </label>
                    </div>
                </section>

                <section class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="p-6 bg-emerald-50/50 rounded-3xl border border-emerald-100 space-y-4">
                        <h3 class="font-bold text-emerald-800 text-sm italic">Contacto Principal (Cliente)</h3>
                        <input type="text" name="nombre_contacto" placeholder="Nombre" class="w-full p-3 bg-white border border-emerald-200 rounded-xl">
                        <input type="text" name="telefono_contacto" placeholder="Teléfono" class="w-full p-3 bg-white border border-emerald-200 rounded-xl">
                        <input type="email" name="correo_contacto" placeholder="Email" class="w-full p-3 bg-white border border-emerald-200 rounded-xl">
                    </div>
                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-200 space-y-4">
                        <h3 class="font-bold text-slate-800 text-sm italic">Representante Legal (SII)</h3>
                        <input type="text" name="rut_representante" placeholder="RUT" class="w-full p-3 bg-white border border-slate-200 rounded-xl">
                        <input type="text" name="nombre_representante" placeholder="Nombre" class="w-full p-3 bg-white border border-slate-200 rounded-xl">
                        <input type="text" name="clave_sii_representante" placeholder="Clave SII" class="w-full p-3 bg-white border border-slate-200 rounded-xl">
                    </div>
                </section>

                <section class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Observaciones</label>
                    <textarea name="observaciones" rows="3" placeholder="Notas internas..." class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-slate-300"></textarea>
                </section>

                <div class="flex gap-4 pt-6 border-t border-slate-100">
                    <button type="submit" class="flex-1 py-4 bg-sky-600 text-white font-black rounded-2xl hover:bg-sky-700 shadow-xl uppercase tracking-widest text-sm transition-all">
                        Registrar Contribuyente
                    </button>
                    <a href="clientes.php" class="px-10 py-4 bg-slate-100 text-slate-500 font-bold rounded-2xl hover:bg-slate-200 uppercase text-sm flex items-center">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</main>

<div id="modalClave" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full overflow-hidden">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 italic">Configurar Clave</h3>
            <button type="button" onclick="closeModalClave()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>
        <div class="p-8 space-y-5">
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400">Plataforma</label>
                <select id="m_clave_tipo" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <?php foreach($tiposClave as $tc): ?>
                        <option value="<?= $tc['id_tipo_clave'] ?>"><?= $tc['nombre_plataforma'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400">Usuario / RUT</label>
                <input type="text" id="m_clave_usuario" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none" placeholder="Ej: 12.345.678-9">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400">Contraseña</label>
                <input type="text" id="m_clave_valor" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none" placeholder="******">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400">Observación / Notas</label>
                <input type="text" id="m_clave_obs" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none" placeholder="Ej: Pregunta secreta o coordenadas">
            </div>
        </div>
        <div class="p-8 bg-slate-50 flex gap-3">
            <button type="button" onclick="addClaveToList()" class="flex-1 py-4 bg-rose-600 text-white font-black rounded-2xl">Vincular</button>
            <button type="button" onclick="closeModalClave()" class="px-6 py-4 bg-white rounded-2xl border font-bold">Cerrar</button>
        </div>
    </div>
</div>

<div id="modalPropiedad" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 italic">Nueva Propiedad</h3>
            <button type="button" onclick="closeModalPropiedad()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>
        <div class="p-8 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <select id="m_tipo" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <?php foreach($tiposDomicilio as $td): ?>
                        <option value="<?= $td['id_tipo'] ?>"><?= $td['nombre_tipo'] ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="m_comuna" placeholder="Comuna" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <input type="text" id="m_rol" placeholder="Rol Propiedad" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
                <input type="number" id="m_monto" placeholder="Monto Arriendo" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            </div>
            <input type="text" id="m_propietario" placeholder="RUT Propietario" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
        </div>
        <div class="p-8 bg-slate-50 flex gap-3">
            <button type="button" onclick="addPropiedadToList()" class="flex-1 py-4 bg-violet-600 text-white font-black rounded-2xl">Añadir</button>
            <button type="button" onclick="closeModalPropiedad()" class="px-6 py-4 bg-white rounded-2xl border font-bold">Cancelar</button>
        </div>
    </div>
</div>

<div id="modalSocio" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full overflow-hidden">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 italic">Datos del Socio</h3>
            <button type="button" onclick="closeModalSocio()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>
        <div class="p-8 space-y-4">
            <input type="text" id="s_rut" placeholder="RUT Socio" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <input type="text" id="s_nombre" placeholder="Nombre Completo" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <div class="grid grid-cols-2 gap-4">
                <input type="number" id="s_porcentaje" placeholder="% Part." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
                <input type="number" id="s_acciones" placeholder="N° Acciones" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            </div>
            <label class="flex items-center gap-2 p-2 cursor-pointer">
                <input type="checkbox" id="s_es_rep" class="w-4 h-4 text-emerald-600 rounded">
                <span class="text-sm font-bold text-slate-600">Es representante legal</span>
            </label>
        </div>
        <div class="p-8 bg-slate-50 flex gap-3">
            <button type="button" onclick="addSocioToList()" class="flex-1 py-4 bg-emerald-600 text-white font-black rounded-2xl">Vincular Socio</button>
            <button type="button" onclick="closeModalSocio()" class="px-6 py-4 bg-white rounded-2xl border font-bold">Cerrar</button>
        </div>
    </div>
</div>

<script>
let propCount = 0;
let claveCount = 0;
let socioCount = 0;

// MODALES
function openModalPropiedad() { document.getElementById('modalPropiedad').classList.remove('hidden'); }
function closeModalPropiedad() { document.getElementById('modalPropiedad').classList.add('hidden'); }
function openModalClave() { document.getElementById('modalClave').classList.remove('hidden'); }
function closeModalClave() { document.getElementById('modalClave').classList.add('hidden'); }
function openModalSocio() { document.getElementById('modalSocio').classList.remove('hidden'); }
function closeModalSocio() { document.getElementById('modalSocio').classList.add('hidden'); }

// LOGICA CLAVES (Actualizada para incluir Usuario)
function addClaveToList() {
    const tipoId = document.getElementById('m_clave_tipo').value;
    const tipoNombre = document.getElementById('m_clave_tipo').options[document.getElementById('m_clave_tipo').selectedIndex].text;
    const usuario = document.getElementById('m_clave_usuario').value;
    const valor = document.getElementById('m_clave_valor').value;
    const obs = document.getElementById('m_clave_obs').value; // Nueva constante
    
    if(!valor || !usuario) return alert("Ingrese usuario y clave");

    const badge = document.createElement('div');
    badge.className = "flex flex-col bg-rose-50 text-rose-700 px-3 py-2 rounded-xl text-[10px] font-bold border border-rose-200 animate-in zoom-in duration-300 relative pr-8";
    
    // Si hay observación, la mostramos en pequeño abajo del usuario
    const obsHtml = obs ? `<span class="text-[8px] text-rose-400 italic mt-1 border-t border-rose-100 pt-1">${obs}</span>` : '';

    badge.innerHTML = `
        <span class="text-rose-900 uppercase">${tipoNombre}</span>
        <span class="font-mono text-[9px] text-rose-500">${usuario}</span>
        ${obsHtml}
        <input type="hidden" name="claves[${claveCount}][id_tipo]" value="${tipoId}">
        <input type="hidden" name="claves[${claveCount}][usuario]" value="${usuario}">
        <input type="hidden" name="claves[${claveCount}][clave]" value="${valor}">
        <input type="hidden" name="claves[${claveCount}][observacion]" value="${obs}">
        <button type="button" onclick="this.parentElement.remove()" class="absolute right-2 top-2 hover:text-red-500 text-lg">&times;</button>
    `;
    
    document.getElementById('lista-claves').appendChild(badge);
    claveCount++;
    
    // Limpiar campos del modal
    document.getElementById('m_clave_usuario').value = "";
    document.getElementById('m_clave_valor').value = "";
    document.getElementById('m_clave_obs').value = "";
    closeModalClave();
}

// LOGICA PROPIEDADES
function addPropiedadToList() {
    const tipoId = document.getElementById('m_tipo').value;
    const tipoNombre = document.getElementById('m_tipo').options[document.getElementById('m_tipo').selectedIndex].text;
    const comuna = document.getElementById('m_comuna').value;
    const rol = document.getElementById('m_rol').value;
    const monto = document.getElementById('m_monto').value;
    const rutP = document.getElementById('m_propietario').value;

    if(!comuna) return alert("Ingrese comuna");

    const badge = document.createElement('div');
    badge.className = "flex items-center gap-2 bg-violet-100 text-violet-700 px-3 py-2 rounded-xl text-[10px] font-bold border border-violet-200 animate-in zoom-in duration-300";
    badge.innerHTML = `
        ${tipoNombre}: ${comuna}
        <input type="hidden" name="propiedades[${propCount}][id_tipo]" value="${tipoId}">
        <input type="hidden" name="propiedades[${propCount}][comuna]" value="${comuna}">
        <input type="hidden" name="propiedades[${propCount}][rol]" value="${rol}">
        <input type="hidden" name="propiedades[${propCount}][monto]" value="${monto}">
        <input type="hidden" name="propiedades[${propCount}][rut_prop]" value="${rutP}">
        <button type="button" onclick="this.parentElement.remove()" class="ml-1 hover:text-red-500 text-lg">&times;</button>
    `;
    document.getElementById('lista-propiedades').appendChild(badge);
    propCount++;
    closeModalPropiedad();
}

// LOGICA SOCIOS
function addSocioToList() {
    const rut = document.getElementById('s_rut').value;
    const nombre = document.getElementById('s_nombre').value;
    const porc = document.getElementById('s_porcentaje').value;
    const acc = document.getElementById('s_acciones').value;
    const esRep = document.getElementById('s_es_rep').checked;

    if(!rut || !nombre) return alert("Datos del socio incompletos");

    const borderClass = esRep ? 'border-emerald-500 ring-2 ring-emerald-50 shadow-md' : 'border-emerald-100';
    const repBadge = esRep ? '<span class="ml-auto bg-emerald-500 text-white text-[8px] px-1.5 py-0.5 rounded-full">Rep Legal</span>' : '';

    const card = document.createElement('div');
    card.className = `p-4 bg-emerald-50 border ${borderClass} rounded-2xl relative animate-in zoom-in duration-300`;
    card.innerHTML = `
        <button type="button" onclick="this.parentElement.remove()" class="absolute top-2 right-2 text-emerald-300 hover:text-red-500 text-xl font-black">&times;</button>
        <div class="flex items-center gap-1 mb-1">
            <p class="text-xs font-black text-emerald-900 uppercase truncate pr-4">${nombre}</p>
            ${repBadge}
        </div>
        <p class="text-[10px] text-emerald-600 font-mono mb-2">${rut}</p>
        <div class="flex justify-between mt-2">
            <span class="text-[10px] font-bold bg-white px-2 py-0.5 rounded-lg border border-emerald-100 text-emerald-600">${porc}%</span>
            <span class="text-[10px] bg-white px-2 py-0.5 rounded-lg border border-emerald-100 text-slate-500 font-bold">Acc: ${acc}</span>
        </div>
        <input type="hidden" name="socios[${socioCount}][rut]" value="${rut}">
        <input type="hidden" name="socios[${socioCount}][nombre]" value="${nombre}">
        <input type="hidden" name="socios[${socioCount}][porcentaje]" value="${porc}">
        <input type="hidden" name="socios[${socioCount}][acciones]" value="${acc}">
        <input type="hidden" name="socios[${socioCount}][es_rep]" value="${esRep ? '1' : '0'}">
    `;
    document.getElementById('lista-socios').appendChild(card);
    socioCount++;
    
    // Limpiar modal
    document.getElementById('s_rut').value = "";
    document.getElementById('s_nombre').value = "";
    document.getElementById('s_porcentaje').value = "";
    document.getElementById('s_acciones').value = "";
    document.getElementById('s_es_rep').checked = false;
    
    closeModalSocio();
}
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>