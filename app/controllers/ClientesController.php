<?php
class ClientesController
{
    private ClientesService $service;
    public function __construct()
    {
        $this->service = new ClientesService();
    }
    public function mostrarCatalogoClientes(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }
        $view = __DIR__ . '/../views/clientes/index.php';
        require __DIR__ . '/../views/layouts/app.php';
    }

    public function vistaSaldosClientes(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }

        $view = __DIR__ . '/../views/clientes/saldos.php';
        require __DIR__ . '/../views/layouts/app.php';
    }
    /**
     * Exportar reporte de saldos pendientes como PDF.
     * Parámetro opcional GET `idcliente` para filtrar por cliente.
     */
    public function exportarReporteSaldos(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }

        $idCliente = isset($_GET['idcliente']) ? (int)$_GET['idcliente'] : null;
        $creditosService = new CreditosService();
        $rows = $creditosService->obtenerSaldosClientes($idCliente);

        $html = '<meta charset="utf-8"><div style="font-family: Arial, sans-serif; font-size:12px;">';
        $html .= '<h2 style="text-align:center;">Reporte de Saldos Pendientes</h2>';
        $html .= '<p>Generado: ' . date('Y-m-d H:i:s') . '</p>';

        if ($idCliente !== null && count($rows) > 0) {
            $clienteNombre = htmlspecialchars($rows[0]['cliente'] ?? '');
            $saldoTotal = number_format((float)($rows[0]['saldo_pendiente'] ?? 0), 2);
            $html .= '<h3>Cliente: ' . $clienteNombre . '</h3>';
            $html .= '<p><strong>Saldo pendiente total:</strong> $' . $saldoTotal . '</p>';

            $creditos = $creditosService->obtenerCreditosCliente($idCliente);
            $html .= '<table width="100%" border="1" cellspacing="0" cellpadding="6">';
            $html .= '<thead><tr><th>ID Crédito</th><th>Monto</th><th>Saldo Pendiente</th><th>Estado</th><th>Inicio</th></tr></thead><tbody>';
            foreach ($creditos as $c) {
                $html .= '<tr>';
                $html .= '<td>' . ((int)($c['idcredito'] ?? 0)) . '</td>';
                $html .= '<td>$' . number_format((float)($c['monto'] ?? 0), 2) . '</td>';
                $html .= '<td>$' . number_format((float)($c['saldo_pendiente'] ?? 0), 2) . '</td>';
                $html .= '<td>' . htmlspecialchars($c['estado'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($c['fecha_inicio'] ?? '') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $html .= '<table width="100%" border="1" cellspacing="0" cellpadding="6">';
            $html .= '<thead><tr><th>ID Cliente</th><th>Cliente</th><th>Saldo Pendiente</th></tr></thead><tbody>';
            foreach ($rows as $r) {
                $html .= '<tr>';
                $html .= '<td>' . ((int)($r['idcliente'] ?? 0)) . '</td>';
                $html .= '<td>' . htmlspecialchars($r['cliente'] ?? '') . '</td>';
                $html .= '<td>$' . number_format((float)($r['saldo_pendiente'] ?? 0), 2) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        $html .= '</div>';

        // Generar PDF usando Dompdf (ya incluido en vendor)
        try {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'saldos_clientes_' . date('Ymd_His') . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);
            exit;
        } catch (Throwable $e) {
            $_SESSION['error'] = 'No se pudo generar el PDF: ' . $e->getMessage();
            header('location: /panel/public/reportes/saldos');
            exit;
        }
    }

    /**
     * Endpoint JSON para obtener créditos de un cliente (AJAX)
     * GET param `id` (idcliente)
     */
    public function obtenerCreditosClienteJson(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        $creditosService = new CreditosService();
        $creditos = $creditosService->obtenerCreditosCliente($id);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($creditos);
        exit;
    }

    public function clientes(): array
    {
        return $this->service->obtenerTodos();
    }

    public function vistaCrearCliente(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }
        $view = __DIR__ . '/../views/clientes/crearCliente.php';
        require __DIR__ . '/../views/layouts/app.php';
    }

    public function crearCliente()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }

        try {
            $data = [
                "ap_paterno" => $_POST["ap_paterno"],
                "ap_materno" => $_POST["ap_materno"] ?? null,
                "nombres" => $_POST["nombres"],
                "sexo" => $_POST["sexo"] ?? null,
                "fecha_nacimiento" => $_POST["fecha_nacimiento"] ?? null,
                "edad" => $_POST["edad"] ?? null,
                "curp" => $_POST["curp"] ?? null,
                "clave_elector" => $_POST["clave_elector"] ?? null,
                "email" => $_POST["email"] ?? null,
                "telefono" => $_POST["telefono"],
                "foto_ruta" => $this->subirFoto($_FILES['foto_ruta'] ?? null),

                "dom_calle" => $_POST["dom_calle"] ?? null,
                "dom_numero" => $_POST["dom_numero"] ?? null,
                "dom_colonia" => $_POST["dom_colonia"] ?? null,
                "dom_cruz1" => $_POST["dom_cruz1"] ?? null,
                "dom_cruz2" => $_POST["dom_cruz2"] ?? null,
                "dom_cp" => $_POST["dom_cp"] ?? null,
                "idestado" => $_POST["idestado"] ?? null,
                "idmunicipio" => $_POST["idmunicipio"] ?? null,
                "dom_referencia" => $_POST["dom_referencia"] ?? null,
            ];

            $this->service->crearCliente($data);
            $_SESSION['success'] = 'Cliente creado correctamente';
            header('location: /panel/public/clientes');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear cliente: ' . $e->getMessage();
            header('location: /panel/public/nuevo-cliente');
            exit;
        }
    }

    public function vistaEditarCliente(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Cliente no encontrado';
            header('location: /panel/public/clientes');
            exit;
        }

        $cliente = $this->service->obtenerPorId($id);
        if (!$cliente) {
            $_SESSION['error'] = 'Cliente no encontrado';
            header('location: /panel/public/clientes');
            exit;
        }

        $view = __DIR__ . '/../views/clientes/editarCliente.php';
        require __DIR__ . '/../views/layouts/app.php';
    }

    public function actualizarCliente(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/clientes');
            exit;
        }

        $id = (int)($_POST['idcliente'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Cliente no encontrado';
            header('location: /panel/public/clientes');
            exit;
        }

        $clienteActual = $this->service->obtenerPorId($id);
        if (!$clienteActual) {
            $_SESSION['error'] = 'Cliente no encontrado';
            header('location: /panel/public/clientes');
            exit;
        }

        $fotoRuta = $clienteActual['foto_ruta'] ?? null;
        if (!empty($_FILES['foto_ruta']['name'])) {
            $fotoNueva = $this->subirFoto($_FILES['foto_ruta'] ?? null, $fotoRuta);
            $fotoRuta = $fotoNueva;
        }

        try {
            $data = [
                "ap_paterno" => $_POST["ap_paterno"] ?? null,
                "ap_materno" => $_POST["ap_materno"] ?? null,
                "nombres" => $_POST["nombres"] ?? null,
                "sexo" => $_POST["sexo"] ?? null,
                "fecha_nacimiento" => $_POST["fecha_nacimiento"] ?? null,
                "edad" => $_POST["edad"] ?? null,
                "curp" => $_POST["curp"] ?? null,
                "clave_elector" => $_POST["clave_elector"] ?? null,
                "email" => $_POST["email"] ?? null,
                "telefono" => $_POST["telefono"] ?? null,
                "foto_ruta" => $fotoRuta,
                "dom_calle" => $_POST["dom_calle"] ?? null,
                "dom_numero" => $_POST["dom_numero"] ?? null,
                "dom_colonia" => $_POST["dom_colonia"] ?? null,
                "dom_cruz1" => $_POST["dom_cruz1"] ?? null,
                "dom_cruz2" => $_POST["dom_cruz2"] ?? null,
                "dom_cp" => $_POST["dom_cp"] ?? null,
                "idestado" => $_POST["idestado"] ?? null,
                "idmunicipio" => $_POST["idmunicipio"] ?? null,
                "dom_referencia" => $_POST["dom_referencia"] ?? null,
            ];

            $this->service->actualizarCliente($id, $data);
            $_SESSION['success'] = 'Cliente actualizado correctamente';
            header('location: /panel/public/clientes');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar cliente: ' . $e->getMessage();
            header('location: /panel/public/editar-cliente?id=' . $id);
            exit;
        }
    }

    public function eliminarCliente(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }
        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/clientes');
            exit;
        }

        $id = (int)($_POST['idcliente'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Cliente no encontrado';
            header('location: /panel/public/clientes');
            exit;
        }

        $cliente = $this->service->obtenerPorId($id);
        if (!$cliente) {
            $_SESSION['error'] = 'Cliente no encontrado';
            header('location: /panel/public/clientes');
            exit;
        }

        try {
            $this->service->eliminarCliente($id);
            $_SESSION['success'] = 'Cliente eliminado correctamente';
            header('location: /panel/public/clientes');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar cliente: ' . $e->getMessage();
            header('location: /panel/public/clientes');
            exit;
        }
    }

    private function subirFoto(?array $archivo, ?string $fotoActual = null): ?string
    {
        if (empty($archivo) || empty($archivo['name'])) {
            return $fotoActual;
        }

        $directorio = __DIR__ . '/../../public/uploads/clientes/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreArchivo = uniqid() . '_' . basename($archivo['name']);
        $rutaCompleta = $directorio . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            throw new Exception('Error al subir la foto');
        }

        if ($fotoActual) {
            $rutaAnterior = __DIR__ . '/../../public/' . ltrim($fotoActual, '/');
            if (is_file($rutaAnterior)) {
                @unlink($rutaAnterior);
            }
        }

        return 'uploads/clientes/' . $nombreArchivo;
    }
}
