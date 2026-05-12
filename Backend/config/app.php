<?php
/**
 * Application Configuration
 * weather-system/backend/config/app.php
 */

// JWT
define('JWT_SECRET',  getenv('JWT_SECRET')  ?: 'your-super-secret-jwt-key-change-in-production-!@#$%');
define('JWT_EXPIRE',  (int)(getenv('JWT_EXPIRE')  ?: 86400)); // 24h in seconds

// OpenWeatherMap
define('OWM_API_KEY', getenv('OWM_API_KEY') ?: 'YOUR_OPENWEATHERMAP_API_KEY');
define('OWM_BASE_URL','https://api.openweathermap.org/data/2.5');
define('OWM_GEO_URL', 'https://api.openweathermap.org/geo/1.0');

// App
define('APP_NAME',    'Weather System');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     getenv('APP_ENV') ?: 'production');
define('APP_DEBUG',   APP_ENV === 'development');

// CORS origins (comma-separated in env)
define('CORS_ORIGIN', getenv('CORS_ORIGIN') ?: 'http://localhost');

// Password rules
define('PASSWORD_MIN_LENGTH', 8);

// Pagination
define('DEFAULT_PAGE_SIZE', 20);