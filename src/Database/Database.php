<?php
declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Database {
    /**
     * @var PDO Экземпляр PDO для работы с базой данных
     */
    private PDO $pdo;

    /**
     * Конструктор класса Database. Создаёт подключение к SQLite-базе данных.
     *
     * @param string $dbFile Путь к файлу базы данных SQLite
     */
    public function __construct(string $dbFile = __DIR__ . '/../../database.sqlite') {
        try {
            $this->pdo = new PDO("sqlite:" . $dbFile);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initialize();
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Ошибка подключения к базе данных']);
            exit;
        }
    }

    /**
     * Инициализирует базу данных, создавая таблицу пользователей, если она не существует.
     *
     * @return void
     */
    private function initialize(): void {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE,
                password TEXT,
                email TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        );
    }

    /**
     * Возвращает экземпляр PDO для выполнения запросов к базе данных.
     *
     * @return PDO Экземпляр PDO
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }
}
