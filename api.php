<?php
require_once __DIR__ . '/autoload.php';

use App\Controllers\TaskController;
use App\Controllers\ProjectController;
use App\Controllers\AssigneeController;

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
    // Task Routes
    if ($routeParts[0] === 'tasks') {
        if ($method === 'GET') {
            respond(200, $controller->index());
        }
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($routeParts[1])) {
                if ($routeParts[1] === 'move') respond(200, $controller->move($data));
                if ($routeParts[1] === 'delete') respond(200, $controller->destroy($data));
            } else {
                respond(201, $controller->store($data));
            }
        }
        if ($method === 'PUT' && isset($routeParts[1])) {
            $data = json_decode(file_get_contents('php://input'), true);
            respond(200, $controller->update($routeParts[1], $data));
        }
    }

    // Project Routes
    if ($routeParts[0] === 'projects') {
        $projController = new ProjectController();
        if ($method === 'GET') respond(200, $projController->index());
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            respond(201, $projController->store($data));
        }
        if ($method === 'DELETE' || ($method === 'POST' && isset($routeParts[1]) && $routeParts[1] === 'delete')) {
            $data = json_decode(file_get_contents('php://input'), true);
            respond(200, $projController->destroy($data));
        }
    }

    // Assignee Routes
    if ($routeParts[0] === 'assignees') {
        $assigneeController = new AssigneeController();
        if ($method === 'GET') respond(200, $assigneeController->index());
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            respond(201, $assigneeController->store($data));
        }
        if ($method === 'DELETE' || ($method === 'POST' && isset($routeParts[1]) && $routeParts[1] === 'delete')) {
            $data = json_decode(file_get_contents('php://input'), true);
            respond(200, $assigneeController->destroy($data));
        }
    }

    respond(404, ['error' => 'Route not found: ' . $route]);
} catch (\Exception $e) {
    respond(500, ['error' => $e->getMessage()]);
}