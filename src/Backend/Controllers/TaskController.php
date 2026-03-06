<?php
namespace App\Controllers;

use App\Models\Task;

class TaskController {
    private $taskModel;

    public function __construct() {
        $this->taskModel = new Task();
    }

    public function index() {
        return ['tasks' => $this->taskModel->all()];
    }

    public function store($data) {
        $id = $this->taskModel->create($data);
        return ['id' => $id, 'status' => 'success'];
    }

    public function move($data) {
        $this->taskModel->updateStatus($data['id'], $data['status']);
        return ['status' => 'success'];
    }

    public function update($id, $data) {
        $this->taskModel->updateTitle($id, $data['title']);
        return ['status' => 'success'];
    }

    public function destroy($data) {
        $this->taskModel->delete($data['id']);
        return ['status' => 'success'];
    }
}
