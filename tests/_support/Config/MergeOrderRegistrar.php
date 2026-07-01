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
 * Registrar exercising the ordering directives (prepend/before/after) through
 * the real registrar flow, including nesting inside byKey() for a Filters-style
 * globals list.
 */
class MergeOrderRegistrar
{
    public static function MergeRegistrarConfig(): array
    {
        return [
            // Order a filter relative to an existing one in a nested list.
            'globals' => Merge::byKey([
                'before' => Merge::after('csrf', ['auth']),
                'after'  => Merge::prepend(['honeypot']),
            ]),
            // Property-root list ordering.
            'list' => Merge::before('a', ['z']),
        ];
    }
}
