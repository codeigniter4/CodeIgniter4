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
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ArrayHelperRecursiveDiffTest extends CIUnitTestCase
{
    private array $compareWith;

    protected function setUp(): void
    {
        $this->compareWith = [
            'a' => [
                'b' => [
                    'c' => [
                        'd' => 'value1',
                    ],
                ],
                'e' => 'value2',
                'f' => [
                    'g' => 'value3',
                    'h' => 'value4',
                    'i' => [
                        'j' => 'value5',
                    ],
                ],
                'k' => null,
                'l' => [],
                'm' => '',
            ],
        ];
    }

    public function testRecursiveDiffCopy(): void
    {
        $this->assertSame([], ArrayHelper::recursiveDiff($this->compareWith, $this->compareWith));
    }

    public function testRecursiveDiffShuffleCopy(): void
    {
        $original = [
            'a' => [
                'l' => [],
                'k' => null,
                'e' => 'value2',
                'f' => [
                    'i' => [
                        'j' => 'value5',
                    ],
                    'h' => 'value4',
                    'g' => 'value3',
                ],
                'm' => '',
                'b' => [
                    'c' => [
                        'd' => 'value1',
                    ],
                ],
            ],
        ];

        $this->assertSame([], ArrayHelper::recursiveDiff($original, $this->compareWith));
    }

    public function testRecursiveDiffCopyWithAnotherValues(): void
    {
        $original = [
            'a' => [
                'b' => [
                    'c' => [
                        'd' => 'value1_1',
                    ],
                ],
                'e' => 'value2_2',
                'f' => [
                    'g' => 'value3_3',
                    'h' => 'value4_4',
                    'i' => [
                        'j' => 'value5_5',
                    ],
                ],
                'k' => [],
                'l' => null,
                'm' => 'value6_6',
            ],
        ];

        $this->assertSame([], ArrayHelper::recursiveDiff($original, $this->compareWith));
    }

    public function testRecursiveDiffEmptyCompare(): void
    {
        $this->assertSame($this->compareWith, ArrayHelper::recursiveDiff($this->compareWith, []));
    }

    public function testRecursiveDiffEmptyOriginal(): void
    {
        $this->assertSame([], ArrayHelper::recursiveDiff([], $this->compareWith));
    }

    public function testRecursiveDiffCompletelyDifferent(): void
    {
        $original = [
            'new_a' => [
                'new_b' => [
                    'new_c' => [
                        'new_d' => 'value1_1',
                    ],
                ],
                'new_e' => 'value2_2',
                'new_f' => [
                    'new_g' => 'value3_3',
                    'new_h' => 'value4_4',
                    'new_i' => [
                        'new_j' => 'value5_5',
                    ],
                ],
                'new_k' => [],
                'new_l' => null,
                'new_m' => '',
            ],
        ];

        $this->assertSame($original, ArrayHelper::recursiveDiff($original, $this->compareWith));
    }

    public function testRecursiveDiffPartlyDifferent(): void
    {
        $original = [
            'a' => [
                'b' => [
                    'new_c' => [
                        'd' => 'value1',
                    ],
                ],
                'e' => 'value2',
                'f' => [
                    'g'     => 'value3',
                    'new_h' => 'value4',
                    'i'     => [
                        'new_j' => 'value5',
                    ],
                ],
                'k'     => null,
                'new_l' => [],
                'm'     => [
                    'new_n' => '',
                ],
            ],
        ];

        $diff = [
            'a' => [
                'b' => [
                    'new_c' => [
                        'd' => 'value1',
                    ],
                ],
                'f' => [
                    'new_h' => 'value4',
                    'i'     => [
                        'new_j' => 'value5',
                    ],
                ],
                'm' => [
                    'new_n' => '',
                ],
            ],
        ];

        $this->assertSame($diff, ArrayHelper::recursiveDiff($original, $this->compareWith));
    }

    public function testRecursiveCountSimple(): void
    {
        $array = [
            'a' => 'value1',
            'b' => 'value2',
            'c' => 'value3',
        ];

        $this->assertSame(3, ArrayHelper::recursiveCount($array));
    }

    public function testRecursiveCountNested(): void
    {
        $array = [
            'a' => 'value1',
            'b' => [
                'c' => 'value2',
            ],
            'd' => 'value3',
            'e' => [
                'f' => [
                    'g' => 'value4',
                    'h' => [
                        'i' => 'value5',
                    ],
                ],
            ],
            'j' => [],
            'k' => null,
        ];

        $this->assertSame(11, ArrayHelper::recursiveCount($array));
    }

    public function testRecursiveCountEqualEmpty(): void
    {
        $array = [
            'root' => [
                'a' => [],
                'b' => false,
                'c' => null,
                'd' => '',
                'e' => 0,
            ],
        ];

        $this->assertSame(6, ArrayHelper::recursiveCount($array));
    }
}
