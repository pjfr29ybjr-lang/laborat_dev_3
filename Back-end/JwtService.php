<?php
/**
 * services/JwtService.php
 * Geração e validação de JSON Web Tokens (JWT) sem biblioteca externa.
 * Algoritmo: HS256
 */

declare(strict_types=1);

class JwtService
{
    private static string $secret;
    private static int    $expiry;

    private static function init(): void
    {
        self::$secret = JWT_SECRET;
        self::$expiry = JWT_EXPIRY;
    }

    // ── Geração ──────────────────────────────────────────────────────────────
    public static function generate(array $payload): string
    {
        self::init();

        $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expiry;

        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature      = self::sign("$header.$payloadEncoded");

        return "$header.$payloadEncoded.$signature";
    }

    // ── Validação ─────────────────────────────────────────────────────────────
    public static function validate(string $token): array
    {
        self::init();

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Token inválido');
        }

        [$header, $payloadEncoded, $signature] = $parts;

        $expectedSig = self::sign("$header.$payloadEncoded");
        if (!hash_equals($expectedSig, $signature)) {
            throw new RuntimeException('Assinatura inválida');
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if ($payload['exp'] < time()) {
            throw new RuntimeException('Token expirado');
        }

        return $payload;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private static function sign(string $data): string
    {
        return self::base64UrlEncode(
            hash_hmac('sha256', $data, self::$secret, true)
        );
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}