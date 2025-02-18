<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database\Database;
use App\Models\User;
use App\Helpers\AuthHelper;
use PDO;

class AuthController {
    /**
     * @var PDO Экземпляр PDO для взаимодействия с базой данных
     */
    private PDO $pdo;

    /**
     * Конструктор AuthController.
     *
     * @param Database $db Экземпляр класса Database
     */
    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    /**
     * Отправляет JSON-ответ с заданным статусом.
     *
     * @param array $data Данные для отправки в формате JSON
     * @param int $status HTTP-статус код
     * @return void
     */
    private function sendJson(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Авторизует пользователя.
     * Обрабатывает POST-запрос на /login.
     *
     * @return void
     */
    public function login(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!isset($data['username'], $data['password'])) {
            $this->sendJson(['error' => 'Отсутствуют поля username или password'], 400);
            return;
        }
        
        $user = User::authenticate($this->pdo, $data['username'], $data['password']);
        if ($user) {
            // Генерация токена на основе user_id
            $token = AuthHelper::generateToken(['user_id' => $user->id]);
            $this->sendJson([
                'message' => 'Авторизация успешна',
                'user_id' => $user->id,
                'token'   => $token
            ]);
        } else {
            $this->sendJson(['error' => 'Неверные учётные данные'], 401);
        }
    }
}
