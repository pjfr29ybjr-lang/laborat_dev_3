<?php
/**
 * API Entry Point — Router (CORRIGIDO PARA O XAMPP LOCAL)
 * weather-system/Backend/index.php
 */

// ── Bootstrap ──────────────────────────────────────────────
// CORREÇÃO 1: Usa __DIR__ para o PHP não se perder a procurar as pastas
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/middleware/CorsMiddleware.php';
require_once BASE_PATH . '/utils/Response.php';

CorsMiddleware::handle();

// ── Router ─────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// CORREÇÃO 2: Limpa o caminho específico do teu localhost para o roteador funcionar
// Esta linha agora ignora o index.php quando o roteador for processar a rota
$uri = preg_replace('#^(/weather-app/Backend/index.php|/weather-app/Backend|/api)#i', '', $uri);
$uri = rtrim($uri, '/') ?: '/';

// Split for param extraction
$segments = explode('/', ltrim($uri, '/'));

try {
    match(true) {

        // Auth
        $uri === '/auth/register' && $method === 'POST' => (function() {
            require_once BASE_PATH . '/controllers/AuthController.php';
            (new AuthController())->register();
        })(),

        $uri === '/auth/login' && $method === 'POST' => (function() {
            require_once BASE_PATH . '/controllers/AuthController.php';
            (new AuthController())->login();
        })(),

        $uri === '/auth/forgot-password' && $method === 'POST' => (function() {
            require_once BASE_PATH . '/controllers/AuthController.php';
            (new AuthController())->forgotPassword();
        })(),

        $uri === '/auth/reset-password' && $method === 'POST' => (function() {
            require_once BASE_PATH . '/controllers/AuthController.php';
            (new AuthController())->resetPassword();
        })(),

        $uri === '/auth/me' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/AuthController.php';
            (new AuthController())->me();
        })(),

        // Weather
        $uri === '/weather/current' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/WeatherController.php';
            (new WeatherController())->current();
        })(),

        $uri === '/weather/coords' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/WeatherController.php';
            (new WeatherController())->byCoords();
        })(),

        $uri === '/weather/forecast' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/WeatherController.php';
            (new WeatherController())->forecast();
        })(),

        $uri === '/weather/forecast-coords' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/WeatherController.php';
            (new WeatherController())->forecastByCoords();
        })(),

        $uri === '/weather/search' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/WeatherController.php';
            (new WeatherController())->search();
        })(),

        // Favorites
        $uri === '/favorites' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/FavoriteController.php';
            (new FavoriteController())->index();
        })(),

        $uri === '/favorites' && $method === 'POST' => (function() {
            require_once BASE_PATH . '/controllers/FavoriteController.php';
            (new FavoriteController())->store();
        })(),

        // DELETE /favorites/:id
        preg_match('#^/favorites/(\d+)$#', $uri, $m) && $method === 'DELETE' => (function() use ($m) {
            require_once BASE_PATH . '/controllers/FavoriteController.php';
            (new FavoriteController())->destroy((int)$m[1]);
        })(),

        // History
        $uri === '/history' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/HistoryController.php';
            (new HistoryController())->index();
        })(),

        $uri === '/history' && $method === 'DELETE' => (function() {
            require_once BASE_PATH . '/controllers/HistoryController.php';
            (new HistoryController())->clear();
        })(),

        // User / Profile
        $uri === '/user/profile' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/UserController.php';
            (new UserController())->profile();
        })(),

        $uri === '/user/profile' && $method === 'PUT' => (function() {
            require_once BASE_PATH . '/controllers/UserController.php';
            (new UserController())->updateProfile();
        })(),

        $uri === '/user/password' && $method === 'PUT' => (function() {
            require_once BASE_PATH . '/controllers/UserController.php';
            (new UserController())->changePassword();
        })(),

        // Exports
        $uri === '/export/history/csv' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/ExportController.php';
            (new ExportController())->historyCSV();
        })(),

        $uri === '/export/favorites/csv' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/ExportController.php';
            (new ExportController())->favoritesCSV();
        })(),

        $uri === '/export/history/pdf' && $method === 'GET' => (function() {
            require_once BASE_PATH . '/controllers/ExportController.php';
            (new ExportController())->historyPDF();
        })(),

        // Health check
        $uri === '/health' => Response::success(['status' => 'ok', 'version' => defined('APP_VERSION') ? APP_VERSION : '1.0.0']),

        // 404
        default => Response::notFound("Rota não encontrada: $method $uri"),
    };

} catch (Throwable $e) {
    // CORREÇÃO 3: Garante que os erros são mostrados para ajudar no Debug
    $debug = defined('APP_DEBUG') ? APP_DEBUG : true;
    if ($debug) {
        Response::serverError($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    } else {
        if (file_exists(BASE_PATH . 'utils/Logger.php')) {
            require_once BASE_PATH . 'utils/Logger.php';
            Logger::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
        Response::serverError('Erro interno do servidor.');
    }
}