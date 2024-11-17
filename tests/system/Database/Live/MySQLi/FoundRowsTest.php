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

namespace CodeIgniter\Database\Live\MySQLi;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class FoundRowsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Database config for tests
     *
     * @var array<string, mixed>
     */
    private $tests;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        $config = config('Database');

        $this->tests = $config->tests;

        if ($this->tests['DBDriver'] !== 'MySQLi') {
            $this->markTestSkipped('Only MySQLi can complete this test.');
        }

        parent::setUp();
    }

    public function testEnableFoundRows(): void
    {
        $this->tests['foundRows'] = true;

        $db1 = Database::connect($this->tests);

        $this->assertTrue($db1->foundRows);
    }

    public function testDisableFoundRows(): void
    {
        $this->tests['foundRows'] = false;

        $db1 = Database::connect($this->tests);

        $this->assertFalse($db1->foundRows);
    }

    public function testAffectedRowsAfterEnableFoundRowsWithNoChange(): void
    {
        $this->tests['foundRows'] = true;

        $db1 = Database::connect($this->tests);

        $db1->table('db_user')
            ->set('country', 'US')
            ->where('country', 'US')
            ->update();

        $affectedRows = $db1->affectedRows();

        $this->assertSame(2, $affectedRows);
    }

    public function testAffectedRowsAfterDisableFoundRowsWithNoChange(): void
    {
        $this->tests['foundRows'] = false;

        $db1 = Database::connect($this->tests);

        $db1->table('db_user')
            ->set('country', 'US')
            ->where('country', 'US')
            ->update();

        $affectedRows = $db1->affectedRows();

        $this->assertSame(0, $affectedRows);
    }

    public function testAffectedRowsAfterEnableFoundRowsWithChange(): void
    {
        $this->tests['foundRows'] = true;

        $db1 = Database::connect($this->tests);

        $db1->table('db_user')
            ->set('country', 'NZ')
            ->where('country', 'US')
            ->update();

        $affectedRows = $db1->affectedRows();

        $this->assertSame(2, $affectedRows);
    }

    public function testAffectedRowsAfterDisableFoundRowsWithChange(): void
    {
        $this->tests['foundRows'] = false;

        $db1 = Database::connect($this->tests);

        $db1->table('db_user')
            ->set('country', 'NZ')
            ->where('country', 'US')
            ->update();

        $affectedRows = $db1->affectedRows();

        $this->assertSame(2, $affectedRows);
    }

    public function testAffectedRowsAfterEnableFoundRowsWithPartialChange(): void
    {
        $this->tests['foundRows'] = true;

        $db1 = Database::connect($this->tests);

        $db1->table('db_user')
            ->set('name', 'Derek Jones')
            ->where('country', 'US')
            ->update();

        $affectedRows = $db1->affectedRows();

        $this->assertSame(2, $affectedRows);
    }

    public function testAffectedRowsAfterDisableFoundRowsWithPartialChange(): void
    {
        $this->tests['foundRows'] = false;

        $db1 = Database::connect($this->tests);

        $db1->table('db_user')
            ->set('name', 'Derek Jones')
            ->where('country', 'US')
            ->update();

        $affectedRows = $db1->affectedRows();

        $this->assertSame(1, $affectedRows);
    }
}
