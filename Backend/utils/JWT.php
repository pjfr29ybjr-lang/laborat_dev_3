<?php
/**
 * JWT Utility
 * weather-system/backend/utils/JWT.php
 */

class JWT {

    /**
     * Encode payload into a JWT token
     */
    public static function encode(array $payload): string {
        $header  = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = self::base64UrlEncode(json_encode($payload));
        $sig     = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
        );
        return "$header.$payload.$sig";
    }

    /**
     * Decode and verify a JWT token
     * @throws RuntimeException on invalid/expired token
     */
    public static function decode(string $token): array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token structure.');
        }
        [$header, $payload, $sig] = $parts;

        $expectedSig = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
        );
        if (!hash_equals($expectedSig, $sig)) {
            throw new RuntimeException('Invalid token signature.');
        }

        $data = json_decode(self::base64UrlDecode($payload), true);
        if (!$data) {
            throw new RuntimeException('Invalid token payload.');
        }
        if (isset($data['exp']) && $data['exp'] < time()) {
            throw new RuntimeException('Token expired.');
        }
        return $data;
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}