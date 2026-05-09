<?php
/**
 * config/env.php
 * Centraliza todas as variáveis de configuração do sistema.
 * Em produção, carregue de um ficheiro .env real (ex: vlucas/phpdotenv).
 */

declare(strict_types=1);

define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',     getenv('DB_PORT')     ?: '3306');
define('DB_NAME',     getenv('DB_NAME')     ?: 'weather_system');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASS',     getenv('DB_PASS')     ?: '');

define('JWT_SECRET',  getenv('JWT_SECRET')  ?: 'CHANGE_THIS_SECRET_KEY_IN_PRODUCTION');
define('JWT_EXPIRY',  (int)(getenv('JWT_EXPIRY') ?: 3600)); // segundos

define('OWM_API_KEY', getenv('OWM_API_KEY') ?: 'YOUR_OPENWEATHERMAP_API_KEY');
define('OWM_BASE_URL','https://api.openweathermap.org/data/2.5');
define('OWM_GEO_URL', 'https://api.openweathermap.org/geo/1.0');

define('APP_ENV',     getenv('APP_ENV')     ?: 'development');
define('FRONTEND_URL',getenv('FRONTEND_URL')?: 'http://localhost:4200');