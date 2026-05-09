<?php
/**
 * controllers/UserController.php
 * Gestão do perfil do utilizador e área de administração.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/utils/Response.php';
require_once dirname(__DIR__) . '/utils/Validator.php';

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // PUT /api/user/profile
    public function updateProfile(array $authUser): void
    {
        $data = Validator::bodyJson();
        $v    = new Validator($data);
        $v->required('name');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $allowed = ['name', 'language', 'theme', 'unit'];
        $fields  = array_intersect_key($data, array_flip($allowed));

        $this->userModel->update($authUser['sub'], $fields);
        $user = $this->userModel->findById($authUser['sub']);

        Response::success($user, 'Perfil actualizado com sucesso.');
    }

    // PUT /api/user/password
    public function updatePassword(array $authUser): void
    {
        $data = Validator::bodyJson();
        $v    = new Validator($data);
        $v->required('current_password')->required('new_password')
          ->minLength('new_password', 8);

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $user = $this->userModel->findByEmail(''); // load full user with password
        // Re-fetch user with password field
        $stmt = \Database::getInstance()->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $authUser['sub']]);
        $fullUser = $stmt->fetch();

        if (!$fullUser || !password_verify($data['current_password'], $fullUser['password'])) {
            Response::error('Senha actual incorrecta.', 401);
        }

        $this->userModel->updatePassword($authUser['sub'], $data['new_password']);
        Response::success(null, 'Senha alterada com sucesso.');
    }

    // ── ADMIN ─────────────────────────────────────────────────────────────────

    // GET /api/admin/users?page=1
    public function adminList(array $authUser): void
    {
        if ($authUser['role'] !== 'admin') {
            Response::forbidden();
        }

        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $users = $this->userModel->allPaginated($page);
        $total = $this->userModel->count();

        Response::success(['users' => $users, 'total' => $total, 'page' => $page]);
    }
}