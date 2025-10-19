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
}

// In your controller
$user        = model('UserModel')->find(1);
$transformer = new UserTransformer();

return $this->respond($transformer->transform($user));
