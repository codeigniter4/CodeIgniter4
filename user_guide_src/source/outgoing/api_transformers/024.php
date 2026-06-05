<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'    => $resource['id'],
            'name'  => $resource['name'],
            'email' => $resource['email'],
        ];
    }

    protected function includePosts(): array
    {
        // Use $this->resource to access the current resource being transformed
        $posts = model('PostModel')->where('user_id', $this->resource['id'])->findAll();

        return $this->transformRelated(PostTransformer::class, $posts);
    }
}
