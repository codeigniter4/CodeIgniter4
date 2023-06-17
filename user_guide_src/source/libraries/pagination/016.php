<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table = 'news';

    // ...

    public function getPagination(?int $perPage = null): array
    {
        $this->builder()
            ->select('news.*, category.name')
            ->join('category', 'news.category_id = category.id');

        return [
            'news'  => $this->paginate($perPage),
            'pager' => $this->pager,
        ];
    }
}
