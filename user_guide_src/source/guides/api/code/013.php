<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table         = 'books';
    protected $allowedFields = ['title', 'author_id', 'year'];

    /**
     * Include author_id and author_name
     * in the results.
     */
    public function withAuthorInfo()
    {
        return $this
            ->select('book.*, author.id as author_id, author.name as author_name')
            ->join('author', 'book.author_id = author.id');
    }
}
