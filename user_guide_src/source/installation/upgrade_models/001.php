<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    // Sets the table name.
    protected $table = 'news';

    public function setNews($title, $slug, $text)
    {
        $data = [
            'title' => $title,
            'slug'  => $slug,
            'text'  => $text,
        ];

        // Gets the Query Builder for the table, and calls `insert()`.
        return $this->builder()->insert($data);
    }
}
