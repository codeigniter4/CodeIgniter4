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

/**
 * Invalid transformer for testing error handling
 * Does not implement TransformerInterface
 */
class InvalidTransformer
{
    public function toArray(mixed $resource): array
    {
        return [];
    }
}
