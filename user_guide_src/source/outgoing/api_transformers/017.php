<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class ProductTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'          => $resource['id'],
            'name'        => $resource['name'],
            'price'       => $resource['price'],
            'in_stock'    => $resource['stock_quantity'] > 0,
            'description' => $resource['description'],
        ];
    }
}
