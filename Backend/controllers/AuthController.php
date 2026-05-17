<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Logger.php';

class AuthController {

    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function register(): void {
        $body = $this->parseBody();
        $v    = new Validator($body);

        $v->required('name',     'Nome')
          ->required('email',    'Email')
          ->required('password', 'Senha')
          ->email('email')
          ->minLength('name', 2, 'Nome')
          ->maxLength('name', 100, 'Nome')
          ->strongPassword()
          ->matches('password', 'password_confirm', 'As senhas');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
            exit;
        }

        $name  = $v->sanitize('name');
        $email = strtolower(trim($body['email']));

        if ($this->userModel->findByEmail($email)) {
            Response::error('Este email já está em uso.', 409);
            exit;
        }

        $id = $this->userModel->create([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12]),
        ]);

        $user  = $this->userModel->findById($id);
        $token = $this->generateToken($user);

        unset($user['password'], $user['reset_token'], $user['reset_expires']);

        Logger::info('New user registered', ['id' => $id, 'email' => $email]);
        Response::success(['token' => $token, 'user' => $user], 'Conta criada com sucesso.', 201);
        exit;
    }

    public function login(): void {
        $body = $this->parseBody();
        $v    = new Validator($body);

        $v->required('email',    'Email')
          ->required('password', 'Senha')
          ->email('email');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
            exit;
        }

        $email = strtolower(trim($body['email']));
        $user  = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($body['password'], $user['password'])) {
            Response::error('Email ou senha incorretos.', 401);
            exit;
        }

        if (!$user['is_active']) {
            Response::error('Conta desativada.', 403);
            exit;
        }

        $this->userModel->updateLastLogin($user['id']);
        $token = $this->generateToken($user);

        unset($user['password'], $user['reset_token'], $user['reset_expires']);

        Logger::info('User logged in', ['id' => $user['id']]);
        Response::success(['token' => $token, 'user' => $user], 'Login realizado com sucesso.');
        exit;
    }

    public function forgotPassword(): void {
        $body  = $this->parseBody();
        $email = strtolower(trim($body['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Email inválido.', 422);
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->userModel->setResetToken($user['id'], $token, $expires);
            Logger::info('Password reset requested', ['email' => $email, 'token' => $token]);
        }

        Response::success(null, 'Se o email existir, você receberá instruções em breve.');
        exit;
    }

    public function resetPassword(): void {
        $body = $this->parseBody();
        $v    = new Validator($body);

        $v->required('token',    'Token')
          ->required('password', 'Senha')
          ->strongPassword()
          ->matches('password', 'password_confirm', 'As senhas');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
            exit;
        }

        $user = $this->userModel->findByResetToken($body['token']);
        if (!$user) {
            Response::error('Token inválido ou expirado.', 400);
            exit;
        }

        $this->userModel->updatePassword($user['id'], password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12]));
        $this->userModel->clearResetToken($user['id']);

        Response::success(null, 'Senha redefinida com sucesso.');
        exit;
    }

    public function me(): void {
        require_once __DIR__ . '/../middleware/AuthMiddleware.php';
        $auth = AuthMiddleware::handle();
        $user = $this->userModel->findById($auth['sub']);
        if (!$user) {
            Response::notFound('Utilizador não encontrado.');
            exit;
        }
        Response::success($user);
        exit;
    }

    private function generateToken(array $user): string {
        return JWT::encode([
            'sub'   => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'iat'   => time(),
            'exp'   => time() + JWT_EXPIRE,
        ]);
    }

    private function parseBody(): array {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?: [];
    }
}