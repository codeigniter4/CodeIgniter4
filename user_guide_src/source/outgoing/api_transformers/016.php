<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class StaticDataTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'version' => '1.0',
            'status'  => 'active',
            'message' => 'API is running',
        ];
    }
}

// Usage
$transformer = new StaticDataTransformer();
$result      = $transformer->transform(null); // No resource passed
