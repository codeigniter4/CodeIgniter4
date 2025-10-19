<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'           => $resource['id'],
            'username'     => $resource['name'],  // Renaming the field
            'email'        => $resource['email'],
            'member_since' => date('Y-m-d', strtotime($resource['created_at'])), // Formatting
        ];
    }
}
