<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class PostTransformer extends BaseTransformer
{
    // Enables ?fields[posts]=... for this resource type.
    protected ?string $resourceType = 'posts';

    public function toArray(mixed $resource): array
    {
        return [
            'id'      => $resource['id'],
            'title'   => $resource['title'],
            'slug'    => $resource['slug'],
            'body'    => $resource['body'],
            'user_id' => $resource['user_id'],
        ];
    }
}
