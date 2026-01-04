<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AssetModel;
use App\Models\ProjectModel;
use App\Services\FileParserService;
use CodeIgniter\API\ResponseTrait;

class UploadController extends BaseController
{
    use ResponseTrait;

    protected $fileParser;

    public function __construct()
    {
        $this->fileParser = new FileParserService();
    }

    public function upload()
    {
        $rules = [
            'file' => 'uploaded[file]|max_size[file,10240]|ext_in[file,png,jpg,jpeg,webp,docx]',
            'project_id' => 'required|is_natural_no_zero'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $projectId = $this->request->getPost('project_id');
        $projectModel = new ProjectModel();

        // Check ownership
        $project = $projectModel->find($projectId);
        if (!$project) {
            return $this->failNotFound('Project not found');
        }
        if ($project['user_id'] != session()->get('id') && session()->get('role') !== 'admin') {
            return $this->failForbidden('Access denied');
        }

        $file = $this->request->getFile('file');
        $originalName = $file->getClientName();
        $ext = $file->getExtension();
        $mime = $file->getMimeType();
        $type = ($ext === 'docx') ? 'document' : 'image';

        // Move file
        // Path: public/uploads/projects/{project_id}/
        $uploadPath = FCPATH . 'uploads/projects/' . $projectId;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        $filePathAbsolute = $uploadPath . '/' . $newName;
        $relativePath = 'uploads/projects/' . $projectId . '/' . $newName;
        $fileSize = filesize($filePathAbsolute);

        // Analyze and save metadata
        $metadata = null;
        if ($type === 'image') {
            $analysis = $this->fileParser->analyzeImage($filePathAbsolute);
            $metadata = json_encode($analysis);
        } elseif ($type === 'document') {
             $content = $this->fileParser->parseDocx($filePathAbsolute);
             // Update project with extracted content
             // Append if exists? For now, we overwrite or append. Let's append with new lines.
             $currentContent = $project['extracted_content'] ?? '';
             $newContent = $currentContent . "\n\n" . $content;
             $projectModel->update($projectId, ['extracted_content' => trim($newContent)]);
        }

        // Save to Assets table
        $assetModel = new AssetModel();
        $assetData = [
            'project_id' => $projectId,
            'type' => $type,
            'original_name' => $originalName,
            'file_path' => $relativePath,
            'mime_type' => $mime,
            'file_size' => $fileSize,
            'metadata' => $metadata,
            'is_used' => false
        ];

        $assetId = $assetModel->insert($assetData);

        return $this->respondCreated([
            'id' => $assetId,
            'message' => 'File uploaded successfully',
            'file_path' => $relativePath,
            'type' => $type
        ]);
    }
}
