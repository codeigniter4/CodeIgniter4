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
            // Include email only if user is verified
            'email' => $this->when($resource['is_verified'], $resource['email']),
            // Include role or default to 'user'
            'role' => $this->when(($resource['role'] ?? null) !== null, $resource['role'], 'user'),
        ];
    }
}
