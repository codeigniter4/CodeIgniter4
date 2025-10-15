<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class Customer extends BaseTransformer
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $resource): array
    {
        return [
            // Add your transformation logic here
        ];
    }
}
