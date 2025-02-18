<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class User {
    /**
     * @var int|null ID пользователя
     */
    public ?int $id;

    /**
     * @var string Имя пользователя
     */
    public string $username;

    /**
     * @var string Хэш пароля пользователя
     */
    public string $password;

    /**
     * @var string Email пользователя
     */
    public string $email;

    /**
     * @var string|null Дата создания пользователя
     */
    public ?string $created_at;

    /**
     * Конструктор класса User.
     *
     * @param int|null $id ID пользователя
     * @param string $username Имя пользователя
     * @param string $password Хэш пароля
     * @param string $email Email пользователя
     * @param string|null $created_at Дата создания
     */
    public function __construct(?int $id, string $username, string $password, string $email, ?string $created_at = null) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->created_at = $created_at;
    }

    /**
     * Создаёт нового пользователя и возвращает объект User или null при неудаче.
     *
     * @param PDO $pdo Экземпляр PDO
     * @param string $username Имя пользователя
     * @param string $password Пароль пользователя
     * @param string $email Email пользователя
     * @return User|null Возвращает объект User или null при ошибке
     */
    public static function create(PDO $pdo, string $username, string $password, string $email): ?User {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $passwordHash, $email])) {
            $id = (int)$pdo->lastInsertId();
            return new User($id, $username, $passwordHash, $email, date('Y-m-d H:i:s'));
        }
        return null;
    }

    /**
     * Возвращает объект User по его ID или null, если пользователь не найден.
     *
     * @param PDO $pdo Экземпляр PDO
     * @param int $id ID пользователя
     * @return User|null Возвращает объект User или null, если пользователь не найден
     */
    public static function getById(PDO $pdo, int $id): ?User {
        $stmt = $pdo->prepare("SELECT id, username, password, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new User((int)$data['id'], $data['username'], $data['password'], $data['email'], $data['created_at']) : null;
    }

    /**
     * Обновляет данные пользователя.
     *
     * @param PDO $pdo Экземпляр PDO
     * @param array $fields Массив обновляемых полей
     * @return bool Возвращает true, если обновление успешно, иначе false
     */
    public function update(PDO $pdo, array $fields): bool {
        $updates = [];
        $params = [];

        if (isset($fields['username'])) {
            $updates[] = "username = ?";
            $params[] = $fields['username'];
            $this->username = $fields['username'];
        }
        if (isset($fields['email'])) {
            $updates[] = "email = ?";
            $params[] = $fields['email'];
            $this->email = $fields['email'];
        }
        if (isset($fields['password'])) {
            $updates[] = "password = ?";
            $newPasswordHash = password_hash($fields['password'], PASSWORD_BCRYPT);
            $params[] = $newPasswordHash;
            $this->password = $newPasswordHash;
        }
        if (empty($updates)) {
            return false;
        }
        $params[] = $this->id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Удаляет пользователя.
     *
     * @param PDO $pdo Экземпляр PDO
     * @return bool Возвращает true, если удаление успешно, иначе false
     */
    public function delete(PDO $pdo): bool {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Аутентифицирует пользователя и возвращает объект User или null.
     *
     * @param PDO $pdo Экземпляр PDO
     * @param string $username Имя пользователя
     * @param string $password Пароль пользователя
     * @return User|null Возвращает объект User при успешной аутентификации, иначе null
     */
    public static function authenticate(PDO $pdo, string $username, string $password): ?User {
        $stmt = $pdo->prepare("SELECT id, username, password, email, created_at FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data && password_verify($password, $data['password'])) {
            return new User((int)$data['id'], $data['username'], $data['password'], $data['email'], $data['created_at']);
        }
        return null;
    }

    /**
     * Возвращает представление пользователя в виде массива (без пароля).
     *
     * @return array Возвращает массив с данными пользователя
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
