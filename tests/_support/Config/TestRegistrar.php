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
 * Class Registrar
 *
 * Provides a basic registrar class for testing BaseConfig registration functions.
 */
class TestRegistrar
{
    /**
     * @param array<int|string, mixed> $previous
     */
    public static function RegistrarConfig(array $previous = [])
    {
        if ($previous === []) {
            return [
                'bar' => [
                    'first',
                    'second',
                ],
                'cars' => [
                    'Trucks' => [
                        'Volvo' => [
                            'year'  => 2019,
                            'color' => 'dark blue',
                        ],
                    ],
                    'Sedans Lux' => [
                        'Toyota' => [
                            'year'  => 2025,
                            'color' => 'silver',
                        ],
                    ],
                ],
            ];
        }

        return [
            'bar' => [
                'first',
                'second',
            ],
            'cars' => array_replace_recursive($previous['cars'], [
                'Trucks' => [
                    'Volvo' => [
                        'year'  => 2019,
                        'color' => 'dark blue',
                    ],
                ],
                'Sedans Lux' => [
                    'Toyota' => [
                        'year'  => 2025,
                        'color' => 'silver',
                    ],
                ],
            ]),
        ];
    }
}
