<?php

// In a Cell.

namespace App\Cells;

class Blog
{
    // ...

    public function recentPosts(string $category, int $limit): string
    {
        $posts = $this->blogModel->where('category', $category)
            ->orderBy('published_on', 'desc')
            ->limit($limit)
            ->get();

        return view('recentPosts', ['posts' => $posts]);
    }
}
