<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'         => $resource['id'],
            'name'       => $resource['name'],
            'email'      => $resource['email'],
            'created_at' => $resource['created_at'],
        ];
    }

    protected function getAllowedFields(): ?array
    {
        // Clients can only request id, name, and created_at
        // Attempting to request 'email' will throw an ApiException
        return ['id', 'name', 'created_at'];
    }
}
