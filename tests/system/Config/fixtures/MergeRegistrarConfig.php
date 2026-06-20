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

use CodeIgniter\Config\BaseConfig;

class MergeRegistrarConfig extends BaseConfig
{
    /**
     * @var array<string, mixed>
     */
    public array $arrayNested = [
        'key1' => 'val1',
        'key2' => ['val2' => 'subVal2', 'val3' => 'subVal3'],
    ];

    /**
     * @var array<string, list<string>>
     */
    public array $matrix = [
        'superadmin' => ['admin.access'],
    ];

    /**
     * @var array<string, list<string>>
     */
    public array $globals = [
        'before' => ['csrf'],
        'after'  => ['toolbar'],
    ];

    public string $handler = 'file';

    /**
     * @var list<string>
     */
    public array $list = ['a', 'b'];
}
