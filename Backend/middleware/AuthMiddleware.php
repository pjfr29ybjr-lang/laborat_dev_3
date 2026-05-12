<?php
/**
 * Authentication Middleware
 * weather-system/backend/middleware/AuthMiddleware.php
 */

require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware {

    /**
     * Validate JWT and attach user data to request globals.
     * Returns decoded payload on success.
     */
    public static function handle(): array {
        $token = self::extractToken();
        if (!$token) {
            Response::unauthorized('Token de autenticação não fornecido.');
        }
        try {
            $payload = JWT::decode($token);
        } catch (RuntimeException $e) {
            Response::unauthorized($e->getMessage());
        }
        // Expose user data globally
        $GLOBALS['auth_user'] = $payload;
        return $payload;
    }

    /**
     * Optionally require admin role.
     */
    public static function requireAdmin(): array {
        $user = self::handle();
        if (($user['role'] ?? '') !== 'admin') {
            Response::forbidden('Acesso restrito a administradores.');
        }
        return $user;
    }

    // ── Private ────────────────────────────────────────────

    private static function extractToken(): ?string {
        // Authorization: Bearer <token>
        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? getallheaders()['Authorization']
               ?? getallheaders()['authorization']
               ?? '';

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        // Fallback: query string (for file downloads)
        if (!empty($_GET['token'])) {
            return htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');
        }
        return null;
    }
}