<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

$representantes = [];
$error_db = null;

try {
    if (isset($conn)) {
        $query = "SELECT 
                    r.id_representante, 
                    r.nombre, 
                    r.rut_representante, 
                    STRING_AGG(DISTINCT c.razon_social, '||') as empresas_asociadas,
                    COUNT(c.rut_contribuyente) as total_empresas
                  FROM Representantes r
                  LEFT JOIN Contribuyentes c ON r.id_representante = c.id_representante
                  GROUP BY r.id_representante, r.nombre, r.rut_representante
                  ORDER BY r.nombre ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $representantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error_db = "Error de conexión con el servidor.";
    }
} catch (PDOException $e) {
    $error_db = "Error en la base de datos: " . $e->getMessage();
}
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-5xl mx-auto">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight italic">
                    Directorio <span class="text-sky-600">Representantes</span>
                </h1>
                <p class="text-slate-500 font-medium mt-1">Gestión de apoderados legales y empresas vinculadas.</p>
            </div>
            
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" id="buscador" placeholder="Buscar por nombre o RUT..." 
                       class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-2xl shadow-sm focus:ring-2 focus:ring-sky-400 outline-none transition-all font-medium text-slate-600">
            </div>
        </div>

        <?php if ($error_db): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 mb-8 rounded-2xl text-sm">
                <?= htmlspecialchars($error_db) ?>
            </div>
        <?php endif; ?>

        <div id="lista-representantes" class="grid grid-cols-1 gap-4">
            <?php foreach ($representantes as $rep): ?>
                <div class="rep-card bg-white rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden" 
                     data-string="<?= strtolower(htmlspecialchars($rep['nombre'] . ' ' . $rep['rut_representante'])) ?>">
                    
                    <div class="p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        
                        <div class="flex items-center gap-6">
                            <div class="w-14 h-14 bg-gradient-to-br from-sky-300 to-indigo-400 text-white rounded-2xl flex items-center justify-center text-xl font-black shadow-inner">
                                <?= strtoupper(substr($rep['nombre'] ?? 'R', 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800 leading-tight"><?= htmlspecialchars($rep['nombre'] ?: 'Sin nombre') ?></h3>
                                <p class="text-xs font-bold text-slate-400 tracking-widest mt-1">
                                    RUT: <?= htmlspecialchars($rep['rut_representante']) ?>
                                </p>
                            </div>
                        </div>

                        <button onclick="toggleDetails('details-<?= $rep['id_representante'] ?>')" 
                                class="px-6 py-3 bg-slate-50 text-slate-600 font-bold rounded-xl hover:bg-sky-600 hover:text-white transition-all flex items-center gap-2 group active:scale-95 shadow-sm">
                            Contribuyentes Asociados
                            <span class="bg-slate-200 text-slate-600 text-[10px] px-2 py-0.5 rounded-md group-hover:bg-sky-500 group-hover:text-white transition-colors">
                                <?= $rep['total_empresas'] ?>
                            </span>
                            <svg class="w-4 h-4 transform group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    <div id="details-<?= $rep['id_representante'] ?>" class="hidden bg-slate-50 border-t border-slate-50 p-8 animate-in slide-in-from-top-2 duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <?php 
                            if (!empty($rep['empresas_asociadas'])):
                                $empresas = explode('||', $rep['empresas_asociadas']);
                                foreach ($empresas as $empresa): ?>
                                    <div class="flex items-center gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                        <div class="w-2 h-2 bg-[#5be2f8] rounded-full"></div>
                                        <span class="text-xs font-black text-slate-700 uppercase leading-tight"><?= htmlspecialchars($empresa) ?></span>
                                    </div>
                                <?php endforeach; 
                            else: ?>
                                <p class="text-sm text-slate-400 italic">No tiene contribuyentes asociados.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="no-results" class="hidden text-center py-20">
            <p class="text-slate-400 font-bold">No se encontraron representantes.</p>
        </div>

    </div>
</main>

<script>
function toggleDetails(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}

document.getElementById('buscador').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.rep-card');
    let found = false;

    cards.forEach(card => {
        const text = card.getAttribute('data-string');
        if (text.includes(term)) {
            card.style.display = "block";
            found = true;
        } else {
            card.style.display = "none";
        }
    });

    document.getElementById('no-results').classList.toggle('hidden', found);
});
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
