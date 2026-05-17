<?php
/**
 * Authentication Controller
 * weather-system/backend/controllers/AuthController.php
 */

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

    // ── POST /api/auth/register ─────────────────────────────

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
        }

        $name  = $v->sanitize('name');
        $email = strtolower(trim($body['email']));

        if ($this->userModel->findByEmail($email)) {
            Response::error('Este email já está em uso.', 409);
        }

        $id = $this->userModel->create([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12]),
        ]);

        $user  = $this->userModel->findById($id);
        $token = $this->generateToken($user);

        // CORREÇÃO: Remove campos sensíveis antes de enviar para o Frontend
        unset($user['password'], $user['reset_token'], $user['reset_expires']);

        Logger::info('New user registered', ['id' => $id, 'email' => $email]);
        Response::success(['token' => $token, 'user' => $user], 'Conta criada com sucesso.', 201);
    }

    // ── POST /api/auth/login ────────────────────────────────

    public function login(): void {
        $body = $this->parseBody();
        $v    = new Validator($body);

        $v->required('email',    'Email')
          ->required('password', 'Senha')
          ->email('email');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $email = strtolower(trim($body['email']));
        $user  = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($body['password'], $user['password'])) {
            Response::error('Email ou senha incorretos.', 401);
        }

        if (!$user['is_active']) {
            Response::error('Conta desativada.', 403);
        }

        $this->userModel->updateLastLogin($user['id']);
        $token = $this->generateToken($user);

        // Remove campos sensíveis
        unset($user['password'], $user['reset_token'], $user['reset_expires']);

        Logger::info('User logged in', ['id' => $user['id']]);
        Response::success(['token' => $token, 'user' => $user], 'Login realizado com sucesso.');
    }

    // ── POST /api/auth/forgot-password ─────────────────────

    public function forgotPassword(): void {
        $body  = $this->parseBody();
        $email = strtolower(trim($body['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Email inválido.', 422);
        }

        $user = $this->userModel->findByEmail($email);

        // Always return success to avoid user enumeration
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->userModel->setResetToken($user['id'], $token, $expires);
            Logger::info('Password reset requested', ['email' => $email, 'token' => $token]);
            // In production: send email with reset link
        }

        Response::success(null, 'Se o email existir, você receberá instruções em breve.');
    }

    // ── POST /api/auth/reset-password ──────────────────────

    public function resetPassword(): void {
        $body = $this->parseBody();
        $v    = new Validator($body);

        $v->required('token',    'Token')
          ->required('password', 'Senha')
          ->strongPassword()
          ->matches('password', 'password_confirm', 'As senhas');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $user = $this->userModel->findByResetToken($body['token']);
        if (!$user) {
            Response::error('Token inválido ou expirado.', 400);
        }

        $this->userModel->updatePassword($user['id'], password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12]));
        $this->userModel->clearResetToken($user['id']);

        Response::success(null, 'Senha redefinida com sucesso.');
    }

    // ── GET /api/auth/me ────────────────────────────────────

    public function me(): void {
        require_once __DIR__ . '/../middleware/AuthMiddleware.php';
        $auth = AuthMiddleware::handle();
        $user = $this->userModel->findById($auth['sub']);
        if (!$user) Response::notFound('Utilizador não encontrado.');
        Response::success($user);
    }

    // ── Private ────────────────────────────────────────────

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
} // CORREÇÃO: Removida a vírgula errada que quebrava o script