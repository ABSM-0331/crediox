<?php
$cobratarios = $cobratarios ?? [];
$clientesAsignadosPorCobratario = $clientesAsignadosPorCobratario ?? [];
$creditosSinAsignar = $creditosSinAsignar ?? [];
?>
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
                    <button type="button" class="btn-secondary" style="width: 100%;" data-open-clientes-modal="<?= (int)$cobratario['idcobratario'] ?>">
                        Ver Clientes Asignados
                    </button>
                    <a href="/panel/public/editar-cobratario?id=<?= (int)$cobratario['idcobratario'] ?>" style="flex: 1; text-decoration: none;">
                        <button type="button" class="btn-secondary" style="width: 100%;">Editar Cobratario</button>
                    </a>
                    <form action="/panel/public/eliminar-cobratario" method="post" data-swal-confirm-title="Eliminar cobratario" data-swal-confirm-message="¿Eliminar este cobratario?" data-swal-confirm-button="Sí, eliminar" style="margin: 0;">
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

    <?php foreach ($cobratarios as $cobratario): ?>
        <?php
        $idCobratario = (int)($cobratario['idcobratario'] ?? 0);
        $clientesCobratario = $clientesAsignadosPorCobratario[$idCobratario] ?? [];
        ?>
        <div id="modal-clientes-<?= $idCobratario ?>" data-clientes-modal style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; padding: 20px; overflow-y: auto;">
            <div style="max-width: 960px; margin: 30px auto; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: var(--shadow);">
                <div style="display:flex; justify-content:space-between; align-items:center; padding: 16px 18px; border-bottom:1px solid var(--border-color);">
                    <div>
                        <h3 style="margin:0; color: var(--text-primary);">Clientes asignados</h3>
                        <p style="margin:4px 0 0 0; color: var(--text-secondary);"><?= htmlspecialchars($cobratario['nombre']) ?></p>
                    </div>
                    <button type="button" class="btn-secondary" data-close-clientes-modal="<?= $idCobratario ?>">Cerrar</button>
                </div>

                <div style="padding: 16px 18px;">
                    <?php if (empty($clientesCobratario)): ?>
                        <p style="margin:0; color: var(--text-secondary);">No hay clientes activos asignados a este cobratario.</p>
                    <?php else: ?>
                        <div class="table-container" style="margin-top: 0; overflow-x:auto;">
                            <table class="data-table" style="min-width: 900px;">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Teléfono</th>
                                        <th>Créditos Activos</th>
                                        <th>Saldo Pendiente</th>
                                        <th>Reasignar</th>
                                        <th>Quitar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientesCobratario as $cliente): ?>
                                        <?php $idCliente = (int)($cliente['idcliente'] ?? 0); ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($cliente['cliente'] ?? '') ?></strong>
                                                <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($cliente['email'] ?? 'Sin correo') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($cliente['telefono'] ?? 'N/A') ?></td>
                                            <td><?= (int)($cliente['creditos_activos'] ?? 0) ?></td>
                                            <td>$<?= number_format((float)($cliente['saldo_pendiente'] ?? 0), 2, '.', ',') ?></td>
                                            <td>
                                                <form action="/panel/public/cobratarios/cliente/reasignar" method="post" data-swal-confirm-title="Reasignar cliente" data-swal-confirm-message="¿Reasignar este cliente?" data-swal-confirm-button="Sí, reasignar" style="display:flex; gap:8px; align-items:center;">
                                                    <input type="hidden" name="idcliente" value="<?= $idCliente ?>">
                                                    <input type="hidden" name="idcobratario_actual" value="<?= $idCobratario ?>">
                                                    <select name="idcobratario_nuevo" required style="min-width: 180px; padding: 8px; border-radius: 6px; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color);">
                                                        <option value="">Seleccionar...</option>
                                                        <?php foreach ($cobratarios as $destino): ?>
                                                            <?php $idDestino = (int)($destino['idcobratario'] ?? 0); ?>
                                                            <?php if ($idDestino !== $idCobratario): ?>
                                                                <option value="<?= $idDestino ?>"><?= htmlspecialchars($destino['nombre'] ?? '') ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn-primary" style="white-space: nowrap;">Reasignar</button>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="/panel/public/cobratarios/cliente/quitar" method="post" data-swal-confirm-title="Quitar cliente" data-swal-confirm-message="¿Quitar cliente de este cobratario?" data-swal-confirm-button="Sí, quitar" style="margin:0;">
                                                    <input type="hidden" name="idcliente" value="<?= $idCliente ?>">
                                                    <input type="hidden" name="idcobratario_actual" value="<?= $idCobratario ?>">
                                                    <button type="submit" class="btn-icon danger" title="Quitar cliente">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                        <h4 style="margin: 0 0 10px 0; color: var(--text-primary);">Créditos no asignados</h4>
                        <?php if (empty($creditosSinAsignar)): ?>
                            <p style="margin:0; color: var(--text-secondary);">No hay créditos activos sin cobratario.</p>
                        <?php else: ?>
                            <div class="table-container" style="margin-top: 0; overflow-x:auto;">
                                <table class="data-table" style="min-width: 900px;">
                                    <thead>
                                        <tr>
                                            <th>ID Crédito</th>
                                            <th>Cliente</th>
                                            <th>Tipo</th>
                                            <th>Monto</th>
                                            <th>Saldo Pendiente</th>
                                            <th>Asignar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($creditosSinAsignar as $creditoSinAsignar): ?>
                                            <tr>
                                                <td>#<?= (int)($creditoSinAsignar['idcredito'] ?? 0) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($creditoSinAsignar['cliente'] ?? '') ?></strong>
                                                    <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($creditoSinAsignar['telefono'] ?? 'N/A') ?></div>
                                                </td>
                                                <td style="text-transform: capitalize;"><?= htmlspecialchars($creditoSinAsignar['tipo'] ?? '') ?></td>
                                                <td>$<?= number_format((float)($creditoSinAsignar['monto'] ?? 0), 2, '.', ',') ?></td>
                                                <td>$<?= number_format((float)($creditoSinAsignar['saldo_pendiente'] ?? 0), 2, '.', ',') ?></td>
                                                <td>
                                                    <form action="/panel/public/cobratarios/credito/asignar" method="post" data-swal-confirm-title="Asignar crédito" data-swal-confirm-message="¿Asignar este crédito a <?= htmlspecialchars($cobratario['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>?" data-swal-confirm-button="Sí, asignar" style="margin:0;">
                                                        <input type="hidden" name="idcredito" value="<?= (int)($creditoSinAsignar['idcredito'] ?? 0) ?>">
                                                        <input type="hidden" name="idcobratario_destino" value="<?= $idCobratario ?>">
                                                        <button type="submit" class="btn-primary">Asignar</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
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

        document.querySelectorAll('[data-open-clientes-modal]').forEach((btn) => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-open-clientes-modal');
                const modal = document.getElementById('modal-clientes-' + id);
                if (modal) {
                    modal.style.display = 'block';
                }
            });
        });

        document.querySelectorAll('[data-close-clientes-modal]').forEach((btn) => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-close-clientes-modal');
                const modal = document.getElementById('modal-clientes-' + id);
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('[data-clientes-modal]').forEach((modal) => {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('form[data-swal-confirm-message]').forEach((form) => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (typeof Swal === 'undefined') {
                    this.submit();
                    return;
                }

                const modalPadre = this.closest('[data-clientes-modal]');
                if (modalPadre) {
                    modalPadre.style.display = 'none';
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