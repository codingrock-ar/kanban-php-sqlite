<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dbFile = __DIR__ . '/../../database.sqlite';
        $dsn = 'sqlite:' . $dbFile;
        try {
            $this->pdo = new PDO($dsn);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->migrate();
        } catch (PDOException $e) {
            http_response_code(500);
            echo 'Database error: ' . $e->getMessage();
            exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    private function migrate() {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                status TEXT NOT NULL DEFAULT 'Backlog',
                project TEXT,
                priority TEXT DEFAULT 'Low',
                due_date TEXT,
                created_at TEXT,
                updated_at TEXT
            )"
        );
    }
}
