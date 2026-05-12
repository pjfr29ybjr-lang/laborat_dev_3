<?php
/**
 * Weather Controller
 * weather-system/backend/controllers/WeatherController.php
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/WeatherService.php';
require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Logger.php';

class WeatherController {

    private WeatherService $weather;
    private HistoryModel   $historyModel;

    public function __construct() {
        $this->weather      = new WeatherService();
        $this->historyModel = new HistoryModel();
    }

    // ── GET /api/weather/current?city=&units=&lang= ─────────

    public function current(): void {
        $auth  = AuthMiddleware::handle();
        $city  = trim($_GET['city']  ?? '');
        $units = $_GET['units'] ?? 'metric';
        $lang  = $_GET['lang']  ?? 'pt';

        if (!$city) Response::error('Parâmetro city obrigatório.', 422);

        try {
            $data = $this->weather->getCurrentWeather($city, $units, $lang);
            $this->saveHistory($auth['sub'], $data);
            Response::success($data);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 502);
        }
    }

    // ── GET /api/weather/coords?lat=&lon=&units=&lang= ──────

    public function byCoords(): void {
        $auth  = AuthMiddleware::handle();
        $lat   = (float)($_GET['lat']   ?? 0);
        $lon   = (float)($_GET['lon']   ?? 0);
        $units = $_GET['units'] ?? 'metric';
        $lang  = $_GET['lang']  ?? 'pt';

        if (!$lat && !$lon) Response::error('Coordenadas inválidas.', 422);

        try {
            $data = $this->weather->getCurrentWeatherByCoords($lat, $lon, $units, $lang);
            $this->saveHistory($auth['sub'], $data);
            Response::success($data);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 502);
        }
    }

    // ── GET /api/weather/forecast?city=&units=&lang= ────────

    public function forecast(): void {
        AuthMiddleware::handle();
        $city  = trim($_GET['city']  ?? '');
        $units = $_GET['units'] ?? 'metric';
        $lang  = $_GET['lang']  ?? 'pt';

        if (!$city) Response::error('Parâmetro city obrigatório.', 422);

        try {
            $data = $this->weather->getForecast($city, $units, $lang);
            Response::success($data);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 502);
        }
    }

    // ── GET /api/weather/forecast-coords?lat=&lon= ──────────

    public function forecastByCoords(): void {
        AuthMiddleware::handle();
        $lat   = (float)($_GET['lat']   ?? 0);
        $lon   = (float)($_GET['lon']   ?? 0);
        $units = $_GET['units'] ?? 'metric';
        $lang  = $_GET['lang']  ?? 'pt';

        try {
            $data = $this->weather->getForecastByCoords($lat, $lon, $units, $lang);
            Response::success($data);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 502);
        }
    }

    // ── GET /api/weather/search?q= ──────────────────────────

    public function search(): void {
        AuthMiddleware::handle();
        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) Response::error('Consulta muito curta.', 422);

        try {
            $results = $this->weather->searchCities($query);
            Response::success($results);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 502);
        }
    }

    // ── Private ────────────────────────────────────────────

    private function saveHistory(int $userId, array $weatherData): void {
        try {
            $this->historyModel->add($userId, [
                'city_name'    => $weatherData['name'] ?? 'Unknown',
                'country'      => $weatherData['sys']['country'] ?? '',
                'lat'          => $weatherData['coord']['lat'] ?? null,
                'lon'          => $weatherData['coord']['lon'] ?? null,
                'weather_data' => $weatherData,
            ]);
        } catch (Exception $e) {
            Logger::warning('Failed to save search history', ['error' => $e->getMessage()]);
        }
    }
}