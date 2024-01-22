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

namespace CodeIgniter\Helpers\Array;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @group Others
 *
 * @internal
 */
final class ArrayHelperDuplicateCheckTest extends CIUnitTestCase
{
    private array $array = [
        [
            'provider_name'    => 'Rumah Sakit Siloam',
            'provider_email'   => 'xxxx@example.com',
            'provider_website' => 'example.com',
            'provider_region'  => '31.71',
            'provider_address' => 'wwaasawdasa',
        ],
        [
            'provider_name'    => 'Rumah Sakit Silorm',
            'provider_email'   => 'xxxx@example.com',
            'provider_website' => 'example.com',
            'provider_region'  => '31.72',
            'provider_address' => 'wwaasawdasa',
        ],
        [
            'provider_name'    => 'Rumah Sakit Siloam',
            'provider_email'   => 'xxxx@example.com',
            'provider_website' => 'example.com',
            'provider_region'  => '31.71',
            'provider_address' => 'wwaasawdasa',
        ],
        [
            'provider_name'    => 'Rumah Sakit Siloum',
            'provider_email'   => 'xxxx@example.com',
            'provider_website' => 'example.com',
            'provider_region'  => '31.74',
            'provider_address' => 'wwaasawdasa',
        ],
        [
            'provider_name'    => 'Rumah Sakit Siloem',
            'provider_email'   => 'xxxx@example.com',
            'provider_website' => 'example.com',
            'provider_region'  => '31.75',
            'provider_address' => 'wwaasawdasa',
        ],
        [
            'provider_name'    => 'Rumah Sakit Silosm',
            'provider_email'   => 'xxxx@example.com',
            'provider_website' => 'example.com',
            'provider_region'  => '31.76',
            'provider_address' => 'wwaasawdasa',
        ],
    ];

    public function testSingleColumn(): void
    {
        $this->assertSame([
            2 => [
                'provider_name' => 'Rumah Sakit Siloam',
            ],
        ], ArrayHelper::duplicatesBy('provider_name', $this->array));
    }

    public function testMultipleColumn(): void
    {
        $this->assertSame([
            2 => [
                'provider_name'   => 'Rumah Sakit Siloam',
                'provider_region' => '31.71',
            ],
        ], ArrayHelper::duplicatesBy(['provider_name', 'provider_region'], $this->array));
    }
}
