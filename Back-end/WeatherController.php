<?php
/**
 * controllers/WeatherController.php
 * Lógica de negócio para dados meteorológicos.
 * Consome WeatherService e regista histórico de pesquisas.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/services/WeatherService.php';
require_once dirname(__DIR__) . '/models/SearchHistory.php';
require_once dirname(__DIR__) . '/utils/Response.php';

class WeatherController
{
    private WeatherService $weatherService;
    private SearchHistory  $historyModel;

    public function __construct()
    {
        $this->weatherService = new WeatherService();
        $this->historyModel   = new SearchHistory();
    }

    // GET /api/weather/current?city=Luanda&units=metric&lang=pt
    public function current(array $authUser): void
    {
        $city  = trim($_GET['city']  ?? '');
        $units = $_GET['units'] ?? 'metric';
        $lang  = $_GET['lang']  ?? 'pt';

        if (!$city) {
            Response::error('Parâmetro "city" é obrigatório.', 422);
        }

        try {
            $data = $this->weatherService->currentByCity($city, $units, $lang);

            // Registar no histórico
            $temp      = $data['main']['temp']              ?? null;
            $condition = $data['weather'][0]['description'] ?? null;
            $country   = $data['sys']['country']            ?? '';

            $this->historyModel->add($authUser['sub'], $data['name'], $country, $temp, $condition);

            Response::success($data);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    // GET /api/weather/forecast?city=Luanda&units=metric&lang=pt
    public function forecast(array $authUser): void
    {
        $city  = trim($_GET['city']  ?? '');
        $units = $_GET['units'] ?? 'metric';
        $lang  = $_GET['lang']  ?? 'pt';

        if (!$city) {
            Response::error('Parâmetro "city" é obrigatório.', 422);
        }

        try {
            $data = $this->weatherService->forecastByCity($city, $units, $lang);
            Response::success($data);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    // GET /api/weather/geocode?q=Luanda
    public function geocode(): void
    {
        $q = trim($_GET['q'] ?? '');

        if (!$q) {
            Response::error('Parâmetro "q" é obrigatório.', 422);
        }

        try {
            $data = $this->weatherService->geocode($q);
            Response::success($data);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    // GET /api/weather/history
    public function history(array $authUser): void
    {
        $limit = min((int) ($_GET['limit'] ?? 20), 100);
        $data  = $this->historyModel->byUser($authUser['sub'], $limit);
        Response::success($data);
    }

    // DELETE /api/weather/history
    public function clearHistory(array $authUser): void
    {
        $this->historyModel->clearByUser($authUser['sub']);
        Response::success(null, 'Histórico apagado com sucesso.');
    }
}