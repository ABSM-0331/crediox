<?php
class CobratarioController
{
    private CobratariosService $service;

    public function __construct()
    {
        $this->service = new CobratariosService();
    }

    public function index(): void
    {
        $this->validarAdmin();

        $cobratarios = $this->service->obtenerTodosConEstadisticas();
        $clientesAsignadosRows = $this->service->obtenerClientesAsignadosActivosPorCobratario();
        $creditosSinAsignar = $this->service->obtenerCreditosActivosSinCobratario();
        $clientesAsignadosPorCobratario = [];
        foreach ($clientesAsignadosRows as $fila) {
            $idCob = (int)($fila['idcobratario'] ?? 0);
            if (!isset($clientesAsignadosPorCobratario[$idCob])) {
                $clientesAsignadosPorCobratario[$idCob] = [];
            }
            $clientesAsignadosPorCobratario[$idCob][] = $fila;
        }
        $view = __DIR__ . '/../views/cobratarios/index.php';
        require __DIR__ . '/../views/layouts/app.php';
    }

    public function reasignarCliente(): void
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/cobratarios');
            exit;
        }

        $idCliente = (int)($_POST['idcliente'] ?? 0);
        $idCobratarioActual = (int)($_POST['idcobratario_actual'] ?? 0);
        $idCobratarioNuevo = (int)($_POST['idcobratario_nuevo'] ?? 0);

        if ($idCliente <= 0 || $idCobratarioActual <= 0 || $idCobratarioNuevo <= 0) {
            $_SESSION['error'] = 'Datos inválidos para reasignar cliente';
            header('location: /panel/public/cobratarios');
            exit;
        }

        try {
            $movidos = $this->service->reasignarClienteActivo($idCliente, $idCobratarioActual, $idCobratarioNuevo);
            if ($movidos <= 0) {
                $_SESSION['error'] = 'No se encontraron créditos activos para reasignar';
            } else {
                $_SESSION['success'] = 'Cliente reasignado correctamente';
            }
            header('location: /panel/public/cobratarios');
            exit;
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Error al reasignar cliente: ' . $e->getMessage();
            header('location: /panel/public/cobratarios');
            exit;
        }
    }

    public function quitarCliente(): void
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/cobratarios');
            exit;
        }

        $idCliente = (int)($_POST['idcliente'] ?? 0);
        $idCobratarioActual = (int)($_POST['idcobratario_actual'] ?? 0);

        if ($idCliente <= 0 || $idCobratarioActual <= 0) {
            $_SESSION['error'] = 'Datos inválidos para quitar cliente';
            header('location: /panel/public/cobratarios');
            exit;
        }

        try {
            $movidos = $this->service->quitarClienteActivo($idCliente, $idCobratarioActual);
            if ($movidos <= 0) {
                $_SESSION['error'] = 'No se encontraron créditos activos para quitar';
            } else {
                $_SESSION['success'] = 'Cliente quitado correctamente del cobratario';
            }
            header('location: /panel/public/cobratarios');
            exit;
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Error al quitar cliente: ' . $e->getMessage();
            header('location: /panel/public/cobratarios');
            exit;
        }
    }

    public function asignarCredito(): void
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/cobratarios');
            exit;
        }

        $idCredito = (int)($_POST['idcredito'] ?? 0);
        $idCobratarioDestino = (int)($_POST['idcobratario_destino'] ?? 0);

        if ($idCredito <= 0 || $idCobratarioDestino <= 0) {
            $_SESSION['error'] = 'Datos inválidos para asignar crédito';
            header('location: /panel/public/cobratarios');
            exit;
        }

        try {
            $movidos = $this->service->asignarCreditoSinCobratario($idCredito, $idCobratarioDestino);
            if ($movidos <= 0) {
                $_SESSION['error'] = 'El crédito no estaba disponible para asignación';
            } else {
                $_SESSION['success'] = 'Crédito asignado correctamente';
            }
            header('location: /panel/public/cobratarios');
            exit;
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Error al asignar crédito: ' . $e->getMessage();
            header('location: /panel/public/cobratarios');
            exit;
        }
    }

    public function cobratarios(): array
    {
        return $this->service->obtenerTodos();
    }

    public function vistaCrearCobratario(): void
    {
        $this->validarAdmin();
        $view = __DIR__ . '/../views/cobratarios/crearCobratario.php';

        require __DIR__ . '/../views/layouts/app.php';
    }

    public function crearCobratario(): void
    {
        $this->validarAdmin();

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

            $this->service->crearCobratario($data);
            $_SESSION['success'] = 'Cobratario creado correctamente';
            header('location: /panel/public/cobratarios');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear cobratario: ' . $e->getMessage();
            header('location: /panel/public/nuevo-cobratario');
            exit;
        }
    }

    public function vistaEditarCobratario(): void
    {
        $this->validarAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Cobratario no encontrado';
            header('location: /panel/public/cobratarios');
            exit;
        }

        $cobratario = $this->service->obtenerPorId($id);
        if (!$cobratario) {
            $_SESSION['error'] = 'Cobratario no encontrado';
            header('location: /panel/public/cobratarios');
            exit;
        }

        $view = __DIR__ . '/../views/cobratarios/editarCobratario.php';
        require __DIR__ . '/../views/layouts/app.php';
    }

    public function actualizarCobratario(): void
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/cobratarios');
            exit;
        }

        $id = (int)($_POST['idcobratario'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Cobratario no encontrado';
            header('location: /panel/public/cobratarios');
            exit;
        }

        $cobratarioActual = $this->service->obtenerPorId($id);
        if (!$cobratarioActual) {
            $_SESSION['error'] = 'Cobratario no encontrado';
            header('location: /panel/public/cobratarios');
            exit;
        }

        $fotoRuta = $cobratarioActual['foto_ruta'] ?? null;
        if (!empty($_FILES['foto_ruta']['name'])) {
            $fotoRuta = $this->subirFoto($_FILES['foto_ruta'] ?? null, $fotoRuta);
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

            $this->service->actualizarCobratario($id, $data);
            $_SESSION['success'] = 'Cobratario actualizado correctamente';
            header('location: /panel/public/cobratarios');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar cobratario: ' . $e->getMessage();
            header('location: /panel/public/editar-cobratario?id=' . $id);
            exit;
        }
    }

    public function eliminarCobratario(): void
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/cobratarios');
            exit;
        }

        $id = (int)($_POST['idcobratario'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Cobratario no encontrado';
            header('location: /panel/public/cobratarios');
            exit;
        }

        $cobratario = $this->service->obtenerPorId($id);
        if (!$cobratario) {
            $_SESSION['error'] = 'Cobratario no encontrado';
            header('location: /panel/public/cobratarios');
            exit;
        }

        try {
            $this->service->eliminarCobratario($id);
            $_SESSION['success'] = 'Cobratario eliminado correctamente';
            header('location: /panel/public/cobratarios');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar cobratario: ' . $e->getMessage();
            header('location: /panel/public/cobratarios');
            exit;
        }
    }

    private function subirFoto(?array $archivo, ?string $fotoActual = null): ?string
    {
        if (empty($archivo) || empty($archivo['name'])) {
            return $fotoActual;
        }

        $directorio = __DIR__ . '/../../public/uploads/cobratarios/';
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

        return 'uploads/cobratarios/' . $nombreArchivo;
    }

    private function validarAdmin(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }

        if (isset($_SESSION['usuario_rol']) && (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }
    }
}
