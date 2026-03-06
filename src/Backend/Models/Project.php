<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Project {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all() {
        $stmt = $this->db->query("SELECT * FROM projects ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO projects (name, color, created_at) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['color'] ?? '#3b82f6',
            date('Y-m-d H:i:s')
        ]);
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
