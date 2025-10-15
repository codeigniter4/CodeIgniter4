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
            'email' => $this->whenNot($resource['email_hidden'], $resource['email'], '[hidden]'),
        ];
    }
}
