<?php

namespace App\Services;

use App\Models\PageModel;
use App\Models\ProjectModel;

class CatalogBuilderService
{
    protected $pageModel;
    protected $projectModel;

    public function __construct()
    {
        $this->pageModel = new PageModel();
        $this->projectModel = new ProjectModel();
    }

    public function renderPageHtml(int $pageId): string
    {
        $page = $this->pageModel->find($pageId);
        if (!$page) {
            return 'Page not found';
        }

        $project = $this->projectModel->find($page['project_id']);

        // Prepare content data
        // Merge structured content with some defaults
        $content = json_decode(json_encode($page['content']), true) ?? [];

        // Resolve image paths
        // If images in content are asset IDs, we need to resolve them to paths.
        // For simplicity, let's assume content JSON already has paths or we handle it here.
        // The AI output suggested "suggested_images": ["image1.jpg"].
        // We need to map these names to actual paths if they exist in assets.
        // For now, let's just pass content as is.

        $data = [
            'content' => $content,
            'page_number' => $page['page_number'],
            'project' => $project
        ];

        // Determine view based on layout_type
        $viewName = 'catalog/templates/' . ($page['layout_type'] ?? 'content');

        // Fallback if template doesn't exist
        if (!file_exists(APPPATH . 'Views/' . $viewName . '.php')) {
            $viewName = 'catalog/templates/content';
        }

        return view($viewName, $data);
    }
}
