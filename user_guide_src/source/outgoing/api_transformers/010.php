<?php

namespace App\Transformers;

use App\Transformers\CommentTransformer;
use App\Transformers\PostTransformer;
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
        // Only these relationships can be included
        return ['posts', 'comments'];
    }

    protected function includePosts(): array
    {
        $posts = model('PostModel')->where('user_id', $this->resource['id'])->findAll();

        return (new PostTransformer())->transformMany($posts);
    }

    protected function includeComments(): array
    {
        $comments = model('CommentModel')->where('user_id', $this->resource['id'])->findAll();

        return (new CommentTransformer())->transformMany($comments);
    }

    protected function includeOrders(): array
    {
        // This method exists but won't be callable from the API
        // because 'orders' is not in getAllowedIncludes()
        $orders = model('OrderModel')->where('user_id', $this->resource['id'])->findAll();

        return (new OrderTransformer())->transformMany($orders);
    }
}
