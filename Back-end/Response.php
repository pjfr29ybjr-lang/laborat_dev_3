<?php
/**
 * utils/Response.php
 * Padroniza todas as respostas JSON do backend.
 *
 * Formato:
 *   { "success": bool, "message": string, "data": mixed }
 */

declare(strict_types=1);

class Response
{
    public static function success(mixed $data = null, string $message = 'OK', int $code = 200): never
    {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message, int $code = 400, mixed $data = null): never
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function unauthorized(string $message = 'Não autorizado'): never
    {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Acesso negado'): never
    {
        self::error($message, 403);
    }

    public static function notFound(string $message = 'Recurso não encontrado'): never
    {
        self::error($message, 404);
    }
}