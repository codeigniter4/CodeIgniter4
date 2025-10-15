<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'   => $resource['id'],
            'name' => $resource['name'],
            // Hide email if privacy mode is enabled
            'email' => $this->whenNot($resource['privacy_mode'], $resource['email'], '[hidden]'),
        ];
    }
}
