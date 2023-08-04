<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Validation\FormatRulesTest as TraditionalFormatRulesTest;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @group Others
 */
final class FormatRulesTest extends TraditionalFormatRulesTest
{
    protected array $config = [
        'ruleSets' => [
            Rules::class,
            FormatRules::class,
            FileRules::class,
            CreditCardRules::class,
            TestRules::class,
        ],
        'groupA' => [
            'foo' => 'required|min_length[5]',
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.',
            ],
        ],
    ];

    public static function provideAlphaSpace(): iterable
    {
        yield from [
            [
                null,
                false,  // true in Traditional Rule
            ],
            [
                self::ALPHABET,
                true,
            ],
            [
                self::ALPHABET . ' ',
                true,
            ],
            [
                self::ALPHABET . "\n",
                false,
            ],
            [
                self::ALPHABET . '1',
                false,
            ],
            [
                self::ALPHABET . '*',
                false,
            ],
        ];
    }

    public static function provideInvalidIntegerType(): iterable
    {
        yield 'array with int' => [
            [555],
            false,  // TypeError in Traditional Rule
        ];

        yield 'empty array' => [
            [],
            false,  // TypeError in Traditional Rule
        ];

        yield 'bool true' => [
            true,
            false,  // true in Traditional Rule
        ];

        yield 'bool false' => [
            false,
            false,
        ];

        yield 'null' => [
            null,
            false,
        ];
    }
}
