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

use CodeIgniter\Validation\RulesTest as TraditionalRulesTest;
use CodeIgniter\Validation\Validation;
use Generator;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 */
final class RulesTest extends TraditionalRulesTest
{
    protected Validation $validation;
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

    /**
     * @dataProvider providePermitEmptyCasesStrict
     */
    public function testPermitEmptyStrict(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function providePermitEmptyCasesStrict(): Generator
    {
        yield from [
            [
                ['foo' => 'permit_empty'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => '0'],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => 0.0],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => null],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => false],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => 'user@domain.tld'],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => 'invalid'],
                false,
            ],
            // Required has more priority
            [
                ['foo' => 'permit_empty|required|valid_email'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => null],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => false],
                false,
            ],
            // This tests will return true because the input data is trimmed
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => '0'],
                true,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => 0.0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => 0.0, 'bar' => 1],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => '', 'bar' => 1],
                false,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => 0.0, 'bar' => 1],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => '', 'bar' => 1],
                true,
            ],
        ];
    }
}
