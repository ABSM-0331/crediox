<?php
$creditosService = new CreditosService();
$rows = $creditosService->obtenerSaldosClientes(null);
$totalSaldo = array_sum(array_map(fn($r) => (float)($r['saldo_pendiente'] ?? 0), $rows));
?>
<style>
    :root {
        --bg-primary: #0b1120;
        --bg-secondary: #0f1729;
        --bg-tertiary: #152038;
        --bg-card: #121a2e;
        --border-color: #1e2d4a;
        --border-hover: #2e4a6e;
        --text-primary: #e8edf5;
        --text-secondary: #8a9bba;
        --text-muted: #4a5e80;
        --accent-blue: #38bdf8;
        --accent-blue-hover: #0ea5e9;
        --accent-green: #34d399;
        --accent-purple: #818cf8;
        --accent-orange: #fb923c;
        --accent-red: #f87171;
    }

    .saldos-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .saldos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 20px;
    }

    .saldos-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .saldos-stats {
        display: flex;
        gap: 15px;
    }

    .stat-box {
        background: var(--accent-blue);
        color: var(--text-primary);
        padding: 15px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: 1px solid var(--border-hover);
    }

    .stat-box .label {
        font-size: 12px;
        opacity: 1;
        color: #c7d2e8;
    }

    .stat-box .value {
        font-size: 18px;
        margin-top: 5px;
        color: #ffffff;
        font-weight: 700;
    }

    .saldos-controls {
        margin-bottom: 25px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .saldos-controls input {
        flex: 1;
        max-width: 400px;
        padding: 10px 15px;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        border-radius: 6px;
        font-size: 14px;
        color: var(--text-primary);
        transition: all 0.3s;
    }

    .saldos-controls input::placeholder {
        color: var(--text-muted);
    }

    .saldos-controls input:focus {
        outline: none;
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.1);
        background: var(--bg-secondary);
    }

    .btn-download-all {
        background: var(--accent-blue);
        color: var(--text-primary);
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-download-all:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(56, 189, 248, 0.2);
    }

    .saldos-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--bg-card);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        border: 1px solid var(--border-color);
    }

    .saldos-table thead {
        background: var(--bg-tertiary);
        border-bottom: 1px solid var(--border-color);
    }

    .saldos-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .saldos-table th:last-child {
        text-align: center;
    }

    .saldos-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.2s;
    }

    .saldos-table tbody tr:hover {
        background-color: var(--bg-tertiary);
    }

    .saldos-table td {
        padding: 15px;
        color: #1f2937;
        font-size: 14px;
    }

    .saldos-table .id-cell {
        font-weight: 600;
        color: #38bdf8;
        font-size: 13px;
    }

    .saldos-table .saldo-cell {
        font-weight: 700;
        color: #34d399;
        font-size: 15px;
        text-align: right;
    }

    .acciones-cell {
        display: flex;
        gap: 8px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 7px 12px;
        border-radius: 5px;
        border: 1px solid transparent;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-download {
        background: var(--accent-green);
        color: var(--bg-primary);
        border-color: var(--accent-green);
    }

    .btn-download:hover {
        background: transparent;
        color: var(--accent-green);
    }

    .btn-ver-creditos {
        background: var(--accent-blue);
        color: var(--bg-primary);
        border-color: var(--accent-blue);
    }

    .btn-ver-creditos:hover {
        background: transparent;
        color: var(--accent-blue);

    }

    .detalles-container {
        margin-top: 40px;
        background: var(--bg-card);
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
        display: none;
        border: 1px solid var(--border-color);
    }

    .detalles-container.visible {
        display: block;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .detalles-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .detalles-header h3 {
        margin: 0;
        color: #ffffff;
        font-size: 20px;
    }

    .btn-cerrar {
        background: var(--accent-red);
        color: var(--text-primary);
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-cerrar:hover {
        background: transparent;
        border: 1px solid var(--accent-red);
        color: var(--accent-red);
    }

    .creditos-table {
        width: 100%;
        border-collapse: collapse;
    }

    .creditos-table thead {
        background: var(--bg-tertiary);
    }

    .creditos-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        border-bottom: 1px solid var(--border-color);
    }

    .creditos-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
        color: #1f2937;
    }

    .creditos-table tbody tr:hover {
        background-color: var(--bg-tertiary);
    }

    .estado-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .estado-activo {
        background: rgba(52, 211, 153, 0.2);
        color: var(--accent-green);
        border: 1px solid var(--accent-green);
    }

    .estado-completado {
        background: rgba(56, 189, 248, 0.2);
        color: var(--accent-blue);
        border: 1px solid var(--accent-blue);
    }

    .estado-vencido {
        background: rgba(248, 113, 113, 0.2);
        color: var(--accent-red);
        border: 1px solid var(--accent-red);
    }

    .sin-resultados {
        text-align: center;
        padding: 40px;
        color: #8a9bba;
        font-size: 16px;
    }

    .sin-creditos {
        text-align: center;
        padding: 30px;
        color: #8a9bba;
    }
</style>

<div class="saldos-container">
    <div class="saldos-header">
        <div>
            <h2>📊 Saldos Pendientes</h2>
        </div>
        <div class="saldos-stats">
            <div class="stat-box">
                <div class="label">Clientes con saldo</div>
                <div class="value"><?php echo count($rows); ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Saldo total pendiente</div>
                <div class="value">$<?php echo number_format($totalSaldo, 2); ?></div>
            </div>
        </div>
    </div>

    <div class="saldos-controls">
        <input id="filtro" placeholder="🔍 Buscar cliente por nombre o ID..." />
        <a class="btn-download-all" href="/panel/public/reportes/saldos/export">📥 Descargar PDF (Todos)</a>
    </div>

    <?php if (empty($rows)): ?>
        <div class="sin-resultados">
            No hay clientes con saldos pendientes.
        </div>
    <?php else: ?>
        <table class="saldos-table" id="tablaSaldos">
            <thead>
                <tr>
                    <th style="width:70px;">ID</th>
                    <th>Cliente</th>
                    <th style="width:150px; text-align:right;">Saldo pendiente</th>
                    <th style="width:280px; text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr data-id="<?php echo (int)$r['idcliente']; ?>">
                        <td class="id-cell">#<?php echo (int)$r['idcliente']; ?></td>
                        <td><?php echo htmlspecialchars($r['cliente'] ?? ''); ?></td>
                        <td class="saldo-cell">$<?php echo number_format((float)($r['saldo_pendiente'] ?? 0), 2); ?></td>
                        <td class="acciones-cell">
                            <a class="btn-action btn-download" href="/panel/public/reportes/saldos/export?idcliente=<?php echo (int)$r['idcliente']; ?>">📄 Descargar PDF</a>
                            <button class="btn-action btn-ver-creditos" data-id="<?php echo (int)$r['idcliente']; ?>">👁️ Ver créditos</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div id="detallesContainer" class="detalles-container"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtro = document.getElementById('filtro');
        const tabla = document.getElementById('tablaSaldos');
        const detallesContainer = document.getElementById('detallesContainer');

        // Función de búsqueda
        filtro.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            if (!tabla) return;

            for (const row of tabla.tBodies[0].rows) {
                const id = row.cells[0].textContent.trim();
                const nombre = row.cells[1].textContent.trim().toLowerCase();
                const visible = q === '' || id.includes(q) || nombre.includes(q);
                row.style.display = visible ? '' : 'none';
            }
        });

        // Función para cerrar detalles
        function cerrarDetalles() {
            detallesContainer.classList.remove('visible');
            setTimeout(() => {
                detallesContainer.innerHTML = '';
            }, 300);
        }

        // Evento para botones Ver créditos
        document.querySelectorAll('.btn-ver-creditos').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombreCliente = this.closest('tr').cells[1].textContent.trim();

                detallesContainer.innerHTML = '<div style="text-align:center; padding:20px;"><span style="font-size:18px;">⏳ Cargando créditos...</span></div>';
                detallesContainer.classList.add('visible');

                fetch('/panel/public/reportes/saldos/cliente?id=' + encodeURIComponent(id))
                    .then(r => {
                        if (!r.ok) throw new Error('Error en la solicitud');
                        return r.json();
                    })
                    .then(data => {
                        if (!Array.isArray(data)) {
                            detallesContainer.innerHTML = '<div style="color:red; padding:20px;">Error: No se pudieron obtener los créditos</div>';
                            return;
                        }

                        let html = '<div class="detalles-header">';
                        html += '<h3>💳 Créditos de ' + htmlEscape(nombreCliente) + ' (ID: ' + id + ')</h3>';
                        html += '<button class="btn-cerrar" onclick="document.getElementById(\'detallesContainer\').classList.remove(\'visible\'); setTimeout(() => { document.getElementById(\'detallesContainer\').innerHTML = \'\'; }, 300);">✕ Cerrar</button>';
                        html += '</div>';

                        if (data.length === 0) {
                            html += '<div class="sin-creditos">Este cliente no tiene créditos registrados.</div>';
                        } else {
                            html += '<table class="creditos-table"><thead><tr><th>ID</th><th>Monto</th><th>Saldo</th><th>Estado</th><th>Fecha Inicio</th></tr></thead><tbody>';
                            for (const c of data) {
                                const estado = (c.estado || '').toLowerCase();
                                let claseEstado = 'estado-activo';
                                if (estado === 'completado') claseEstado = 'estado-completado';
                                else if (estado === 'vencido') claseEstado = 'estado-vencido';

                                html += '<tr>';
                                html += '<td><strong>#' + (c.idcredito || '') + '</strong></td>';
                                html += '<td>$' + (Number(c.monto || 0).toFixed(2)) + '</td>';
                                html += '<td><strong>$' + (Number(c.saldo_pendiente || 0).toFixed(2)) + '</strong></td>';
                                html += '<td><span class="estado-badge ' + claseEstado + '">' + (c.estado || 'Desconocido') + '</span></td>';
                                html += '<td>' + (c.fecha_inicio || 'N/A') + '</td>';
                                html += '</tr>';
                            }
                            html += '</tbody></table>';
                        }
                        detallesContainer.innerHTML = html;
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        detallesContainer.innerHTML = '<div style="color:red; padding:20px;">❌ Error al obtener los créditos: ' + err.message + '</div>';
                    });
            });
        });

        // Utilidad para escapar HTML
        function htmlEscape(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    });
</script>