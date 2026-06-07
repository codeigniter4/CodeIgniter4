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
 * Root transformer used to verify that nested transformers do not inherit
 * the root request's `fields`/`include` query state.
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
     * Includes a single related child resource.
     *
     * @return array<string, mixed>
     */
    protected function includeChildren(): array
    {
        return (new ChildTransformer())->transform(['id' => 99]);
    }

    /**
     * Includes a collection of related child resources.
     *
     * @return list<array<string, mixed>>
     */
    protected function includeChildrenCollection(): array
    {
        return (new ChildTransformer())->transformMany([
            ['id' => 77],
            ['id' => 88],
        ]);
    }

    /**
     * Includes a single child while explicitly forwarding the request, opting
     * the child into the request-derived scope even though it is nested.
     *
     * @return array<string, mixed>
     */
    protected function includeExplicitChild(): array
    {
        $childRequest = clone request();
        $childRequest->setGlobal('get', ['fields' => 'child_id']);

        return (new ChildTransformer($childRequest))->transform(['id' => 99]);
    }
}
