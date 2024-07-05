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
final class ArrayHelperIntersectKeyRecursiveTest extends CIUnitTestCase
{
    /**
     * @var array<string, array<string,mixed>|string|null>
     */
    private array $targetArray;

    protected function setUp(): void
    {
        parent::setUp();

        $this->targetArray = [
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

    public function testShuffleCopy(): void
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

        // var_dump(ArrayHelper::intersectKeyRecursive($original, $this->targetArray));

        $this->assertSame($original, ArrayHelper::intersectKeyRecursive($original, $this->targetArray));
    }

    public function testCopyWithAnotherValues(): void
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

        $this->assertSame($original, ArrayHelper::intersectKeyRecursive($original, $this->targetArray));
    }

    public function testEmptyCompare(): void
    {
        $this->assertSame([], ArrayHelper::intersectKeyRecursive($this->targetArray, []));
    }

    public function testEmptyOriginal(): void
    {
        $this->assertSame([], ArrayHelper::intersectKeyRecursive([], $this->targetArray));
    }

    public function testCompletelyDifferent(): void
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

        $this->assertSame([], ArrayHelper::intersectKeyRecursive($original, $this->targetArray));
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

        $intersect = [
            'a' => [
                'b' => [],
                'e' => 'value2',
                'f' => [
                    'g' => 'value3',
                    'i' => [],
                ],
                'k' => null,
                'm' => [
                    'new_n' => '',
                ],
            ],
        ];

        $this->assertSame($intersect, ArrayHelper::intersectKeyRecursive($original, $this->targetArray));
    }
}
