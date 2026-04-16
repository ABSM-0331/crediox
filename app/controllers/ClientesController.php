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
