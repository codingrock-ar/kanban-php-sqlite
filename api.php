<?php
require_once __DIR__ . '/autoload.php';

use App\Controllers\TaskController;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';
$routeParts = explode('/', trim($route, '/'));

$controller = new TaskController();

function respond($code, $payload) {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

try {
    if ($method === 'GET' && $routeParts[0] === 'tasks') {
        respond(200, $controller->index());
    }

    if ($method === 'POST' && $routeParts[0] === 'tasks') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($routeParts[1])) {
            if ($routeParts[1] === 'move') {
                respond(200, $controller->move($data));
            }
            if ($routeParts[1] === 'delete') {
                respond(200, $controller->destroy($data));
            }
        } else {
            respond(201, $controller->store($data));
        }
    }

    if ($method === 'PUT' && $routeParts[0] === 'tasks' && isset($routeParts[1])) {
        $data = json_decode(file_get_contents('php://input'), true);
        respond(200, $controller->update($routeParts[1], $data));
    }

    respond(404, ['error' => 'Route not found: ' . $route]);
} catch (\Exception $e) {
    respond(500, ['error' => $e->getMessage()]);
}