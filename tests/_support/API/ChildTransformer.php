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
 * Nested transformer used to verify that the root request's scope
 * (fields/includes) does not leak into related resources.
 */
class ChildTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'child_id' => $resource['id'] ?? null,
            'status'   => 'transformed',
        ];
    }
}
