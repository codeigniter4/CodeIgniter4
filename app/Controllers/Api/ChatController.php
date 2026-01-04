<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\AIService;
use CodeIgniter\API\ResponseTrait;

class ChatController extends BaseController
{
    use ResponseTrait;

    protected $aiService;

    public function __construct()
    {
        $this->aiService = new AIService();
    }

    public function sendMessage()
    {
        $rules = [
            'project_id' => 'required|is_natural_no_zero',
            'message' => 'required|string'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $projectId = $this->request->getPost('project_id');
        $message = $this->request->getPost('message');

        // Access check
        if (!$this->checkAccess($projectId)) {
            return $this->failForbidden('Access denied');
        }

        try {
            $response = $this->aiService->chat($projectId, $message);
            return $this->respondCreated(['message' => $response]);
        } catch (\Exception $e) {
            log_message('error', 'Chat Error: ' . $e->getMessage());
            return $this->failServerError('AI Service Error');
        }
    }

    public function analyze()
    {
        $rules = [
            'project_id' => 'required|is_natural_no_zero'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $projectId = $this->request->getPost('project_id');

        // Access check
        if (!$this->checkAccess($projectId)) {
            return $this->failForbidden('Access denied');
        }

        try {
            $result = $this->aiService->analyzeCatalogContent($projectId);
            if (isset($result['success']) && $result['success']) {
                 return $this->respond($result);
            } else {
                 return $this->failServerError($result['error'] ?? 'Analysis failed');
            }
        } catch (\Exception $e) {
            log_message('error', 'Analysis Error: ' . $e->getMessage());
            return $this->failServerError('AI Service Error: ' . $e->getMessage());
        }
    }

    public function history($projectId)
    {
        if (!$this->checkAccess($projectId)) {
            return $this->failForbidden('Access denied');
        }

        $model = new \App\Models\ConversationModel();
        $history = $model->where('project_id', $projectId)->orderBy('created_at', 'ASC')->findAll();

        return $this->respond($history);
    }

    protected function checkAccess($projectId)
    {
        $model = new \App\Models\ProjectModel();
        $project = $model->find($projectId);

        if (!$project) return false;

        $userId = session()->get('id');
        if ($project['user_id'] != $userId && session()->get('role') !== 'admin') {
            return false;
        }
        return true;
    }
}
