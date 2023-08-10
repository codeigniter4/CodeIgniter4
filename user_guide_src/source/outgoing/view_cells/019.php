<?php

// app/Cells/RecentPostsCell.php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class RecentPostsCell extends Cell
{
    protected $posts;

    public function mount(?int $categoryId)
    {
        $this->posts = model('PostModel')
            ->when(
                $categoryId,
                static fn ($query, $categoryId) => $query->where('category_id', $categoryId)
            )
            ->orderBy('created_at', 'DESC')
            ->findAll(10);
    }
}
