<?php
/**
 * services/WeatherService.php
 * Integração com a API OpenWeatherMap.
 * Toda comunicação HTTP com a API externa passa por aqui.
 * Controllers não chamam curl directamente.
 */

declare(strict_types=1);

class WeatherService
{
    private string $apiKey;
    private string $baseUrl;
    private string $geoUrl;

    public function __construct()
    {
        $this->apiKey  = OWM_API_KEY;
        $this->baseUrl = OWM_BASE_URL;
        $this->geoUrl  = OWM_GEO_URL;
    }

    // ── Clima actual por nome de cidade ──────────────────────────────────────
    public function currentByCity(string $city, string $units = 'metric', string $lang = 'pt'): array
    {
        $url = $this->buildUrl('/weather', [
            'q'     => $city,
            'units' => $units,
            'lang'  => $lang,
        ]);
        return $this->fetch($url);
    }

    // ── Clima actual por coordenadas ─────────────────────────────────────────
    public function currentByCoords(float $lat, float $lon, string $units = 'metric', string $lang = 'pt'): array
    {
        $url = $this->buildUrl('/weather', [
            'lat'   => $lat,
            'lon'   => $lon,
            'units' => $units,
            'lang'  => $lang,
        ]);
        return $this->fetch($url);
    }

    // ── Previsão de 5 dias / 3 em 3 horas ───────────────────────────────────
    public function forecastByCity(string $city, string $units = 'metric', string $lang = 'pt'): array
    {
        $url = $this->buildUrl('/forecast', [
            'q'     => $city,
            'units' => $units,
            'lang'  => $lang,
            'cnt'   => 40, // 40 registos × 3h = 5 dias
        ]);
        return $this->fetch($url);
    }

    // ── Geocodificação → coordenadas de uma cidade ───────────────────────────
    public function geocode(string $city, int $limit = 5): array
    {
        $url = $this->geoUrl . '/direct?' . http_build_query([
            'q'     => $city,
            'limit' => $limit,
            'appid' => $this->apiKey,
        ]);
        return $this->fetch($url);
    }

    // ── Helpers internos ─────────────────────────────────────────────────────
    private function buildUrl(string $endpoint, array $params): string
    {
        $params['appid'] = $this->apiKey;
        return $this->baseUrl . $endpoint . '?' . http_build_query($params);
    }

    private function fetch(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new RuntimeException("Erro de ligação à API: $error");
        }

        $data = json_decode($body, true);

        if ($httpCode === 401) {
            throw new RuntimeException('Chave de API inválida ou em falta.');
        }

        if ($httpCode === 404) {
            throw new RuntimeException('Cidade não encontrada.');
        }

        if ($httpCode !== 200 || !is_array($data)) {
            $msg = $data['message'] ?? 'Erro desconhecido na API de tempo.';
            throw new RuntimeException($msg);
        }

        return $data;
    }
}