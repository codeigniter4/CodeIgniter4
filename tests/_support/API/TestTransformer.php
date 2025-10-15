<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\API;

use CodeIgniter\API\BaseTransformer;

/**
 * Test transformer for testing paginate() with transformers
 */
class TestTransformer extends BaseTransformer
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $resource): array
    {
        return [
            'id'          => $resource['id'] ?? null,
            'name'        => $resource['name'] ?? null,
            'transformed' => true,
            'name_upper'  => isset($resource['name']) ? strtoupper($resource['name']) : null,
        ];
    }
}
