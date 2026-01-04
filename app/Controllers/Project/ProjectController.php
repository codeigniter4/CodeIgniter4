<?php

namespace App\Controllers\Project;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\AssetModel;

class ProjectController extends BaseController
{
    protected $projectModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
    }

    public function index()
    {
        $userId = session()->get('id');
        $projects = $this->projectModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        return view('project/index', ['projects' => $projects]);
    }

    public function create()
    {
        return view('project/create');
    }

    public function store()
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[200]',
        ];

        if (!$this->validate($rules)) {
            return view('project/create', [
                'validation' => $this->validator,
            ]);
        }

        $userId = session()->get('id');

        $data = [
            'user_id' => $userId,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status' => 'draft',
            'total_pages' => 0,
            'total_price' => 0,
        ];

        $projectId = $this->projectModel->insert($data);

        // Redirect to workspace (or upload page, but flow says workspace/upload)
        // Flow 2: Create -> ... -> Redirect to Workspace
        // For now, let's redirect to index with success message
        return redirect()->to('/dashboard/projects')->with('success', 'پروژه با موفقیت ایجاد شد.');
    }

    public function show($id)
    {
        // Placeholder for workspace
        $project = $this->projectModel->find($id);
        if (!$project) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Check ownership
        if ($project['user_id'] != session()->get('id') && session()->get('role') !== 'admin') {
             return redirect()->to('/dashboard')->with('error', 'دسترسی غیرمجاز');
        }

        // Fetch Assets
        $assetModel = new AssetModel();
        $assets = $assetModel->where('project_id', $id)->orderBy('created_at', 'DESC')->findAll();

        return view('project/workspace', [
            'project' => $project,
            'assets' => $assets
        ]);
    }
}
