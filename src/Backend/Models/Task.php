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
        $sql = "SELECT t.*, p.name as project_name, p.color as project_color, a.name as assignee_name 
                FROM tasks t 
                LEFT JOIN projects p ON t.project_id = p.id 
                LEFT JOIN assignees a ON t.assignee_id = a.id 
                ORDER BY t.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO tasks (title, description, status, project_id, assignee_id, priority, due_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['status'] ?? 'Backlog',
            $data['project_id'] ?? null,
            $data['assignee_id'] ?? null,
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
