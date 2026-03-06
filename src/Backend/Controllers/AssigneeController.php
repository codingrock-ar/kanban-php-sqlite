<?php
namespace App\Controllers;

use App\Models\Assignee;

class AssigneeController {
    private $assigneeModel;

    public function __construct() {
        $this->assigneeModel = new Assignee();
    }

    public function index() {
        return ['assignees' => $this->assigneeModel->all()];
    }

    public function store($data) {
        $id = $this->assigneeModel->create($data);
        return ['id' => $id, 'status' => 'success'];
    }

    public function destroy($data) {
        $this->assigneeModel->delete($data['id']);
        return ['status' => 'success'];
    }
}
