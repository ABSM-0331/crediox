<?php
$administradores = $administradores ?? [];
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<section id="administradores" class="content-section">
    <div class="section-header">
        <h2>Catálogo de Administradores</h2>
        <a href="nuevo-administrador">
            <button class="btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Nuevo Administrador
            </button>
        </a>
    </div>

    <?php if ($success): ?>
        <div class="form-card" style="margin-bottom: 16px; border-left: 4px solid var(--accent-green);">
            <p style="margin: 0; color: var(--accent-green); font-weight: 600;">
                <?= htmlspecialchars($success) ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="form-card" style="margin-bottom: 16px; border-left: 4px solid var(--accent-red);">
            <p style="margin: 0; color: var(--accent-red); font-weight: 600;">
                <?= htmlspecialchars($error) ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="search-bar">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="buscadorAdministradores" placeholder="Buscar administrador...">
    </div>

    <div id="sinResultadosAdministradores" style="display: none; text-align: center; padding: 18px; margin-bottom: 10px; color: var(--text-muted); background: var(--bg-tertiary); border-radius: 8px;">
        No se encontraron administradores con ese criterio de búsqueda.
    </div>

    <div class="cards-grid" id="cardsAdministradoresGrid">
        <?php foreach ($administradores as $administrador): ?>
            <div class="person-card" data-search-card>
                <div class="person-avatar">
                    <span><?= htmlspecialchars(strtoupper(substr($administrador['nombre'], 0, 2))) ?></span>
                </div>
                <div class="person-info">
                    <h3><?= htmlspecialchars($administrador['nombre']) ?></h3>
                    <p class="person-email"><?= htmlspecialchars($administrador['email'] ?? 'Sin correo') ?></p>
                    <p class="person-phone"><?= htmlspecialchars($administrador['telefono'] ?? 'Sin teléfono') ?></p>
                </div>
                <div class="person-stats">
                    <div class="stat">
                        <span class="stat-number"><?= htmlspecialchars($administrador['municipio'] ?? 'N/A') ?></span>
                        <span class="stat-text">Municipio</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?= htmlspecialchars($administrador['estado'] ?? 'N/A') ?></span>
                        <span class="stat-text">Estado</span>
                    </div>
                </div>
                <div class="person-actions">
                    <a href="/panel/public/editar-administrador?id=<?= (int)$administrador['idadministrador'] ?>" style="flex: 1; text-decoration: none;">
                        <button type="button" class="btn-secondary" style="width: 100%; justify-content: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Editar
                        </button>
                    </a>
                    <form action="/panel/public/eliminar-administrador" method="post" data-swal-confirm-title="Eliminar administrador" data-swal-confirm-message="¿Eliminar este administrador?" data-swal-confirm-button="Sí, eliminar" style="margin: 0; width: 100%;">
                        <input type="hidden" name="idadministrador" value="<?= (int)$administrador['idadministrador'] ?>">
                        <button type="submit" class="btn-icon danger" title="Eliminar administrador" style="width: 100%; justify-content: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorAdministradores');
        const cards = document.querySelectorAll('#cardsAdministradoresGrid [data-search-card]');
        const sinResultados = document.getElementById('sinResultadosAdministradores');

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

        document.querySelectorAll('form[data-swal-confirm-message]').forEach((form) => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (typeof Swal === 'undefined') {
                    this.submit();
                    return;
                }

                const title = this.dataset.swalConfirmTitle || 'Confirmar acción';
                const message = this.dataset.swalConfirmMessage || '¿Deseas continuar?';
                const confirmButtonText = this.dataset.swalConfirmButton || 'Sí, continuar';

                Swal.fire({
                    icon: 'warning',
                    title,
                    text: message,
                    showCancelButton: true,
                    confirmButtonText,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ef4444'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    });
</script>