<?php

// app/Cells/RecentPostsCell.php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class RecentPostsCell extends Cell
{
    protected $posts;

    public function linkPost($post): string
    {
        return anchor('posts/' . $post->id, $post->title);
    }
}
