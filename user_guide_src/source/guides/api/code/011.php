<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class BookTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'    => $resource['id'],
            'title' => $resource['title'],
            'year'  => $resource['year'],
        ];
    }

    protected function includeAuthor(array $book): ?array
    {
        if (empty($book['author_id']) || empty($book['author_name'])) {
            return null;
        }

        return [
            'id'   => $book['author_id'],
            'name' => $book['author_name'],
        ];
    }
}
