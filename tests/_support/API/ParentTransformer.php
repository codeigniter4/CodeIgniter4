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
 * Parent transformer for testing transformRelated() and Global State Leakage.
 */
class ParentTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'parent_id' => $resource['id'] ?? null,
        ];
    }

    /**
     * Test include that triggers a child transformer.
     * If Global State leaks (?include=child), the child will try to find
     * includeChild() on itself and throw an ApiException.
     */
    protected function includeChild(): array
    {
        $childData = ['id' => 99];

        return $this->transformRelated(ChildTransformer::class, $childData);
    }

    /**
     * Test include that returns a collection of items to verify smart routing.
     */
    protected function includeChildrenCollection(): array
    {
        $collectionData = [
            ['id' => 77],
            ['id' => 88],
        ];

        return $this->transformRelated(ChildTransformer::class, $collectionData);
    }
}
