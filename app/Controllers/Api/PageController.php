<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\CatalogBuilderService;
use App\Models\PageModel;
use App\Models\ProjectModel;
use CodeIgniter\API\ResponseTrait;

class PageController extends BaseController
{
    use ResponseTrait;

    protected $builderService;
    protected $pageModel;

    public function __construct()
    {
        $this->builderService = new CatalogBuilderService();
        $this->pageModel = new PageModel();
    }

    public function getPages($projectId)
    {
        if (!$this->checkAccess($projectId)) {
            return $this->failForbidden('Access denied');
        }

        $pages = $this->pageModel->where('project_id', $projectId)
                                 ->orderBy('page_number', 'ASC')
                                 ->findAll();

        return $this->respond($pages);
    }

    public function getPreview($pageId)
    {
        $page = $this->pageModel->find($pageId);
        if (!$page) {
            return $this->failNotFound('Page not found');
        }

        if (!$this->checkAccess($page['project_id'])) {
            return $this->failForbidden('Access denied');
        }

        $html = $this->builderService->renderPageHtml($pageId);

        return $this->response->setBody($html)->setHeader('Content-Type', 'text/html');
    }

    protected function checkAccess($projectId)
    {
        $model = new ProjectModel();
        $project = $model->find($projectId);

        if (!$project) return false;

        $userId = session()->get('id');
        if ($project['user_id'] != $userId && session()->get('role') !== 'admin') {
            return false;
        }
        return true;
    }
}
