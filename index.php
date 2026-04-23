<?php
$titulo_pagina = "Votaciones - GestFincas";

// Elegimos el topbar adecuado según el rol
$rol = $_SESSION['vivienda']['rol'] ?? 'vecino';
if ($rol === 'presidente') {
    include "src/views/components/topbar.php";
} else {
    include "src/views/components/topbarv.php";
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .toggle-icon { transition: transform 0.3s ease; }
    .collapsed .toggle-icon { transform: rotate(-90deg); }
    /* Aseguramos que el contenido principal no se solape si hay comportamientos extraños */
    main { min-height: 100vh; }
</style>
<script>
    /**
     * MANIPULACIÓN AVANZADA DEL DOM: MutationObserver
     * Esta API nos permite reaccionar a cambios en el HTML en tiempo real.
     * En este caso, detectamos cuando el usuario pulsa el botón de Modo Oscuro/Claro
     * para actualizar los colores de las gráficas de Chart.js sin tener que recargar la página.
     */
    window.votacionCharts = window.votacionCharts || {};

    const themeObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'data-theme') {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                // Colores dinámicos basados en el estado del tema
                const mainColor = isDark ? '#CDD6F4' : '#221C35';
                const pendingColor = isDark ? '#585B70' : '#dee2e6';
                const gridColor = isDark ? 'rgba(205, 214, 244, 0.1)' : 'rgba(34, 28, 53, 0.05)';

                Object.values(window.votacionCharts).forEach(chart => {
                    // Actualizamos colores de las barras basándonos en si la etiqueta es 'Pendientes'
                    chart.data.datasets[0].backgroundColor = chart.data.labels.map(label => 
                        label === 'Pendientes' ? pendingColor : mainColor
                    );
                    // Actualizamos colores de los ejes y rejillas
                    chart.options.scales.y.ticks.color = isDark ? '#CDD6F4' : '#221C35';
                    chart.options.scales.y.grid.color = gridColor;
                    chart.options.scales.x.ticks.color = isDark ? '#CDD6F4' : '#221C35';
                    chart.update();
                });
            }
        });
    });
    themeObserver.observe(document.documentElement, { attributes: true });
