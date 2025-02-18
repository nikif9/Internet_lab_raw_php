<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database\Database;
use App\Models\User;
use App\Helpers\AuthHelper;
use PDO;

class UserController {
    /**
     * @var PDO Экземпляр PDO для взаимодействия с базой данных
     */
    private PDO $pdo;

    /**
     * Конструктор UserController.
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
     * Создаёт нового пользователя (регистрация).
     * Обрабатывает POST-запрос на /users.
     *
     * @return void
     */
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

    /**
     * Получает информацию о пользователе по его ID.
     * Обрабатывает GET-запрос на /users/{id}.
     *
     * @param string $id ID пользователя
     * @return void
     */
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

    /**
     * Обновляет информацию о пользователе.
     * Обрабатывает PUT-запрос на /users/{id}.
     *
     * @param string $id ID пользователя
     * @return void
     */
    public function updateUser(string $id): void {
        if (!ctype_digit($id)) {
            $this->sendJson(['error' => 'Некорректный ID'], 400);
            return;
        }
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

    /**
     * Удаляет пользователя.
     * Обрабатывает DELETE-запрос на /users/{id}.
     *
     * @param string $id ID пользователя
     * @return void
     */
    public function deleteUser(string $id): void {
        if (!ctype_digit($id)) {
            $this->sendJson(['error' => 'Некорректный ID'], 400);
            return;
        }
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
