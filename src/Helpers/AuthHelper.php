<?php
declare(strict_types=1);

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthHelper {
    /**
     * @var string Секретный ключ для подписи JWT-токенов
     */
    private const SECRET_KEY = 'your-secret-key-here'; // Замените на надёжное значение

    /**
     * @var string Алгоритм подписи JWT-токенов
     */
    private const ALGORITHM = 'HS256';

    /**
     * @var int Время жизни токена в секундах (1 час)
     */
    private const EXPIRATION_TIME = 3600;

    /**
     * Генерирует JWT-токен с данными payload.
     *
     * @param array $payload Данные, которые будут включены в токен
     * @return string Сгенерированный JWT-токен
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
     * Проверяет JWT-токен и возвращает данные payload или null при неудаче.
     *
     * @param string $token JWT-токен для проверки
     * @return array|null Декодированные данные payload или null в случае ошибки
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
     * Извлекает JWT-токен из заголовка Authorization.
     *
     * @return string|null Извлечённый JWT-токен или null, если заголовок отсутствует
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
