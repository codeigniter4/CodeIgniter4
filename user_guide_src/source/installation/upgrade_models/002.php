<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    // Sets the table name.
    protected $table = 'news';

    // Sets the field names to allow to insert/update.
    protected $allowedFields = ['title', 'slug', 'text'];

    public function setNews($title, $slug, $text)
    {
        $data = [
            'title' => $title,
            'slug'  => $slug,
            'text'  => $text,
        ];

        // Uses Model's`insert()` method.
        return $this->insert($data);
    }
}
