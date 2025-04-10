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

namespace CodeIgniter\Database\Live\OCI8;

use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\Query;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class LastInsertIDTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->db->DBDriver !== 'OCI8') {
            $this->markTestSkipped('Only OCI8 has its own implementation.');
        }
    }

    public function testGetInsertIDWithInsert(): void
    {
        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Discount!',
        ];

        $this->db->table('job')->insert($jobData);
        $actual = $this->db->insertID();

        $this->assertSame(5, $actual);
    }

    public function testGetInsertIDWithQuery(): void
    {
        $this->db->query('INSERT INTO "db_job" ("name", "description") VALUES (?, ?)', ['Grocery Sales', 'Discount!']);
        $actual = $this->db->insertID();

        $this->assertSame(5, $actual);
    }

    public function testGetInsertIDWithHasCommentQuery(): void
    {
        $sql = <<<'SQL'
            -- INSERT INTO "db_misc" ("key", "value") VALUES ('key', 'value')
            --INSERT INTO "db_misc" ("key", "value") VALUES ('key', 'value')
            /* INSERT INTO "db_misc" ("key", "value") VALUES ('key', 'value') */
            /*INSERT INTO "db_misc" ("key", "value") VALUES ('key', 'value')*/
            INSERT /* INTO "db_misc" */ INTO -- comment "db_misc"
            "db_job"  ("name", "description") VALUES (' INTO "abc"', ?)
            SQL;
        $this->db->query($sql, ['Discount!']);
        $actual = $this->db->insertID();

        $this->assertSame(5, $actual);
    }

    public function testGetInsertIDWithPreparedQuery(): void
    {
        $query = $this->db->prepare(static function ($db) {
            $sql = 'INSERT INTO "db_job" ("name", "description") VALUES (?, ?)';

            return (new Query($db))->setQuery($sql);
        });
        $this->assertInstanceOf(BasePreparedQuery::class, $query);

        $query->execute('foo', 'bar');
        $actual = $this->db->insertID();

        $this->assertSame(5, $actual);
    }
}
