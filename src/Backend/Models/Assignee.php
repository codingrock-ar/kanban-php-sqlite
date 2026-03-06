<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Assignee {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all() {
        $stmt = $this->db->query("SELECT * FROM assignees ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO assignees (name, avatar, created_at) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['avatar'] ?? null,
            date('Y-m-d H:i:s')
        ]);
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM assignees WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
