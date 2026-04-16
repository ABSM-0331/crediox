<?php $cobratarios = $cobratarios ?? []; ?>
<section id="cobratarios" class="content-section">
    <div class="section-header">
        <h2>Catálogo de Cobratarios</h2>
        <a href="nuevo-cobratario">
            <button class="btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Nuevo Cobratario
            </button>
        </a>
    </div>

    <div class="search-bar">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="buscadorCobratarios" placeholder="Buscar cobratario...">
    </div>

    <div id="sinResultadosCobratarios" style="display: none; text-align: center; padding: 18px; margin-bottom: 10px; color: var(--text-muted); background: var(--bg-tertiary); border-radius: 8px;">
        No se encontraron cobratarios con ese criterio de búsqueda.
    </div>

    <div class="cards-grid" id="cardsCobratariosGrid">
        <?php foreach ($cobratarios as $cobratario): ?>
            <div class="person-card" data-search-card>
                <div class="person-avatar">
                    <span><?= htmlspecialchars(strtoupper(substr($cobratario['nombre'], 0, 2))) ?></span>
                </div>
                <div class="person-info">
                    <h3><?= htmlspecialchars($cobratario['nombre']) ?></h3>
                    <p class="person-email"><?= htmlspecialchars($cobratario['email'] ?? 'Sin correo') ?></p>
                    <p class="person-phone"><?= htmlspecialchars($cobratario['telefono'] ?? 'Sin telefono') ?></p>
                </div>
                <div class="person-stats">
                    <div class="stat">
                        <span class="stat-number"><?= (int)($cobratario['clientes_asignados'] ?? 0) ?></span>
                        <span class="stat-text">Clientes</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">$<?= number_format((float)($cobratario['total_cobrado'] ?? 0), 2, '.', ',') ?></span>
                        <span class="stat-text">Cobrado hoy</span>
                    </div>
                </div>
                <div class="person-actions">
                    <a href="/panel/public/editar-cobratario?id=<?= (int)$cobratario['idcobratario'] ?>" style="flex: 1; text-decoration: none;">
                        <button type="button" class="btn-secondary" style="width: 100%;">Editar Cobratario</button>
                    </a>
                    <form action="/panel/public/eliminar-cobratario" method="post" onsubmit="return confirm('¿Eliminar este cobratario?');" style="margin: 0;">
                        <input type="hidden" name="idcobratario" value="<?= (int)$cobratario['idcobratario'] ?>">
                        <button type="submit" class="btn-icon danger" title="Eliminar cobratario">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorCobratarios');
        const cards = document.querySelectorAll('[data-search-card]');
        const sinResultados = document.getElementById('sinResultadosCobratarios');

        if (!buscador || cards.length === 0) {
            return;
        }

        buscador.addEventListener('input', function() {
            const termino = this.value.toLowerCase().trim();
            let visibles = 0;

            cards.forEach((card) => {
                const textoCard = card.textContent.toLowerCase();
                const coincide = termino === '' || textoCard.includes(termino);
                card.style.display = coincide ? '' : 'none';

                if (coincide) {
                    visibles += 1;
                }
            });

            if (sinResultados) {
                sinResultados.style.display = visibles === 0 ? 'block' : 'none';
            }
        });
    });
</script>