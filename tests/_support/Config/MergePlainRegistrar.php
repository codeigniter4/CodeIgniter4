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

/**
 * Plain-array registrar (no directives) used to assert the legacy shallow
 * merge behavior is unchanged — nested siblings are dropped.
 */
class MergePlainRegistrar
{
    public static function MergeRegistrarConfig(): array
    {
        return [
            'arrayNested' => [
                'key2' => ['val4' => 'subVal4'],
            ],
        ];
    }
}
