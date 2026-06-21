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

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use Config\Feature;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class LimitTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testLimitAlone(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testLimitAndOffset(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5, 1);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 1, 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testLimitAndOffsetMethod(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5)->offset(1);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 1, 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testLimitZeroAsAllOptimization(): void
    {
        $feature = new class () extends Feature {
            public int $accessCount = 0;

            public function __construct()
            {
                parent::__construct();
                unset($this->limitZeroAsAll);
            }

            public function __get(string $name): mixed
            {
                if ($name === 'limitZeroAsAll') {
                    $this->accessCount++;

                    return true;
                }

                return null;
            }
        };

        Factories::injectMock('config', 'Feature', $feature);

        // Constructor accesses it once
        $builder = new BaseBuilder('user', $this->db);
        $this->assertSame(1, $feature->accessCount);

        // Should not access config again during typical query building
        $builder->limit(0);
        $builder->getCompiledSelect();

        $this->assertSame(1, $feature->accessCount);

        Factories::reset('config');
    }
}
