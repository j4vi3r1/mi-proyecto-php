<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../app/conexion.php'; 
include_once __DIR__ . '/../partials/header.php'; 

try {
    // Consulta mejorada para traer los datos necesarios
    $query = "SELECT r.id_representante, r.nombre, r.rut_representante, 
                     COALESCE(cont.telefono, 'No registrado') as telefono,
                     STRING_AGG(c.razon_social, '||') as empresas_asociadas
              FROM Representantes r
              LEFT JOIN Contribuyentes c ON r.id_representante = c.id_representante
              LEFT JOIN Contacto cont ON c.id_contacto = cont.id_contacto
              GROUP BY r.id_representante, r.nombre, r.rut_representante, cont.telefono
              ORDER BY r.nombre ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $representantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<main class="min-h-screen bg-slate-50 py-12 px-6">
    <div class="max-w-5xl mx-auto">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">Directorio de <span class="text-sky-600">Representantes</span></h1>
                <p class="text-slate-500 font-medium mt-1">Busca y gestiona los apoderados legales del sistema.</p>
            </div>
            
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" id="buscador" placeholder="Buscar por nombre o RUT..." 
                       class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-2xl shadow-sm focus:ring-2 focus:ring-sky-500 outline-none transition-all font-medium text-slate-600">
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-2xl mb-6 text-sm font-bold">
                ⚠️ Error: <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div id="lista-representantes" class="grid grid-cols-1 gap-4">
            <?php foreach ($representantes as $rep): ?>
                <div class="rep-card bg-white rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-md transition-all overflow-hidden" 
                     data-string="<?= strtolower(htmlspecialchars($rep['nombre'] . ' ' . $rep['rut_representante'])) ?>">
                    
                    <div class="p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center text-xl font-black group-hover:bg-sky-100 group-hover:text-sky-600 transition-colors">
                                <?= strtoupper(substr($rep['nombre'], 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800"><?= htmlspecialchars($rep['nombre']) ?></h3>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500 font-medium mt-1">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                        <?= htmlspecialchars($rep['rut_representante']) ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        <?= htmlspecialchars($rep['telefono']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button onclick="toggleDetails('details-<?= $rep['id_representante'] ?>')" 
                                class="px-6 py-3 bg-slate-50 text-slate-600 font-bold rounded-xl hover:bg-sky-600 hover:text-white transition-all flex items-center gap-2 group">
                            Contribuyentes Asociados
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>

                    <div id="details-<?= $rep['id_representante'] ?>" class="hidden bg-slate-50/50 border-t border-slate-100 p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php 
                            if ($rep['empresas_asociadas']):
                                $empresas = explode('||', $rep['empresas_asociadas']);
                                foreach ($empresas as $empresa): ?>
                                    <div class="flex items-center gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                                        <div class="w-2 h-2 bg-sky-500 rounded-full"></div>
                                        <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($empresa) ?></span>
                                    </div>
                                <?php endforeach; 
                            else: ?>
                                <p class="text-sm text-slate-400 italic font-medium">No existen contribuyentes vinculados a este representante.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="no-results" class="hidden text-center py-20">
            <p class="text-slate-400 font-bold text-lg">No se encontraron representantes con esos datos.</p>
        </div>

    </div>
</main>

<script>
// Función para mostrar/ocultar detalles
function toggleDetails(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}

// Lógica del Buscador en tiempo real
document.getElementById('buscador').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.rep-card');
    const noResults = document.getElementById('no-results');
    let hasResults = false;

    cards.forEach(card => {
        const content = card.getAttribute('data-string');
        if (content.includes(term)) {
            card.style.display = "block";
            hasResults = true;
        } else {
            card.style.display = "none";
        }
    });

    noResults.classList.toggle('hidden', hasResults);
});
</script>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
