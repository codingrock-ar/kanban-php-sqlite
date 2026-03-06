<?php
require 'db.php';

header('Content-Type: application/json');
$pdo = $pdo ?? null;

// Very simple API: GET /tasks (list all), POST /tasks (create), POST /tasks/{id}/move (change status)
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

// Segments extraction logic
$route = $_GET['route'] ?? '';
$routeParts = explode('/', trim($route, '/'));

function respond($code, $payload) {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if (!$pdo) {
    respond(500, ['error' => 'Database connection failed']);
}

// GET /tasks
if ($method === 'GET' && $routeParts[0] === 'tasks') {
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respond(200, ['tasks' => $rows]);
}

// POST /tasks (Create)
if ($method === 'POST' && $routeParts[0] === 'tasks' && count($routeParts) === 1) {
    $data = json_decode(file_get_contents('php://input'), true);
    $title = $data['title'] ?? '';
    
    if ($title === '') {
        respond(400, ['error' => 'title required']);
    }
    
    $desc = $data['description'] ?? '';
    $status = $data['status'] ?? 'Backlog';
    $proj = $data['project'] ?? '';
    $prio = $data['priority'] ?? 'Low';
    $now = date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, status, project, priority, due_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $title, 
        $desc, 
        $status, 
        $proj, 
        $prio, 
        $data['due_date'] ?? null, 
        $now, 
        $now
    ]);
    
    respond(201, ['id' => $pdo->lastInsertId(), 'title' => $title]);
}

// POST /tasks/move
if ($method === 'POST' && $routeParts[0] === 'tasks' && isset($routeParts[1]) && $routeParts[1] === 'move') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $newStatus = $data['status'];
    
    $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = ? WHERE id = ?");
    $stmt->execute([$newStatus, date('Y-m-d H:i:s'), $id]);
    
    respond(200, ['status' => 'success', 'moved' => true]);
}

// POST /tasks/delete
if ($method === 'POST' && $routeParts[0] === 'tasks' && isset($routeParts[1]) && $routeParts[1] === 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    
    respond(200, ['status' => 'success', 'deleted' => true]);
}

// PUT /tasks/{id} (Update title)
if ($method === 'PUT' && $routeParts[0] === 'tasks' && isset($routeParts[1]) && is_numeric($routeParts[1])) {
    $id = $routeParts[1];
    $data = json_decode(file_get_contents('php://input'), true);
    $title = $data['title'] ?? '';
    
    if ($title) {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$title, date('Y-m-d H:i:s'), $id]);
        respond(200, ['status' => 'success']);
    }
}

respond(404, ['error' => 'Route not found: ' . $route]);
?>