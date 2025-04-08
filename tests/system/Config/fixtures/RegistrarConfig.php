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

class RegistrarConfig extends CodeIgniter\Config\BaseConfig
{
    public $foo = 'bar';
    public $bar = [
        'baz',
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    public $cars = [
        'Sedans' => [
            'Toyota' => [
                'year'  => 2018,
                'color' => 'silver',
            ],
        ],
        'Trucks' => [
            'Volvo' => [
                'year'  => 2019,
                'color' => 'blue',
            ],
        ],
    ];
}
