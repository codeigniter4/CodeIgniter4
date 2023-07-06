<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class RecentPostsCell extends Cell
{
    protected $posts;

    public function mount()
    {
        $this->posts = model('PostModel')->getRecent();
    }
}
