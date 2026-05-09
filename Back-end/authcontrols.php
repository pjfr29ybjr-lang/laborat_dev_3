<?php
/**
 * controllers/AuthController.php
 * Lógica de negócio para autenticação:
 *   register, login, logout, recover, reset-password
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/services/JwtService.php';
require_once dirname(__DIR__) . '/utils/Response.php';
require_once dirname(__DIR__) . '/utils/Validator.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // POST /api/auth/register
    public function register(): void
    {
        $data = Validator::bodyJson();
        $v    = new Validator($data);
        $v->required('name')->required('email')->required('password')
          ->email('email')
          ->minLength('password', 8);

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        if ($this->userModel->findByEmail($v->get('email'))) {
            Response::error('Este email já está registado.', 409);
        }

        $id   = $this->userModel->create($v->get('name'), $v->get('email'), $v->get('password'));
        $user = $this->userModel->findById($id);
        $token = JwtService::generate(['sub' => $id, 'role' => 'user']);

        Response::success(['token' => $token, 'user' => $user], 'Conta criada com sucesso.', 201);
    }

    // POST /api/auth/login
    public function login(): void
    {
        $data = Validator::bodyJson();
        $v    = new Validator($data);
        $v->required('email')->required('password')->email('email');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $user = $this->userModel->findByEmail($v->get('email'));

        if (!$user || !password_verify($v->get('password'), $user['password'])) {
            Response::error('Credenciais inválidas.', 401);
        }

        $token = JwtService::generate(['sub' => $user['id'], 'role' => $user['role']]);

        unset($user['password'], $user['reset_token'], $user['reset_expires']);

        Response::success(['token' => $token, 'user' => $user], 'Login realizado com sucesso.');
    }

    // POST /api/auth/recover
    public function recover(): void
    {
        $data = Validator::bodyJson();
        $v    = new Validator($data);
        $v->required('email')->email('email');

        if ($v->fails()) {
            Response::error('Email inválido.', 422);
        }

        $user = $this->userModel->findByEmail($v->get('email'));

        // Resposta genérica por segurança (não revelar se email existe)
        if (!$user) {
            Response::success(null, 'Se o email existir, receberá as instruções.');
        }

        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $this->userModel->saveResetToken($user['id'], $token, $expires);

        // Em produção: enviar email com link contendo o token
        // MailService::send($user['email'], 'reset', ['token' => $token]);

        Response::success(
            APP_ENV === 'development' ? ['debug_token' => $token] : null,
            'Se o email existir, receberá as instruções.'
        );
    }

    // POST /api/auth/reset-password
    public function resetPassword(): void
    {
        $data = Validator::bodyJson();
        $v    = new Validator($data);
        $v->required('token')->required('password')->minLength('password', 8);

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $user = $this->userModel->findByResetToken($v->get('token'));

        if (!$user) {
            Response::error('Token inválido ou expirado.', 400);
        }

        $this->userModel->updatePassword($user['id'], $v->get('password'));
        $this->userModel->clearResetToken($user['id']);

        Response::success(null, 'Senha redefinida com sucesso.');
    }

    // GET /api/auth/me   (rota protegida)
    public function me(array $authUser): void
    {
        $user = $this->userModel->findById($authUser['sub']);

        if (!$user) {
            Response::notFound('Utilizador não encontrado.');
        }

        Response::success($user);
    }
}