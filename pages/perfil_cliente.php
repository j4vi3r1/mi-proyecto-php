<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SEGURIDAD: Empleados (1) y Administradores (2)
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], [1, 2])) {
    header("Location: ../public/index.php?error=acceso_denegado");
    exit();
}

include_once __DIR__ . '/../app/conexion.php';
include_once __DIR__ . '/../partials/header.php';

$rut = $_GET['rut'] ?? null;
if (!$rut) { die("Error: RUT no especificado."); }

try {
    // 1. Consulta principal con todos los JOINs necesarios
    $stmt = $conn->prepare("
        SELECT c.*, 
               e.nombre_estado, 
               i.tipo_iva, 
               r.nombre_regimen, 
               t.tipo as tipo_empresa_nombre,
               rep.nombre as nombre_rep, 
               rep.rut_representante as rut_rep_real, 
               rep.clave_sii as clave_rep,
               con.nombre_contacto, 
               con.telefono as tel_con, 
               con.correo as mail_con
        FROM Contribuyentes c
        LEFT JOIN EstadosContribuyente e ON c.id_estado = e.id_estado
        LEFT JOIN IVA i ON c.id_iva = i.id_iva
        LEFT JOIN Regimenes r ON c.id_regimen = r.id_regimen
        LEFT JOIN TiposEmpresa t ON c.id_tipo_empresa = t.id_tipo
        LEFT JOIN Representantes rep ON c.id_representante = rep.id_representante
        LEFT JOIN Contacto con ON c.id_contacto = con.id_contacto
        WHERE c.rut_contribuyente = ?
    ");
    $stmt->execute([$rut]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) { die("Error: Contribuyente no encontrado."); }

    // 2. Consultar Propiedades
    $propiedadesStmt = $conn->prepare("
        SELECT p.*, td.nombre_tipo 
        FROM PropiedadesContribuyente p
        JOIN TiposDomicilio td ON p.id_tipo_domicilio = td.id_tipo
        WHERE p.rut_contribuyente = ?
    ");
    $propiedadesStmt->execute([$rut]);
    $listaPropiedades = $propiedadesStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Consultar Claves
    $clavesStmt = $conn->prepare("
        SELECT ca.*, tc.nombre_plataforma 
        FROM ClavesAcceso ca
        JOIN TiposClave tc ON ca.id_tipo_clave = tc.id_tipo_clave
        WHERE ca.rut_contribuyente = ?
    ");
    $clavesStmt->execute([$rut]);
    $listaClaves = $clavesStmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Consultar Socios
    $sociosStmt = $conn->prepare("SELECT * FROM Socios WHERE rut_contribuyente = ?");
    $sociosStmt->execute([$rut]);
    $listaSocios = $sociosStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error crítico: " . $e->getMessage());
}

// Función auxiliar para detectar booleanos (soporta 't'/true de Postgres y 1/0 de PHP)
function esVerdadero($valor) {
    return ($valor === true || $valor === 't' || $valor == 1 || $valor === 'true');
}
?>

<main class="min-h-screen bg-slate-50 py-8 px-4">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="text-sky-600 font-bold text-sm uppercase tracking-widest"><?= htmlspecialchars($cliente['tipo_empresa_nombre'] ?? 'Empresa') ?></span>
                    <?php if(esVerdadero($cliente['remuneracion'])): ?>
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-bold rounded-lg uppercase">Remuneraciones</span>
                    <?php endif; ?>
                    <?php if(esVerdadero($cliente['facturacion'])): ?>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-lg uppercase">Facturación</span>
                    <?php endif; ?>
                </div>
                <h1 class="text-3xl font-black text-slate-800"><?= htmlspecialchars($cliente['razon_social']) ?></h1>
                <p class="text-slate-500 font-medium">
                    RUT: <?= htmlspecialchars($cliente['rut_contribuyente']) ?> • 
                    <span class="text-emerald-600 font-bold">● <?= htmlspecialchars($cliente['nombre_estado'] ?? 'Vigente') ?></span>
                </p>
            </div>
            <div class="flex gap-2">
                <a href="editar_cliente.php?rut=<?= urlencode($rut) ?>" class="px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition-all text-sm">Editar Datos</a>
                
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 2): ?>
                <button onclick="confirmarEliminar('<?= $rut ?>')" class="px-4 py-3 bg-rose-50 text-rose-500 rounded-2xl hover:bg-rose-100 border border-rose-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                <?php endif; ?>

                <button onclick="window.print()" class="px-4 py-3 bg-white border border-slate-200 text-slate-400 rounded-2xl hover:bg-slate-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                
                <section class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                        <div class="w-2 h-6 bg-sky-500 rounded-full"></div> Datos Tributarios
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Tipo de Empresa</p>
                            <p class="text-slate-700 font-semibold"><?= htmlspecialchars($cliente['tipo_empresa_nombre'] ?? 'No definido') ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Régimen</p>
                            <p class="text-slate-700 font-semibold"><?= htmlspecialchars($cliente['nombre_regimen'] ?? 'No definido') ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Tipo IVA</p>
                            <p class="text-slate-700 font-semibold"><?= htmlspecialchars($cliente['tipo_iva'] ?? 'No definido') ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Inicio Actividades</p>
                            <p class="text-slate-700 font-semibold"><?= $cliente['inicio_actividades'] ? date('d/m/Y', strtotime($cliente['inicio_actividades'])) : 'No registrado' ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-bold text-slate-400 uppercase">Software Contable</p>
                            <p class="text-slate-700 font-semibold"><?= htmlspecialchars($cliente['software'] ?: 'No usa / No registrado') ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Tasa PPM</p>
                            <p class="text-xl font-black text-sky-600"><?= number_format($cliente['tasa_ppm'] ?? 0, 2) ?>%</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Honorario Mensual</p>
                            <p class="text-xl font-black text-slate-800">$<?= number_format($cliente['honorario_mensual'] ?? 0, 0, ',', '.') ?></p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                            <p class="text-[10px] font-black text-slate-400 uppercase">H. Renta Anual</p>
                            <p class="text-xl font-black text-slate-800">$<?= number_format($cliente['honorario_renta'] ?? 0, 0, ',', '.') ?></p>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                        <div class="w-2 h-6 bg-indigo-500 rounded-full"></div> Direcciones y Domicilios
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach($listaPropiedades as $prop): ?>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="flex justify-between items-start mb-2">
                                <span class="px-2 py-1 bg-white text-indigo-600 text-[10px] font-black rounded-lg border border-indigo-100 uppercase"><?= htmlspecialchars($prop['nombre_tipo']) ?></span>
                                <span class="text-xs font-mono font-bold text-slate-400">ROL: <?= htmlspecialchars($prop['rol_propiedad'] ?: 'S/R') ?></span>
                            </div>
                            <p class="text-slate-700 font-bold"><?= htmlspecialchars($prop['comuna']) ?></p>
                            <p class="text-xs text-slate-500 mt-1">Propietario: <?= htmlspecialchars($prop['rut_propietario'] ?: 'Mismo contribuyente') ?></p>
                            <?php if($prop['monto_arriendo'] > 0): ?>
                                <p class="text-sm font-black text-indigo-600 mt-2">Arriendo: $<?= number_format($prop['monto_arriendo'], 0, ',', '.') ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($listaPropiedades)) echo "<p class='text-slate-400 text-sm italic col-span-2'>No hay domicilios registrados.</p>"; ?>
                    </div>
                </section>

                <section class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                        <div class="w-2 h-6 bg-amber-500 rounded-full"></div> Composición Societaria
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-slate-400 uppercase text-[10px] font-black border-b border-slate-100">
                                    <th class="pb-3">Socio</th>
                                    <th class="pb-3 text-center">Participación</th>
                                    <th class="pb-3 text-center">Acciones</th>
                                    <th class="pb-3 text-right">Cargo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($listaSocios as $socio): ?>
                                <tr class="group">
                                    <td class="py-4">
                                        <p class="font-bold text-slate-700"><?= htmlspecialchars($socio['nombre_socio']) ?></p>
                                        <p class="text-xs text-slate-400"><?= htmlspecialchars($socio['rut_socio']) ?></p>
                                    </td>
                                    <td class="py-4 text-center font-bold text-slate-600"><?= $socio['porcentaje_participacion'] ?>%</td>
                                    <td class="py-4 text-center text-slate-500"><?= number_format($socio['cantidad_acciones'], 0, ',', '.') ?></td>
                                    <td class="py-4 text-right">
                                        <?php if(esVerdadero($socio['es_representante'])): ?>
                                            <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black uppercase">Representante</span>
                                        <?php else: ?>
                                            <span class="text-slate-300 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if(empty($listaSocios)) echo "<p class='text-slate-400 text-sm italic py-4 text-center'>No se registran socios.</p>"; ?>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                
                <section class="bg-emerald-600 rounded-3xl p-6 shadow-lg shadow-emerald-100 text-white">
                    <h2 class="font-bold mb-4 opacity-80 uppercase text-xs tracking-widest">Persona de Contacto</h2>
                    <p class="text-xl font-black"><?= htmlspecialchars($cliente['nombre_contacto'] ?: 'Sin asignar') ?></p>
                    <div class="mt-4 space-y-2 text-sm opacity-90">
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> 
                            <?= htmlspecialchars($cliente['tel_con'] ?: 'Sin teléfono') ?>
                        </p>
                        <p class="flex items-center gap-2 break-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> 
                            <?= htmlspecialchars($cliente['mail_con'] ?: 'Sin correo') ?>
                        </p>
                    </div>
                </section>

                <section class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                        <div class="w-2 h-6 bg-rose-500 rounded-full"></div> Claves de Acceso
                    </h2>
                    <div class="space-y-4">
                        <?php foreach($listaClaves as $cl): ?>
                            <div class="p-4 border border-slate-100 rounded-2xl hover:bg-slate-50 transition-colors">
                                <p class="text-[10px] font-black text-slate-400 uppercase mb-2"><?= htmlspecialchars($cl['nombre_plataforma']) ?></p>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center bg-slate-50 px-2 py-1 rounded-lg">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">User:</span>
                                        <div class="flex items-center gap-2">
                                            <code class="text-xs font-bold text-slate-600"><?= htmlspecialchars($cl['usuario']) ?></code>
                                            <button onclick="copy('<?= $cl['usuario'] ?>')" class="text-slate-300 hover:text-sky-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center bg-rose-50 px-2 py-1 rounded-lg">
                                        <span class="text-[10px] text-rose-400 font-bold uppercase">Pass:</span>
                                        <div class="flex items-center gap-2">
                                            <code class="text-xs font-bold text-rose-600"><?= htmlspecialchars($cl['contrasena']) ?></code>
                                            <button onclick="copy('<?= $cl['contrasena'] ?>')" class="text-rose-300 hover:text-rose-600"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                                        </div>
                                    </div>
                                    <?php if(!empty($cl['observaciones'])): ?>
                                        <p class="text-[9px] text-slate-400 italic">Nota: <?= htmlspecialchars($cl['observaciones']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($listaClaves)) echo "<p class='text-slate-400 text-xs italic'>No hay claves registradas.</p>"; ?>
                    </div>
                </section>

                <section class="bg-slate-800 rounded-3xl p-6 text-white shadow-xl">
                    <h3 class="text-xs font-bold text-slate-400 uppercase mb-4">Representante Legal</h3>
                    <p class="font-black text-lg leading-tight"><?= htmlspecialchars($cliente['nombre_rep'] ?: 'No asignado') ?></p>
                    <p class="text-sm text-slate-400 mt-1">RUT: <?= htmlspecialchars($cliente['rut_rep_real'] ?: '---') ?></p>
                    <div class="mt-4 pt-4 border-t border-slate-700 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500 uppercase">Clave SII</span>
                        <div class="flex items-center gap-2">
                            <code class="text-sky-400 font-bold"><?= htmlspecialchars($cliente['clave_rep'] ?: 'S/C') ?></code>
                            <button onclick="copy('<?= $cliente['clave_rep'] ?>')" class="text-slate-500 hover:text-sky-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
            <h2 class="text-sm font-black text-slate-400 uppercase mb-4">Observaciones Adicionales</h2>
            <p class="text-slate-600 leading-relaxed"><?= $cliente['observaciones'] ? nl2br(htmlspecialchars($cliente['observaciones'])) : 'Sin observaciones registradas.' ?></p>
        </div>

    </div>
</main>

<script>
function copy(text) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        alert("Copiado al portapapeles");
    });
}

function confirmarEliminar(rut) {
    if (confirm("¿Estás seguro de eliminar este contribuyente? Esta acción es irreversible y borrará claves, socios y propiedades asociados.")) {
        window.location.href = "../app/borrar_cliente.php?rut=" + rut;
    }
}
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
