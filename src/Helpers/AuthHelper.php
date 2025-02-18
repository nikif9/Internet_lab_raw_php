<?php
declare(strict_types=1);

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthHelper {
    private const SECRET_KEY = 'your-secret-key-here'; // Замените на надёжное значение
    private const ALGORITHM = 'HS256';
    private const EXPIRATION_TIME = 3600; // 1 час

    /**
     * Генерирует JWT-токен с данными payload.
     */
    public static function generateToken(array $payload): string {
        $issuedAt = time();
        $expire = $issuedAt + self::EXPIRATION_TIME;
        $token = [
            'iat'  => $issuedAt,
            'exp'  => $expire,
            'data' => $payload,
        ];
        return JWT::encode($token, self::SECRET_KEY, self::ALGORITHM);
    }

    /**
     * Проверяет JWT-токен и возвращает данные payload или null.
     */
    public static function verifyToken(string $token): ?array {
        try {
            $decoded = JWT::decode($token, new Key(self::SECRET_KEY, self::ALGORITHM));
            return (array)$decoded->data;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Извлекает токен из заголовка Authorization.
     */
    public static function getTokenFromHeader(): ?string {
        $headers = getallheaders();
        if (!empty($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
