<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class AuthorTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'   => $resource['id'],
            'name' => $resource['name'],
        ];
    }
}
