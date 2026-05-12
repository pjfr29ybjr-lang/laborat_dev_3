<?php
/**
 * Favorites Controller
 * weather-system/backend/controllers/FavoriteController.php
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/FavoriteModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class FavoriteController {

    private FavoriteModel $model;

    public function __construct() {
        $this->model = new FavoriteModel();
    }

    // ── GET /api/favorites ──────────────────────────────────

    public function index(): void {
        $auth = AuthMiddleware::handle();
        Response::success($this->model->findByUser($auth['sub']));
    }

    // ── POST /api/favorites ─────────────────────────────────

    public function store(): void {
        $auth = AuthMiddleware::handle();
        $body = $this->parseBody();
        $v    = new Validator($body);

        $v->required('city_name', 'Cidade')
          ->required('country',   'País');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $city    = $v->sanitize('city_name');
        $country = strtoupper($v->sanitize('country'));

        if ($this->model->exists($auth['sub'], $city, $country)) {
            Response::error('Cidade já está nos favoritos.', 409);
        }

        $id = $this->model->add($auth['sub'], [
            'city_name' => $city,
            'country'   => $country,
            'lat'       => $body['lat'] ?? null,
            'lon'       => $body['lon'] ?? null,
        ]);

        Response::success(['id' => $id], 'Cidade adicionada aos favoritos.', 201);
    }

    // ── DELETE /api/favorites/:id ───────────────────────────

    public function destroy(int $id): void {
        $auth = AuthMiddleware::handle();

        if (!$this->model->remove($id, $auth['sub'])) {
            Response::notFound('Favorito não encontrado.');
        }

        Response::success(null, 'Removido dos favoritos.');
    }
}