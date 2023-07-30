<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Query;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use InvalidArgumentException;

/**
 * @internal
 *
 * @group Others
 */
final class InsertTest extends CIUnitTestCase
{
    /**
     * @var MockConnection
     */
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testInsertArray(): void
    {
        $builder = $this->db->table('jobs');

        $insertData = [
            'id'   => 1,
            'name' => 'Grocery Sales',
        ];
        $builder->testMode()->insert($insertData, true);

        $expectedSQL   = 'INSERT INTO "jobs" ("id", "name") VALUES (1, \'Grocery Sales\')';
        $expectedBinds = [
            'id' => [
                1,
                true,
            ],
            'name' => [
                'Grocery Sales',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledInsert()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testInsertObject(): void
    {
        $builder = $this->db->table('jobs');

        $insertData = (object) [
            'id'   => 1,
            'name' => 'Grocery Sales',
        ];
        $builder->testMode()->insert($insertData, true);

        $expectedSQL   = 'INSERT INTO "jobs" ("id", "name") VALUES (1, \'Grocery Sales\')';
        $expectedBinds = [
            'id' => [
                1,
                true,
            ],
            'name' => [
                'Grocery Sales',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledInsert()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testInsertObjectWithRawSql(): void
    {
        $builder = $this->db->table('jobs');

        $insertData = (object) [
            'id'   => 1,
            'name' => new RawSql('CONCAT("id", \'Grocery Sales\')'),
        ];
        $builder->testMode()->insert($insertData, true);

        $expectedSQL = 'INSERT INTO "jobs" ("id", "name") VALUES (1, CONCAT("id", \'Grocery Sales\'))';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledInsert()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5365
     */
    public function testInsertWithTableAlias(): void
    {
        $builder = $this->db->table('jobs as j');

        $insertData = [
            'id'   => 1,
            'name' => 'Grocery Sales',
        ];
        $builder->testMode()->insert($insertData, true);

        $expectedSQL   = 'INSERT INTO "jobs" ("id", "name") VALUES (1, \'Grocery Sales\')';
        $expectedBinds = [
            'id' => [
                1,
                true,
            ],
            'name' => [
                'Grocery Sales',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledInsert()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testThrowsExceptionOnNoValuesSet(): void
    {
        $builder = $this->db->table('jobs');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must use the "set" method to insert an entry.');

        $builder->testMode()->insert(null, true);
    }

    public function testInsertBatch(): void
    {
        $builder = $this->db->table('jobs');

        $insertData = [
            [
                'id'          => 2,
                'name'        => 'Commedian',
                'description' => 'There\'s something in your teeth',
            ],
            [
                'id'          => 3,
                'name'        => 'Cab Driver',
                'description' => 'I am yellow',
            ],
        ];

        $this->db->shouldReturn('execute', 1)->shouldReturn('affectedRows', 1);
        $builder->insertBatch($insertData, true);

        $query = $this->db->getLastQuery();
        $this->assertInstanceOf(Query::class, $query);

        $raw = <<<'SQL'
            INSERT INTO "jobs" ("description", "id", "name") VALUES ('There''s something in your teeth',2,'Commedian'), ('I am yellow',3,'Cab Driver')
            SQL;
        $this->assertSame($raw, str_replace("\n", ' ', $query->getOriginalQuery()));

        $expected = "INSERT INTO \"jobs\" (\"description\", \"id\", \"name\") VALUES ('There''s something in your teeth',2,'Commedian'), ('I am yellow',3,'Cab Driver')";
        $this->assertSame($expected, str_replace("\n", ' ', $query->getQuery()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5671
     */
    public function testInsertBatchIgnore(): void
    {
        $builder = $this->db->table('jobs');

        $insertData = [
            [
                'id'          => 2,
                'name'        => 'Commedian',
                'description' => 'There\'s something in your teeth',
            ],
            [
                'id'          => 3,
                'name'        => 'Cab Driver',
                'description' => 'I am yellow',
            ],
        ];

        $this->db->shouldReturn('execute', 1)->shouldReturn('affectedRows', 1);
        $builder->ignore()->insertBatch($insertData, true, 1);

        $query = $this->db->getLastQuery();
        $this->assertInstanceOf(Query::class, $query);

        $raw = <<<'SQL'
            INSERT IGNORE INTO "jobs" ("description", "id", "name") VALUES ('I am yellow',3,'Cab Driver')
            SQL;
        $this->assertSame($raw, str_replace("\n", ' ', $query->getOriginalQuery()));

        $expected = <<<'SQL'
            INSERT IGNORE INTO "jobs" ("description", "id", "name") VALUES ('I am yellow',3,'Cab Driver')
            SQL;
        $this->assertSame($expected, str_replace("\n", ' ', $query->getQuery()));
    }

    public function testInsertBatchWithoutEscape(): void
    {
        $builder = $this->db->table('jobs');

        $insertData = [
            [
                'id'          => 2,
                'name'        => '1 + 1',
                'description' => '1 + 2',
            ],
            [
                'id'          => 3,
                'name'        => '2 + 1',
                'description' => '2 + 2',
            ],
        ];

        $this->db->shouldReturn('execute', 1)->shouldReturn('affectedRows', 1);
        $builder->insertBatch($insertData, false);

        $query = $this->db->getLastQuery();
        $this->assertInstanceOf(Query::class, $query);

        $expected = 'INSERT INTO "jobs" ("description", "id", "name") VALUES (1 + 2,2,1 + 1), (2 + 2,3,2 + 1)';
        $this->assertSame($expected, str_replace("\n", ' ', $query->getQuery()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4345
     */
    public function testInsertBatchWithFieldsEndingInNumbers(): void
    {
        $builder = $this->db->table('ip_table');

        $data = [
            ['ip' => '1.1.1.0', 'ip2' => '1.1.1.2'],
            ['ip' => '2.2.2.0', 'ip2' => '2.2.2.2'],
            ['ip' => '3.3.3.0', 'ip2' => '3.3.3.2'],
            ['ip' => '4.4.4.0', 'ip2' => '4.4.4.2'],
        ];

        $this->db->shouldReturn('execute', 1)->shouldReturn('affectedRows', 1);
        $builder->insertBatch($data, true);

        $query = $this->db->getLastQuery();
        $this->assertInstanceOf(Query::class, $query);

        $expected = "INSERT INTO \"ip_table\" (\"ip\", \"ip2\") VALUES ('1.1.1.0','1.1.1.2'), ('2.2.2.0','2.2.2.2'), ('3.3.3.0','3.3.3.2'), ('4.4.4.0','4.4.4.2')";
        $this->assertSame($expected, str_replace("\n", ' ', $query->getQuery()));
    }

    public function testInsertBatchThrowsExceptionOnNoData(): void
    {
        $builder = $this->db->table('jobs');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('insertBatch() has no data.');
        $builder->insertBatch();
    }

    public function testInsertBatchThrowsExceptionOnEmptyData(): void
    {
        $builder = $this->db->table('jobs');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('insertBatch() has no data.');
        $builder->insertBatch([]);
    }

    public function testSetIncorrectRawSqlUsage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'RawSql "expires = DATE_ADD(NOW(), INTERVAL 2 HOUR)" cannot be used here.'
        );

        $builder = $this->db->table('auth_bearer');

        $builder->testMode()
            ->set([
                'jti'       => 'jti',
                'proctorID' => '12',
            ])
            ->set(new RawSql('expires = DATE_ADD(NOW(), INTERVAL 2 HOUR)'))
            ->insert();
    }
}
