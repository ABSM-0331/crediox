<?php
class AdministradoresController
{
    private AdministradoresService $service;

    public function __construct()
    {
        $this->service = new AdministradoresService();
    }

    public function index(): void
    {
        $this->validarAdmin();

        $administradores = $this->service->obtenerTodos();
        $view = __DIR__ . '/../views/administradores/index.php';
        require __DIR__ . '/../views/layouts/app.php';
    }

    public function vistaCrearAdministrador(): void
    {
        $this->validarAdmin();

        $view = __DIR__ . '/../views/administradores/crearAdministrador.php';
        require __DIR__ . '/../views/layouts/app.php';
    }

    public function vistaEditarAdministrador(): void
    {
        $this->validarAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Administrador no encontrado';
            header('location: /panel/public/administradores');
            exit;
        }

        $administrador = $this->service->obtenerPorId($id);
        if (!$administrador) {
            $_SESSION['error'] = 'Administrador no encontrado';
            header('location: /panel/public/administradores');
            exit;
        }

        $view = __DIR__ . '/../views/administradores/editarAdministrador.php';
        require __DIR__ . '/../views/layouts/app.php';
    }

    public function crearAdministrador(): void
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/administradores');
            exit;
        }

        try {
            $data = [
                'ap_paterno' => $_POST['ap_paterno'] ?? null,
                'ap_materno' => $_POST['ap_materno'] ?? null,
                'nombres' => $_POST['nombres'] ?? null,
                'sexo' => $_POST['sexo'] ?? null,
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
                'edad' => $_POST['edad'] ?? null,
                'curp' => $_POST['curp'] ?? null,
                'clave_elector' => $_POST['clave_elector'] ?? null,
                'email' => $_POST['email'] ?? null,
                'telefono' => $_POST['telefono'] ?? null,
                'foto_ruta' => $this->subirFoto($_FILES['foto_ruta'] ?? null),
                'dom_calle' => $_POST['dom_calle'] ?? null,
                'dom_numero' => $_POST['dom_numero'] ?? null,
                'dom_colonia' => $_POST['dom_colonia'] ?? null,
                'dom_cruz1' => $_POST['dom_cruz1'] ?? null,
                'dom_cruz2' => $_POST['dom_cruz2'] ?? null,
                'dom_cp' => $_POST['dom_cp'] ?? null,
                'idestado' => $_POST['idestado'] ?? null,
                'idmunicipio' => $_POST['idmunicipio'] ?? null,
                'dom_referencia' => $_POST['dom_referencia'] ?? null,
            ];

            if (empty($data['ap_paterno']) || empty($data['nombres']) || empty($data['telefono'])) {
                $_SESSION['error'] = 'Apellido paterno, nombres y teléfono son requeridos';
                header('location: /panel/public/nuevo-administrador');
                exit;
            }

            $this->service->crearAdministrador($data);
            $_SESSION['success'] = 'Administrador creado correctamente';
            header('location: /panel/public/administradores');
            exit;
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Error al crear administrador: ' . $e->getMessage();
            header('location: /panel/public/nuevo-administrador');
            exit;
        }
    }

    public function actualizarAdministrador(): void
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/administradores');
            exit;
        }

        $id = (int)($_POST['idadministrador'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Administrador no encontrado';
            header('location: /panel/public/administradores');
            exit;
        }

        $administradorActual = $this->service->obtenerPorId($id);
        if (!$administradorActual) {
            $_SESSION['error'] = 'Administrador no encontrado';
            header('location: /panel/public/administradores');
            exit;
        }

        $fotoRuta = $administradorActual['foto_ruta'] ?? null;
        if (!empty($_FILES['foto_ruta']['name'])) {
            $fotoRuta = $this->subirFoto($_FILES['foto_ruta'] ?? null, $fotoRuta);
        }

        try {
            $data = [
                'ap_paterno' => $_POST['ap_paterno'] ?? null,
                'ap_materno' => $_POST['ap_materno'] ?? null,
                'nombres' => $_POST['nombres'] ?? null,
                'sexo' => $_POST['sexo'] ?? null,
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
                'edad' => $_POST['edad'] ?? null,
                'curp' => $_POST['curp'] ?? null,
                'clave_elector' => $_POST['clave_elector'] ?? null,
                'email' => $_POST['email'] ?? null,
                'telefono' => $_POST['telefono'] ?? null,
                'foto_ruta' => $fotoRuta,
                'dom_calle' => $_POST['dom_calle'] ?? null,
                'dom_numero' => $_POST['dom_numero'] ?? null,
                'dom_colonia' => $_POST['dom_colonia'] ?? null,
                'dom_cruz1' => $_POST['dom_cruz1'] ?? null,
                'dom_cruz2' => $_POST['dom_cruz2'] ?? null,
                'dom_cp' => $_POST['dom_cp'] ?? null,
                'idestado' => $_POST['idestado'] ?? null,
                'idmunicipio' => $_POST['idmunicipio'] ?? null,
                'dom_referencia' => $_POST['dom_referencia'] ?? null,
            ];

            if (empty($data['ap_paterno']) || empty($data['nombres']) || empty($data['telefono'])) {
                $_SESSION['error'] = 'Apellido paterno, nombres y teléfono son requeridos';
                header('location: /panel/public/editar-administrador?id=' . $id);
                exit;
            }

            $this->service->actualizarAdministrador($id, $data);
            $_SESSION['success'] = 'Administrador actualizado correctamente';
            header('location: /panel/public/administradores');
            exit;
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Error al actualizar administrador: ' . $e->getMessage();
            header('location: /panel/public/editar-administrador?id=' . $id);
            exit;
        }
    }

    public function eliminarAdministrador(): void
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /panel/public/administradores');
            exit;
        }

        $id = (int)($_POST['idadministrador'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Administrador no encontrado';
            header('location: /panel/public/administradores');
            exit;
        }

        $administrador = $this->service->obtenerPorId($id);
        if (!$administrador) {
            $_SESSION['error'] = 'Administrador no encontrado';
            header('location: /panel/public/administradores');
            exit;
        }

        try {
            $this->service->eliminarAdministrador($id);
            $_SESSION['success'] = 'Administrador eliminado correctamente';
            header('location: /panel/public/administradores');
            exit;
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Error al eliminar administrador: ' . $e->getMessage();
            header('location: /panel/public/administradores');
            exit;
        }
    }

    private function subirFoto(?array $archivo, ?string $fotoActual = null): ?string
    {
        if (empty($archivo) || empty($archivo['name'])) {
            return $fotoActual;
        }

        $directorio = __DIR__ . '/../../public/uploads/administradores/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreArchivo = uniqid() . '_' . basename($archivo['name']);
        $rutaCompleta = $directorio . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            throw new Exception('Error al subir la foto');
        }

        return 'uploads/administradores/' . $nombreArchivo;
    }

    private function validarAdmin(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/login');
            exit;
        }

        if (!isset($_SESSION['usuario_rol']) || (int)$_SESSION['usuario_rol'] !== 1) {
            header('location: /panel/public/dashboard');
            exit;
        }
    }
}
