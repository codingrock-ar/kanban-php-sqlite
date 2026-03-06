<?php
namespace App\Controllers;

use App\Models\Project;

class ProjectController {
    private $projectModel;

    public function __construct() {
        $this->projectModel = new Project();
    }

    public function index() {
        return ['projects' => $this->projectModel->all()];
    }

    public function store($data) {
        $id = $this->projectModel->create($data);
        return ['id' => $id, 'status' => 'success'];
    }

    public function destroy($data) {
        $this->projectModel->delete($data['id']);
        return ['status' => 'success'];
    }
}
