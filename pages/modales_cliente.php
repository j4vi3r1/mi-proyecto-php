<div id="modalSocio" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-emerald-50/50">
            <h3 class="text-xl font-black text-emerald-900">Nuevo Socio</h3>
            <button onclick="closeModalSocio()" class="text-slate-400 hover:text-slate-600 text-2xl font-black">&times;</button>
        </div>
        <div class="p-8 space-y-4">
            <input type="text" id="m_socio_nombre" placeholder="Nombre Completo" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
            <input type="text" id="m_socio_rut" placeholder="RUT (ej: 12.345.678-9)" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
            <div class="grid grid-cols-2 gap-4">
                <input type="number" id="m_socio_porcentaje" placeholder="% Part." class="p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                <input type="number" id="m_socio_acciones" placeholder="N° Acciones" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
            </div>
            <label class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-100 transition-colors">
                <input type="checkbox" id="m_socio_es_rep" class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                <span class="text-sm font-bold text-slate-700">Es representante legal</span>
            </label>
            <button onclick="saveSocio()" class="w-full py-4 bg-emerald-600 text-white font-black rounded-2xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">Agregar Socio</button>
        </div>
    </div>
</div>

<div id="modalPropiedad" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-violet-50/50">
            <h3 class="text-xl font-black text-violet-900">Configurar Propiedad</h3>
            <button onclick="closeModalPropiedad()" class="text-slate-400 hover:text-slate-600 text-2xl font-black">&times;</button>
        </div>
        <div class="p-8 space-y-4">
            <select id="m_prop_tipo" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                <?php foreach($tiposDomicilio as $td): ?>
                    <option value="<?= $td['id_tipo'] ?>"><?= $td['nombre_tipo'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="m_prop_comuna" placeholder="Comuna" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-violet-500">
            <input type="text" id="m_prop_rol" placeholder="Rol de la propiedad" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
            <input type="text" id="m_prop_rut_prop" placeholder="RUT Propietario (si arrienda)" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
            <input type="number" id="m_prop_monto" placeholder="Monto Arriendo ($)" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
            <button onclick="savePropiedad()" class="w-full py-4 bg-violet-600 text-white font-black rounded-2xl hover:bg-violet-700 transition-all shadow-lg shadow-violet-100">Vincular Propiedad</button>
        </div>
    </div>
</div>

<div id="modalClave" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-rose-50/50">
            <h3 class="text-xl font-black text-rose-900">Nueva Clave</h3>
            <button onclick="closeModalClave()" class="text-slate-400 hover:text-slate-600 text-2xl font-black">&times;</button>
        </div>
        <div class="p-8 space-y-4">
            <select id="m_clave_tipo" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                <?php foreach($tiposClave as $tc): ?>
                    <option value="<?= $tc['id_tipo_clave'] ?>"><?= $tc['nombre_plataforma'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="m_clave_valor" placeholder="Contraseña o PIN" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-rose-500">
            <button onclick="saveClave()" class="w-full py-4 bg-rose-600 text-white font-black rounded-2xl hover:bg-rose-700 transition-all shadow-lg shadow-rose-100">Guardar Acceso</button>
        </div>
    </div>
</div>

<script>
/** Funciones de Control de UI **/
function openModalSocio() { document.getElementById('modalSocio').classList.remove('hidden'); }
function closeModalSocio() { document.getElementById('modalSocio').classList.add('hidden'); }

function openModalPropiedad() { document.getElementById('modalPropiedad').classList.remove('hidden'); }
function closeModalPropiedad() { document.getElementById('modalPropiedad').classList.add('hidden'); }

function openModalClave() { document.getElementById('modalClave').classList.remove('hidden'); }
function closeModalClave() { document.getElementById('modalClave').classList.add('hidden'); }

/** Lógica de Procesamiento de Datos **/

function saveSocio() {
    const nombre = document.getElementById('m_socio_nombre').value;
    const rut = document.getElementById('m_socio_rut').value;
    const porc = document.getElementById('m_socio_porcentaje').value;
    const acc = document.getElementById('m_socio_acciones').value;
    const esRep = document.getElementById('m_socio_es_rep').checked;

    if(!nombre || !rut) return alert("Nombre y RUT son obligatorios");

    agregarBadgeSocio(nombre, rut, porc, acc, esRep);
    closeModalSocio();
    
    // Resetear
    document.getElementById('m_socio_nombre').value = '';
    document.getElementById('m_socio_rut').value = '';
    document.getElementById('m_socio_porcentaje').value = '';
    document.getElementById('m_socio_acciones').value = '';
    document.getElementById('m_socio_es_rep').checked = false;
}

function savePropiedad() {
    const tipoSelect = document.getElementById('m_prop_tipo');
    const tipoNombre = tipoSelect.options[tipoSelect.selectedIndex].text;
    const tipoId = tipoSelect.value;
    const comuna = document.getElementById('m_prop_comuna').value;
    const rol = document.getElementById('m_prop_rol').value;
    const monto = document.getElementById('m_prop_monto').value;
    const rutProp = document.getElementById('m_prop_rut_prop').value;

    if(!comuna) return alert("La comuna es obligatoria");

    agregarBadgePropiedad(tipoNombre, comuna, tipoId, rol, monto, rutProp);
    closeModalPropiedad();

    document.getElementById('m_prop_comuna').value = '';
    document.getElementById('m_prop_rol').value = '';
    document.getElementById('m_prop_monto').value = '';
    document.getElementById('m_prop_rut_prop').value = '';
}

function saveClave() {
    const tipoSelect = document.getElementById('m_clave_tipo');
    const plataforma = tipoSelect.options[tipoSelect.selectedIndex].text;
    const tipoId = tipoSelect.value;
    const valor = document.getElementById('m_clave_valor').value;

    if(!valor) return alert("La contraseña no puede estar vacía");

    agregarBadgeClave(plataforma, tipoId, valor);
    closeModalClave();

    document.getElementById('m_clave_valor').value = '';
}
</script>