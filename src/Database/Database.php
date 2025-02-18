<?php
declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Database {
    private PDO $pdo;

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

    public function getPdo(): PDO {
        return $this->pdo;
    }
}
