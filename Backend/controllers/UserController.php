<?php
/**
 * User Controller
 * weather-system/backend/controllers/UserController.php
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class UserController {

    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // ── GET /api/user/profile ───────────────────────────────

    public function profile(): void {
        $auth = AuthMiddleware::handle();
        $user = $this->userModel->findById($auth['sub']);
        if (!$user) Response::notFound('Utilizador não encontrado.');
        Response::success($user);
    }

    // ── PUT /api/user/profile ───────────────────────────────

    public function updateProfile(): void {
        $auth = AuthMiddleware::handle();
        $body = $this->parseBody();
        $v    = new Validator($body);

        if (isset($body['name'])) {
            $v->required('name', 'Nome')->minLength('name', 2, 'Nome')->maxLength('name', 100, 'Nome');
        }

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $updateData = array_filter([
            'name'         => isset($body['name'])         ? $v->sanitize('name') : null,
            'language'     => isset($body['language'])     ? $v->sanitize('language') : null,
            'theme'        => isset($body['theme'])        ? $v->sanitize('theme') : null,
            'default_city' => isset($body['default_city']) ? $v->sanitize('default_city') : null,
        ], fn($v) => $v !== null);

        $this->userModel->updateProfile($auth['sub'], $updateData);
        $user = $this->userModel->findById($auth['sub']);
        Response::success($user, 'Perfil atualizado com sucesso.');
    }

    // ── PUT /api/user/password ──────────────────────────────

    public function changePassword(): void {
        $auth = AuthMiddleware::handle();
        $body = $this->parseBody();
        $v    = new Validator($body);

        $v->required('current_password', 'Senha atual')
          ->required('password',         'Nova senha')
          ->strongPassword()
          ->matches('password', 'password_confirm', 'As senhas');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $user = $this->userModel->findByEmail(''); // need full record
        // fetch with password
        $stmt = \Database::getInstance()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$auth['sub']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($body['current_password'], $user['password'])) {
            Response::error('Senha atual incorreta.', 401);
        }

        $this->userModel->updatePassword(
            $auth['sub'],
            password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12])
        );
        Response::success(null, 'Senha alterada com sucesso.');
    }

    // ── Private ────────────────────────────────────────────

    private function parseBody(): array {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?: [];
    }
}