</script>

    <div class="container-fluid p-0">
        <div class="row flex-nowrap m-0">
            <?php include "src/views/components/sidebar.php"; ?>
            <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
                
                <div class="container-fluid p-0">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0" style="font-family: var(--fuente-titulos);">Votaciones</h2>
                        
                        <?php if ($rol === 'presidente'): ?>
                            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#nuevaVotacionModal">
                                <i class="fa-solid fa-plus me-2"></i> Nueva Votación
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row" id="accordionVotaciones">
                        <?php if (empty($votaciones)): ?>
                            <div class="col-12"><p class="text-muted">No hay votaciones activas en este momento.</p></div>
                        <?php endif; ?>

                        <?php foreach ($votaciones as $v): ?>
                            <?php 
                                $fecha_limite = !empty($v['fecha_limite']) ? strtotime($v['fecha_limite']) : null;
                                $esta_finalizada = $fecha_limite && $fecha_limite < time();
                                $collapse_id = "collapse-v-" . $v['id_votacion'];
                            ?>
                            <div class="col-12 mb-3 accordion-item border-0 bg-transparent">
                                <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                                    <!-- Cabecera mínima que sirve de disparador -->
                                    <div class="card-header bg-white border-0 p-3 d-flex justify-content-between align-items-center collapsed" 
                                         style="cursor: pointer;"
                                         role="button"
                                         data-bs-toggle="collapse" 
                                         data-bs-target="#<?= $collapse_id ?>"
                                         aria-expanded="false" 
                                         aria-controls="<?= $collapse_id ?>">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="fa-solid fa-chevron-down text-muted small toggle-icon"></i>
                                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($v['titulo']) ?></h6>
                                            <?php if ($esta_finalizada): ?>
                                                <span class="badge bg-danger">Finalizada</span>
                                            <?php elseif ($v['ha_votado']): ?>
                                                <span class="badge bg-success">Participado</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Abierta</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <small class="text-muted d-none d-md-block">
                                                <?= $fecha_limite ? 'Límite: ' . date('d/m/Y H:i', $fecha_limite) : 'Sin límite' ?>
                                            </small>
                                            
                                            <?php if ($rol === 'presidente'): ?>
                                                <button type="button" class="btn btn-link text-danger p-0 border-0 shadow-none" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id-votacion="<?= $v['id_votacion'] ?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Contenido colapsable -->
                                    <div id="<?= $collapse_id ?>" class="accordion-collapse collapse" data-bs-parent="#accordionVotaciones">
                                        <div class="card-body border-top">
                                        <p class="card-text text-muted"><?= htmlspecialchars($v['descripcion']) ?></p>
                                        
                                        <hr>

                                        <?php if (!$v['ha_votado'] && !$esta_finalizada): ?>
                                            <div class="d-grid gap-2">
                                                <?php if (isset($v['opciones'])): foreach ($v['opciones'] as $opc): ?>
                                                    <button type="button" class="btn btn-outline-primary text-start" data-bs-toggle="modal" data-bs-target="#confirmVoteModal" data-id-votacion="<?= $v['id_votacion'] ?>" data-id-opcion="<?= $opc['id_opcion'] ?>" data-texto-opcion="<?= htmlspecialchars($opc['texto']) ?>">
                                                        <i class="fa-regular fa-circle me-2"></i> <?= htmlspecialchars($opc['texto']) ?>
                                                    </button>
                                                <?php endforeach; endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center">
                                                <?php if ($esta_finalizada): ?>
                                                    <div class="alert alert-warning py-2 small mb-3">Votación cerrada por fecha límite.</div>
                                                <?php else: ?>
                                                    <span class="badge bg-info text-dark mb-3">Ya has participado</span>
                                                <?php endif; ?>
                                                <div style="max-height: 300px; position: relative;">
                                                    <canvas id="chart-<?= $v['id_votacion'] ?>"></canvas>
                                                </div>
                                                
                                                <!-- Resumen de participación -->
                                                <div class="mt-3 p-2 bg-light rounded-3 small border text-start">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Participación:</span>
                                                        <span class="fw-bold"><?= $v['resultados']['total'] ?> de <?= $v['resultados']['censo'] ?> vecinos</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-muted">
                                                        <span>Pendientes de voto:</span>
                                                        <span><?= $v['resultados']['pendientes'] ?> vecinos</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    const ctx = document.getElementById('chart-<?= $v['id_votacion'] ?>').getContext('2d');
                                                    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

                                                    // Preparamos datos incluyendo pendientes para visualizar la abstención temporal
                                                    const labels = <?= json_encode(array_column($v['resultados']['detalle'] ?? [], 'texto')) ?>;
                                                    const data = <?= json_encode(array_column($v['resultados']['detalle'] ?? [], 'total')) ?>;
                                                    
                                                    // Adaptamos color de barras: claro en modo oscuro, oscuro en modo claro
                                                    const colors = data.map(() => isDark ? '#CDD6F4' : '#221C35');

                                                    // Añadimos la barra de pendientes si existe gente que falta por votar
                                                    const pendientes = <?= $v['resultados']['pendientes'] ?>;
                                                    if (pendientes > 0) {
                                                        labels.push('Pendientes');
                                                        data.push(pendientes);
                                                        // Un gris contrastado según el modo
                                                        colors.push(isDark ? '#585B70' : '#dee2e6');
                                                    }

                                                    window.votacionCharts['<?= $v['id_votacion'] ?>'] = new Chart(ctx, {
                                                        type: 'bar',
                                                        data: {
                                                            labels: labels,
                                                            datasets: [{
                                                                label: 'Votos',
                                                                data: data,
                                                                backgroundColor: colors,
                                                                borderRadius: 5
                                                            }]
                                                        },
                                                        options: { 
                                                            plugins: { legend: { display: false } },
                                                            scales: {
                                                                y: { 
                                                                    beginAtZero: true,
                                                                    ticks: { 
                                                                        stepSize: 1, 
                                                                        precision: 0,
                                                                        color: isDark ? '#CDD6F4' : '#221C35'
                                                                    },
                                                                    grid: {
                                                                        color: isDark ? 'rgba(205, 214, 244, 0.1)' : 'rgba(34, 28, 53, 0.05)'
                                                                    }
                                                                },
                                                                x: {
                                                                    ticks: { color: isDark ? '#CDD6F4' : '#221C35' },
                                                                    grid: { display: false }
                                                                }
                                                            }
                                                        }
                                                    });
                                                });
                                            </script>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Nueva Votación (Solo Presidentes) -->
    <?php if ($rol === 'presidente'): ?>
    <div class="modal fade" id="nuevaVotacionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Crear Nueva Votación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?route=votacion/crear" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Título de la votación</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ej: Pintar la fachada" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descripción / Detalles</label>
                            <textarea name="descripcion" class="form-control" rows="4" placeholder="Explica los detalles para que los vecinos puedan decidir..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha y Hora Límite (Opcional)</label>
                            <input type="datetime-local" name="fecha_limite" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold d-flex justify-content-between">
                                Opciones de respuesta
                                <button type="button" class="btn btn-sm btn-outline-primary border-0" id="btn-add-opcion">
                                    <i class="fa-solid fa-plus-circle"></i> Añadir
                                </button>
                            </label>
                            <div id="contenedor-opciones">
                                <input type="text" name="opciones[]" class="form-control mb-2" placeholder="Opción 1" required>
                                <input type="text" name="opciones[]" class="form-control mb-2" placeholder="Opción 2" required>
                            </div>
                            <small class="text-muted">Mínimo 2 opciones.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Publicar Votación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('btn-add-opcion').addEventListener('click', function() {
            const contenedor = document.getElementById('contenedor-opciones');
            const index = contenedor.querySelectorAll('input').length + 1;
            const input = document.createElement('div');
            input.className = 'input-group mb-2';
            input.innerHTML = `
                <input type="text" name="opciones[]" class="form-control" placeholder="Opción ${index}">
                <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
            `;
            contenedor.appendChild(input);
        });
    </script>
    <?php endif; ?>

    <!-- Modal Confirmar Voto -->
    <div class="modal fade" id="confirmVoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Confirmar Voto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?route=votacion/votar" method="POST">
                    <div class="modal-body py-1">
                        <input type="hidden" name="id_votacion" id="vote-id-votacion">
                        <input type="hidden" name="id_opcion" id="vote-id-opcion">
                        <p class="mb-0 fs-5">¿Confirmas tu voto por la opción: <strong id="voteOptionText" class="text-primary"></strong>?</p>
                        <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-circle-exclamation me-1"></i> Recuerda que el voto es definitivo y no se puede cambiar.</p>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Confirmar Voto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminación (Solo Presidentes) -->
    <?php if ($rol === 'presidente'): ?>
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Eliminar Votación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?route=votacion/eliminar" method="POST">
                    <div class="modal-body py-1">
                        <input type="hidden" name="id_votacion" id="delete-id-votacion">
                        <p class="mb-0 fs-5">¿Estás seguro de que deseas eliminar esta votación permanentemente?</p>
                        <p class="text-muted small mt-3 mb-0">Esta acción no se puede deshacer y se perderán todos los votos registrados.</p>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4">Sí, eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Lógica para inyectar datos en el modal de Votar
        const confirmVoteModal = document.getElementById('confirmVoteModal');
        if (confirmVoteModal) {
            confirmVoteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                confirmVoteModal.querySelector('#vote-id-votacion').value = button.getAttribute('data-id-votacion');
                confirmVoteModal.querySelector('#vote-id-opcion').value = button.getAttribute('data-id-opcion');
                confirmVoteModal.querySelector('#voteOptionText').textContent = button.getAttribute('data-texto-opcion');
            });
        }

        // Lógica para inyectar datos en el modal de Eliminar
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        if (confirmDeleteModal) {
            confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                confirmDeleteModal.querySelector('#delete-id-votacion').value = button.getAttribute('data-id-votacion');
            });
        }
    </script>