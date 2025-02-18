<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class User {
    public ?int $id;
    public string $username;
    public string $password; // хранится в виде хэша
    public string $email;
    public ?string $created_at;

    public function __construct(?int $id, string $username, string $password, string $email, ?string $created_at = null) {
        $this->id         = $id;
        $this->username   = $username;
        $this->password   = $password;
        $this->email      = $email;
        $this->created_at = $created_at;
    }

    /**
     * Создаёт нового пользователя и возвращает объект User или null при неудаче.
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
     */
    public static function getById(PDO $pdo, int $id): ?User {
        $stmt = $pdo->prepare("SELECT id, username, password, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User((int)$data['id'], $data['username'], $data['password'], $data['email'], $data['created_at']);
        }
        return null;
    }

    /**
     * Обновляет данные пользователя.
     */
    public function update(PDO $pdo, array $fields): bool {
        $updates = [];
        $params  = [];

        if (isset($fields['username'])) {
            $updates[]        = "username = ?";
            $params[]         = $fields['username'];
            $this->username   = $fields['username'];
        }
        if (isset($fields['email'])) {
            $updates[]      = "email = ?";
            $params[]       = $fields['email'];
            $this->email    = $fields['email'];
        }
        if (isset($fields['password'])) {
            $updates[]          = "password = ?";
            $newPasswordHash    = password_hash($fields['password'], PASSWORD_BCRYPT);
            $params[]           = $newPasswordHash;
            $this->password     = $newPasswordHash;
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
     */
    public function delete(PDO $pdo): bool {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Аутентифицирует пользователя и возвращает объект User или null.
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
     */
    public function toArray(): array {
        return [
            'id'         => $this->id,
            'username'   => $this->username,
            'email'      => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
