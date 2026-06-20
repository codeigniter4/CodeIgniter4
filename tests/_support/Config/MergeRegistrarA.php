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
 * First of two registrars touching the same properties, used to assert that
 * accumulation/replacement follows registrar (discovery) order.
 */
class MergeRegistrarA
{
    public static function MergeRegistrarConfig(): array
    {
        return [
            'list'    => Merge::append(['x']),
            'handler' => Merge::replace('redis'),
        ];
    }
}
