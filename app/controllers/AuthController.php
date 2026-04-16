<?php
class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function mostrarLogin(): void
    {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        if (isset($_SESSION['usuario_id'])) {
            header('location: /panel/public/dashboard');
            exit;
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void
    {
        $correo = $_POST['correo'] ?? '';
        $password = $_POST['password'] ?? '';
        if ($this->auth->login($correo, $password)) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Inicio de sesión correcto',
            ];
            $rol = $_SESSION['usuario_rol'] ?? null;
            if ($rol === 2) {
                header('location: /panel/public/dashboard-cliente');
            } elseif ($rol === 3) {
                header('location: /panel/public/dashboard-cobratario');
            } else {
                header('location: /panel/public/dashboard');
            }
            exit;
        }
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Credenciales inválidas',
        ];
        header('location: /panel/public/login');
        exit;
    }


    public function logout(): void
    {
        $this->auth->logout();
        header('location: /panel/public/login');
        exit;
    }
}
