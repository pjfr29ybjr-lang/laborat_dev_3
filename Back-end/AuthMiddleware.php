<?php
/**
 * middleware/AuthMiddleware.php
 * Valida o JWT enviado no header Authorization: Bearer <token>
 * Injeta os dados do utilizador autenticado em $_REQUEST['auth_user']
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/services/JwtService.php';
require_once dirname(__DIR__) . '/utils/Response.php';

class AuthMiddleware
{
    public static function handle(): array
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            Response::unauthorized('Token não fornecido.');
        }

        $token = substr($authHeader, 7);

        try {
            $payload = JwtService::validate($token);
        } catch (RuntimeException $e) {
            Response::unauthorized($e->getMessage());
        }

        return $payload;
    }
}