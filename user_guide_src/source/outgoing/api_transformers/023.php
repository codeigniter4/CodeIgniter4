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

    protected function getAllowedIncludes(): ?array
    {
        // Only 'posts' can be included via ?include=posts
        // Attempting to include 'orders' will throw an ApiException
        return ['posts'];
    }

    protected function includePosts(): array
    {
        $posts = model('PostModel')->where('user_id', $this->resource['id'])->findAll();

        return (new PostTransformer())->transformMany($posts);
    }

    protected function includeOrders(): array
    {
        // This method exists but cannot be called via the API
        $orders = model('OrderModel')->where('user_id', $this->resource['id'])->findAll();

        return (new OrderTransformer())->transformMany($orders);
    }
}
