<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Task {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all() {
        $stmt = $this->db->query("SELECT * FROM tasks ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO tasks (title, description, status, project, priority, due_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['status'] ?? 'Backlog',
            $data['project'] ?? '',
            $data['priority'] ?? 'Low',
            $data['due_date'] ?? null,
            $now,
            $now
        ]);
        return $this->db->lastInsertId();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE tasks SET status = ?, updated_at = ? WHERE id = ?");
        return $stmt->execute([$status, date('Y-m-d H:i:s'), $id]);
    }

    public function updateTitle($id, $title) {
        $stmt = $this->db->prepare("UPDATE tasks SET title = ?, updated_at = ? WHERE id = ?");
        return $stmt->execute([$title, date('Y-m-d H:i:s'), $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
