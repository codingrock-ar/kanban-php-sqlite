<?php
require_once __DIR__ . '/autoload.php';
use App\Models\Task;

header('Content-Type: application/json');

try {
    $taskModel = new Task();
    $tasks = $taskModel->all();
    echo json_encode(['status' => 'ok', 'count' => count($tasks), 'tasks' => $tasks]);
} catch (\Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
