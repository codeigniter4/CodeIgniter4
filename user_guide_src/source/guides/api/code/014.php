<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    public function withAuthorInfo()
    {
        return $this
            ->select('book.*, author.name as author_name')
            ->join('author', 'book.author_id = author.id');
    }
}
