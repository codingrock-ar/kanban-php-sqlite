<?php
// Data access for a simple Kanban using SQLite
$dbFile = __DIR__ . '/database.sqlite';
$dsn = 'sqlite:' . $dbFile;
try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'DB connection error';
    exit;
}

// Migrate: create tasks table if not exists
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        status TEXT NOT NULL DEFAULT 'Backlog',
        project TEXT,
        priority TEXT,
        due_date TEXT,
        created_at TEXT,
        updated_at TEXT
    )"
);

?>