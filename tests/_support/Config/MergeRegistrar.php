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

namespace Tests\Support\Config;

use CodeIgniter\Config\Merge;

/**
 * Registrar exercising the Merge directives against MergeRegistrarConfig.
 */
class MergeRegistrar
{
    public static function MergeRegistrarConfig(): array
    {
        return [
            // Example A — deep-merge a nested subtree, preserving siblings.
            'arrayNested' => Merge::byKey([
                'key2' => ['val4' => 'subVal4'],
            ]),
            // Example B — append to a list nested under a string key.
            'matrix' => Merge::byKey([
                'superadmin' => ['shippinglabel-logos.*'],
            ]),
            // Example C — nested directives inside byKey().
            'globals' => Merge::byKey([
                'before' => Merge::append(['blogFilter']),
                'after'  => Merge::replace([]),
            ]),
            // Scalar replace.
            'handler' => Merge::replace('redis'),
            // Property-root append.
            'list' => Merge::append(['c']),
        ];
    }
}
