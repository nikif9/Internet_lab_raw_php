<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database\Database;
use App\Models\User;
use App\Helpers\AuthHelper;
use PDO;

class UserController {
    private PDO $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    private function sendJson(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // Создание пользователя (регистрация): POST /users
    public function createUser(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!isset($data['username'], $data['password'], $data['email'])) {
            $this->sendJson(['error' => 'Отсутствуют обязательные поля'], 400);
            return;
        }
        $user = User::create($this->pdo, $data['username'], $data['password'], $data['email']);
        if ($user) {
            $this->sendJson(['message' => 'Пользователь создан', 'id' => $user->id], 201);
        } else {
            $this->sendJson(['error' => 'Не удалось создать пользователя'], 500);
        }
    }

    // Получение информации о пользователе: GET /users/{id}
    // (Публичный эндпоинт – не требует авторизации)
    public function getUser(string $id): void {
        if (!ctype_digit($id)) {
            $this->sendJson(['error' => 'Некорректный ID'], 400);
            return;
        }
        $user = User::getById($this->pdo, (int)$id);
        if ($user) {
            $this->sendJson($user->toArray());
        } else {
            $this->sendJson(['error' => 'Пользователь не найден'], 404);
        }
    }

    // Обновление информации о пользователе: PUT /users/{id}
    // Требует валидного токена – пользователь может обновлять только свои данные
    public function updateUser(string $id): void {
        if (!ctype_digit($id)) {
            $this->sendJson(['error' => 'Некорректный ID'], 400);
            return;
        }
        // Проверка наличия и валидности токена
        $token = AuthHelper::getTokenFromHeader();
        if (!$token || !($payload = AuthHelper::verifyToken($token))) {
            $this->sendJson(['error' => 'Unauthorized'], 401);
            return;
        }
        if ((int)$payload['user_id'] !== (int)$id) {
            $this->sendJson(['error' => 'Forbidden'], 403);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data)) {
            $this->sendJson(['error' => 'Нет полей для обновления'], 400);
            return;
        }
        $user = User::getById($this->pdo, (int)$id);
        if (!$user) {
            $this->sendJson(['error' => 'Пользователь не найден'], 404);
            return;
        }
        if ($user->update($this->pdo, $data)) {
            $this->sendJson(['message' => 'Пользователь обновлён']);
        } else {
            $this->sendJson(['error' => 'Не удалось обновить пользователя'], 500);
        }
    }

    // Удаление пользователя: DELETE /users/{id}
    // Требует валидного токена – пользователь может удалить только свой аккаунт
    public function deleteUser(string $id): void {
        if (!ctype_digit($id)) {
            $this->sendJson(['error' => 'Некорректный ID'], 400);
            return;
        }
        // Проверка наличия и валидности токена
        $token = AuthHelper::getTokenFromHeader();
        if (!$token || !($payload = AuthHelper::verifyToken($token))) {
            $this->sendJson(['error' => 'Unauthorized'], 401);
            return;
        }
        if ((int)$payload['user_id'] !== (int)$id) {
            $this->sendJson(['error' => 'Forbidden'], 403);
            return;
        }
        $user = User::getById($this->pdo, (int)$id);
        if (!$user) {
            $this->sendJson(['error' => 'Пользователь не найден'], 404);
            return;
        }
        if ($user->delete($this->pdo)) {
            $this->sendJson(['message' => 'Пользователь удалён']);
        } else {
            $this->sendJson(['error' => 'Не удалось удалить пользователя'], 500);
        }
    }
}
