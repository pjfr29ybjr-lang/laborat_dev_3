<?php
/**
 * controllers/FavoriteController.php
 * CRUD completo para cidades favoritas do utilizador.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/models/Favorite.php';
require_once dirname(__DIR__) . '/utils/Response.php';
require_once dirname(__DIR__) . '/utils/Validator.php';

class FavoriteController
{
    private Favorite $favoriteModel;

    public function __construct()
    {
        $this->favoriteModel = new Favorite();
    }

    // GET /api/favorites
    public function index(array $authUser): void
    {
        $favorites = $this->favoriteModel->allByUser($authUser['sub']);
        Response::success($favorites);
    }

    // POST /api/favorites
    public function store(array $authUser): void
    {
        $data = Validator::bodyJson();
        $v    = new Validator($data);
        $v->required('city_name')->required('country');

        if ($v->fails()) {
            Response::error('Dados inválidos.', 422, $v->errors());
        }

        $city    = $v->get('city_name');
        $country = $v->get('country');

        if ($this->favoriteModel->exists($authUser['sub'], $city, $country)) {
            Response::error('Esta cidade já está nos favoritos.', 409);
        }

        $id = $this->favoriteModel->create(
            $authUser['sub'],
            $city,
            $country,
            isset($data['lat']) ? (float) $data['lat'] : null,
            isset($data['lon']) ? (float) $data['lon'] : null
        );

        Response::success(['id' => $id], 'Favorito adicionado.', 201);
    }

    // DELETE /api/favorites/{id}
    public function destroy(array $authUser, int $id): void
    {
        $deleted = $this->favoriteModel->delete($id, $authUser['sub']);

        if (!$deleted) {
            Response::notFound('Favorito não encontrado.');
        }

        Response::success(null, 'Favorito removido.');
    }
}