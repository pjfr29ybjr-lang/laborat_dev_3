<?php
/**
 * routes/api.php
 * Roteador principal do backend.
 * Mapeia URI + método HTTP → Controller::método
 *
 * Rotas públicas  → sem autenticação
 * Rotas protegidas → AuthMiddleware::handle() chamado antes do controller
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/utils/Response.php';
require_once dirname(__DIR__) . '/controllers/AuthController.php';
require_once dirname(__DIR__) . '/controllers/WeatherController.php';
require_once dirname(__DIR__) . '/controllers/FavoriteController.php';
require_once dirname(__DIR__) . '/controllers/UserController.php';
require_once dirname(__DIR__) . '/exports/CsvExporter.php';

// ── Normalizar URI ────────────────────────────────────────────────────────────
$requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove prefixo /api
$path = preg_replace('#^/api#', '', $requestUri);

// ── Tabela de rotas ───────────────────────────────────────────────────────────
// [método, padrão regex, callable]
$routes = [

    // AUTH — públicas
    ['POST', '#^/auth/register$#',       fn() => (new AuthController())->register()],
    ['POST', '#^/auth/login$#',          fn() => (new AuthController())->login()],
    ['POST', '#^/auth/recover$#',        fn() => (new AuthController())->recover()],
    ['POST', '#^/auth/reset-password$#', fn() => (new AuthController())->resetPassword()],

    // AUTH — protegidas
    ['GET', '#^/auth/me$#', function() {
        $u = AuthMiddleware::handle();
        (new AuthController())->me($u);
    }],

    // WEATHER — protegidas
    ['GET', '#^/weather/current$#', function() {
        $u = AuthMiddleware::handle();
        (new WeatherController())->current($u);
    }],
    ['GET', '#^/weather/forecast$#', function() {
        $u = AuthMiddleware::handle();
        (new WeatherController())->forecast($u);
    }],
    ['GET', '#^/weather/geocode$#', function() {
        AuthMiddleware::handle(); // valida mas não usa payload
        (new WeatherController())->geocode();
    }],
    ['GET', '#^/weather/history$#', function() {
        $u = AuthMiddleware::handle();
        (new WeatherController())->history($u);
    }],
    ['DELETE', '#^/weather/history$#', function() {
        $u = AuthMiddleware::handle();
        (new WeatherController())->clearHistory($u);
    }],

    // FAVORITES — protegidas
    ['GET', '#^/favorites$#', function() {
        $u = AuthMiddleware::handle();
        (new FavoriteController())->index($u);
    }],
    ['POST', '#^/favorites$#', function() {
        $u = AuthMiddleware::handle();
        (new FavoriteController())->store($u);
    }],
    ['DELETE', '#^/favorites/(\d+)$#', function(int $id) {
        $u = AuthMiddleware::handle();
        (new FavoriteController())->destroy($u, $id);
    }],

    // USER — protegidas
    ['PUT', '#^/user/profile$#', function() {
        $u = AuthMiddleware::handle();
        (new UserController())->updateProfile($u);
    }],
    ['PUT', '#^/user/password$#', function() {
        $u = AuthMiddleware::handle();
        (new UserController())->updatePassword($u);
    }],

    // ADMIN
    ['GET', '#^/admin/users$#', function() {
        $u = AuthMiddleware::handle();
        (new UserController())->adminList($u);
    }],

    // EXPORTS
    ['GET', '#^/export/csv/history$#', fn() => CsvExporter::exportHistory()],
];

// ── Dispatcher ────────────────────────────────────────────────────────────────
foreach ($routes as [$method, $pattern, $handler]) {
    if ($requestMethod === $method && preg_match($pattern, $path, $matches)) {
        // Parâmetros capturados (ex: ID na URL)
        $params = array_map('intval', array_slice($matches, 1));
        $handler(...$params);
        exit;
    }
}

// Nenhuma rota encontrada
Response::notFound('Rota não encontrada: ' . $requestMethod . ' ' . $path);