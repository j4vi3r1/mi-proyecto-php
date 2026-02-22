<?php
// 1. BUFFER DE SALIDA (Evita el error de "Cannot modify header information")
ob_start();

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. CONEXIÓN Y HEADER
include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

// 3. SEGURIDAD: Recuperar el rol del usuario
$rol_usuario = $_SESSION['rol'] ?? 0; 

// 4. CAPTURAR PERIODO SELECCIONADO
$mes_sel = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
$anio_sel = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

$meses = [
    1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio",
    7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre"
];

try {
    // --- LÓGICA DE PROCESAMIENTO (POST) ---

    // A. GENERAR MES COMPLETO / FALTANTES
    if (isset($_POST['accion']) && $_POST['accion'] === 'generar_mes') {
        $sql_generar = "INSERT INTO PagosMensuales (rut_contribuyente, mes, anio, monto_a_cobrar)
                        SELECT rut_contribuyente, :mes, :anio, honorario_mensual 
                        FROM Contribuyentes 
                        WHERE honorario_mensual > 0
                        ON CONFLICT (rut_contribuyente, mes, anio) DO NOTHING";
        $stmt_gen = $conn->prepare($sql_generar);
        $stmt_gen->execute(['mes' => $mes_sel, 'anio' => $anio_sel]);
        header("Location: cobranza.php?mes=$mes_sel&anio=$anio_sel");
        exit();
    }

    // B. GENERAR COBRO INDIVIDUAL
    if (isset($_POST['accion']) && $_POST['accion'] === 'generar_individual') {
        $rut = $_POST['rut_contribuyente'];
        $sql_h = "SELECT honorario_mensual FROM Contribuyentes WHERE rut_contribuyente = ?";
        $stmt_h = $conn->prepare($sql_h);
        $stmt_h->execute([$rut]);
        $monto = $stmt_h->fetchColumn();

        if ($monto) {
            $sql_ind = "INSERT INTO PagosMensuales (rut_contribuyente, mes, anio, monto_a_cobrar)
                        VALUES (:rut, :mes, :anio, :monto)
                        ON CONFLICT (rut_contribuyente, mes, anio) DO NOTHING";
            $stmt_ind = $conn->prepare($sql_ind);
            $stmt_ind->execute(['rut' => $rut, 'mes' => $mes_sel, 'anio' => $anio_sel, 'monto' => $monto]);
        }
        header("Location: cobranza.php?mes=$mes_sel&anio=$anio_sel");
        exit();
    }

    // C. ELIMINACIÓN MASIVA (SOLO ADMIN)
    if (isset($_POST['accion']) && $_POST['accion'] === 'limpiar_mes' && $rol_usuario == 2) {
        $stmt_del = $conn->prepare("DELETE FROM PagosMensuales WHERE mes = ? AND anio = ?");
        $stmt_del->execute([$mes_sel, $anio_sel]);
        header("Location: cobranza.php?mes=$mes_sel&anio=$anio_sel");
        exit();
    }

    // --- CONSULTAS PARA LA VISTA ---
    $total_deberian = $conn->query("SELECT COUNT(*) FROM Contribuyentes WHERE honorario_mensual > 0")->fetchColumn();
    $stmt_ya = $conn->prepare("SELECT COUNT(*) FROM PagosMensuales WHERE mes = :mes AND anio = :anio");
    $stmt_ya->execute(['mes' => $mes_sel, 'anio' => $anio_sel]);
    $ya_tienen = $stmt_ya->fetchColumn();
    $faltan_cobros = ($ya_tienen < $total_deberian);

    $query = "SELECT p.id_pago, c.razon_social, cont.nombre_contacto, cont.telefono, p.monto_a_cobrar, p.estado
              FROM PagosMensuales p
              JOIN Contribuyentes c ON p.rut_contribuyente = c.rut_contribuyente
              LEFT JOIN Contacto cont ON c.id_contacto = cont.id_contacto
              WHERE p.mes = :mes AND p.anio = :anio
              ORDER BY p.estado DESC, c.razon_social ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute(['mes' => $mes_sel, 'anio' => $anio_sel]);
    $cobros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_pendiente = 0; $total_pagado = 0;
    foreach ($cobros as $c) {
        if ($c['estado'] === 'Pendiente') $total_pendiente += $c['monto_a_cobrar'];
        else $total_pagado += $c['monto_a_cobrar'];
    }

    $todos_contri = $conn->query("SELECT rut_contribuyente, razon_social FROM Contribuyentes ORDER BY razon_social ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { $error = $e->getMessage(); }
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-6xl mx-auto">
        
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

        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <span class="text-[#7c83e5] font-black uppercase text-xs tracking-widest leading-none">Módulo Financiero</span>
                <h1 class="text-4xl font-black text-slate-800 italic tracking-tighter">Panel de <span class="text-[#5be2f8]">Cobranza</span></h1>
                <p class="text-slate-400 font-bold uppercase text-[10px] mt-1 italic"><?= $meses[$mes_sel] ?> de <?= $anio_sel ?></p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" class="flex items-center gap-2 bg-white p-2 rounded-full border border-slate-100 shadow-sm">
                    <select name="mes" class="bg-transparent border-none text-sm font-bold text-slate-600 focus:ring-0">
                        <?php foreach($meses as $n => $m): ?>
                            <option value="<?= $n ?>" <?= $n === $mes_sel ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="anio" class="bg-transparent border-none text-sm font-bold text-slate-600 focus:ring-0">
                        <?php for($a = 2024; $a <= date('Y')+1; $a++): ?>
                            <option value="<?= $a ?>" <?= $a === $anio_sel ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="bg-[#7c83e5] text-white p-2 rounded-full hover:bg-slate-800 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>

                <button onclick="document.getElementById('modalInd').classList.remove('hidden')" class="p-4 bg-white border border-slate-100 rounded-full text-slate-600 hover:text-[#7c83e5] shadow-sm transition-all" title="Añadir cobro individual">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </button>

                <?php if(!empty($cobros) && $rol_usuario == 2): ?>
                <form method="POST" onsubmit="return confirm('¿Eliminar TODO el mes? Esta acción no se puede deshacer.');">
                    <input type="hidden" name="accion" value="limpiar_mes">
                    <button type="submit" class="p-4 bg-rose-50 text-rose-500 rounded-full hover:bg-rose-500 hover:text-white transition-all shadow-sm shadow-rose-100" title="Reiniciar Mes">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex items-center gap-5">
                <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <div><p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1 leading-none italic">Por Cobrar</p><h3 class="text-3xl font-black text-slate-800 tracking-tight">$<?= number_format($total_pendiente, 0, ',', '.') ?></h3></div>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex items-center gap-5">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <div><p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1 leading-none italic">Recaudado</p><h3 class="text-3xl font-black text-slate-800 tracking-tight">$<?= number_format($total_pagado, 0, ',', '.') ?></h3></div>
            </div>
        </div>

        <?php if($faltan_cobros): ?>
            <div class="mb-8 bg-indigo-600 rounded-[2rem] p-6 shadow-xl shadow-indigo-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4 text-white">
                    <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                    <div>
                        <p class="font-black uppercase text-xs tracking-widest leading-none mb-1">Generación masiva</p>
                        <p class="text-indigo-100 text-sm font-medium italic">Hay <?= ($total_deberian - $ya_tienen) ?> registros pendientes de crear.</p>
                    </div>
                </div>
                <form method="POST">
                    <input type="hidden" name="accion" value="generar_mes">
                    <button type="submit" class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-black text-xs uppercase tracking-tighter hover:bg-slate-100 transition-all shadow-lg active:scale-95">
                        Generar Faltantes
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if(empty($cobros)): ?>
            <div class="bg-white rounded-[3rem] p-32 text-center border border-dashed border-slate-200">
                <p class="text-slate-400 font-black uppercase text-[10px] tracking-[0.3em]">No hay datos generados para este periodo</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-[3rem] border border-slate-100 shadow-xl overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                            <th class="px-8 py-6 text-center">Estado</th>
                            <th class="px-8 py-6">Contribuyente</th>
                            <th class="px-8 py-6">Honorario</th>
                            <th class="px-8 py-6 text-center">Gestión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($cobros as $c): ?>
                        <tr class="hover:bg-slate-50/50 transition-all group">
                            <td class="px-8 py-6 text-center">
                                <span class="inline-block w-3 h-3 <?= $c['estado'] === 'Pendiente' ? 'bg-amber-400 animate-pulse' : 'bg-emerald-500' ?> rounded-full ring-4 <?= $c['estado'] === 'Pendiente' ? 'ring-amber-100' : 'ring-emerald-100' ?>"></span>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-slate-800 font-black text-sm uppercase italic leading-tight"><?= htmlspecialchars($c['razon_social']) ?></p>
                                <p class="text-slate-400 text-[10px] font-bold"><?= htmlspecialchars($c['nombre_contacto'] ?? 'S/N') ?></p>
                            </td>
                            <td class="px-8 py-6 font-black text-slate-700">$<?= number_format($c['monto_a_cobrar'], 0, ',', '.') ?></td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center items-center gap-2">
                                    <button onclick="enviarWA('<?= $c['telefono'] ?>', '<?= $c['nombre_contacto'] ?>', '<?= $c['monto_a_cobrar'] ?>', '<?= $meses[$mes_sel] ?>')" class="p-3 bg-[#5be2f8]/10 text-[#2db5cc] rounded-xl hover:bg-[#5be2f8] hover:text-white transition-all active:scale-90" title="WhatsApp">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    </button>
                                    
                                    <?php if($c['estado'] === 'Pendiente'): ?>
                                    <form method="POST" action="marcar_pagado.php">
                                        <input type="hidden" name="id_pago" value="<?= $c['id_pago'] ?>">
                                        <input type="hidden" name="mes" value="<?= $mes_sel ?>"><input type="hidden" name="anio" value="<?= $anio_sel ?>">
                                        <button type="submit" class="p-3 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-90">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>

                                    <?php if($rol_usuario == 2): ?>
                                    <form method="POST" action="marcar_pagado.php" onsubmit="return confirm('¿Eliminar este cobro?');">
                                        <input type="hidden" name="id_pago" value="<?= $c['id_pago'] ?>">
                                        <input type="hidden" name="mes" value="<?= $mes_sel ?>"><input type="hidden" name="anio" value="<?= $anio_sel ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <button type="submit" class="p-3 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-all active:scale-90 shadow-sm shadow-rose-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<div id="modalInd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl relative overflow-hidden text-center">
        <div class="absolute top-0 left-0 w-full h-2 bg-[#5be2f8]"></div>
        <h2 class="text-3xl font-black text-slate-800 mb-2 italic tracking-tighter">Añadir <span class="text-[#5be2f8]">Cobro</span></h2>
        <p class="text-slate-500 text-xs mb-8 font-bold italic tracking-tight">Periodo: <span class="text-[#7c83e5]"><?= $meses[$mes_sel] ?></span></p>
        
        <form method="POST">
            <input type="hidden" name="accion" value="generar_individual">
            <select name="rut_contribuyente" class="w-full bg-slate-50 border-none rounded-2xl p-4 mb-8 font-bold text-slate-700 focus:ring-2 focus:ring-[#7c83e5] appearance-none text-center">
                <?php foreach($todos_contri as $tc): ?>
                    <option value="<?= $tc['rut_contribuyente'] ?>"><?= $tc['razon_social'] ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex gap-4">
                <button type="button" onclick="document.getElementById('modalInd').classList.add('hidden')" class="flex-1 py-4 text-slate-400 font-black uppercase text-[10px] tracking-widest">Cerrar</button>
                <button type="submit" class="flex-1 bg-[#7c83e5] text-white py-4 rounded-2xl font-black shadow-lg shadow-indigo-100 hover:bg-slate-800 transition-all">Generar</button>
            </div>
        </form>
    </div>
</div>

<script>
function enviarWA(tel, nombre, monto, mesNombre) {
    if(!tel || tel.trim() === '') {
        alert('Sin teléfono registrado.');
        return;
    }
    const telLimpio = tel.replace(/\D/g,'');
    const montoFmt = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(monto);
    const msj = `Hola ${nombre}, recordamos el pago de honorarios de ${mesNombre} por un monto de ${montoFmt}. Saludos de ArriendoPro.`;
    window.location.href = `whatsapp://send?phone=${telLimpio}&text=${encodeURIComponent(msj)}`;
}
</script>

<?php 
// 5. ENVIAR SALIDA DEL BUFFER
ob_end_flush();
include_once __DIR__ . '/../partials/footer.php'; 
?>
