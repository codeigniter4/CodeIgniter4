<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected string $table        = 'book';
    protected array $allowedFields = ['title', 'author_id', 'year'];

    /**
     * Include author_id and author_name
     * in the results.
     */
    public function withAuthorInfo()
    {
        return $this->select('books.*, authors.id as author_id, authors.name as author_name')
            ->join('authors', 'books.author_id = authors.id');
    }
}
