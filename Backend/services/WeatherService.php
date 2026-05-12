<?php
/**
 * OpenWeatherMap API Service
 * weather-system/backend/services/WeatherService.php
 */

class WeatherService {

    private string $apiKey;
    private string $baseUrl;
    private string $geoUrl;

    public function __construct() {
        $this->apiKey  = OWM_API_KEY;
        $this->baseUrl = OWM_BASE_URL;
        $this->geoUrl  = OWM_GEO_URL;
    }

    // ── Public API ─────────────────────────────────────────

    public function getCurrentWeather(string $city, string $units = 'metric', string $lang = 'pt'): array {
        return $this->request($this->baseUrl . '/weather', [
            'q'     => $city,
            'units' => $units,
            'lang'  => $lang,
        ]);
    }

    public function getCurrentWeatherByCoords(float $lat, float $lon, string $units = 'metric', string $lang = 'pt'): array {
        return $this->request($this->baseUrl . '/weather', [
            'lat'   => $lat,
            'lon'   => $lon,
            'units' => $units,
            'lang'  => $lang,
        ]);
    }

    public function getForecast(string $city, string $units = 'metric', string $lang = 'pt'): array {
        return $this->request($this->baseUrl . '/forecast', [
            'q'     => $city,
            'units' => $units,
            'lang'  => $lang,
            'cnt'   => 40, // 5 days × 8 per day
        ]);
    }

    public function getForecastByCoords(float $lat, float $lon, string $units = 'metric', string $lang = 'pt'): array {
        return $this->request($this->baseUrl . '/forecast', [
            'lat'   => $lat,
            'lon'   => $lon,
            'units' => $units,
            'lang'  => $lang,
            'cnt'   => 40,
        ]);
    }

    public function searchCities(string $query, int $limit = 5): array {
        return $this->request($this->geoUrl . '/direct', [
            'q'     => $query,
            'limit' => $limit,
        ]);
    }

    // ── Private ────────────────────────────────────────────

    private function request(string $url, array $params): array {
        $params['appid'] = $this->apiKey;
        $fullUrl = $url . '?' . http_build_query($params);

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 10,
                'header'  => 'User-Agent: WeatherSystem/1.0',
            ],
            'ssl' => ['verify_peer' => true],
        ]);

        $response = @file_get_contents($fullUrl, false, $ctx);

        if ($response === false) {
            throw new RuntimeException('Falha ao conectar à API de clima.');
        }

        $data = json_decode($response, true);

        if (isset($data['cod']) && (int)$data['cod'] >= 400) {
            throw new RuntimeException($data['message'] ?? 'Erro da API de clima.');
        }

        return $data;
    }
}