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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use CodeIgniter\Database\Query;

/**
 * @internal
 */
final class ReplaceTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testSimpleReplace()
    {
        $builder = $this->db->table('jobs');

        $expected = 'REPLACE INTO "jobs" ("title", "name", "date") VALUES (:title:, :name:, :date:)';

        $data = [
            'title' => 'My title',
            'name'  => 'My Name',
            'date'  => 'My date',
        ];

        $this->assertSame($expected, $builder->testMode()->replace($data));
    }

    public function testReplaceThrowsExceptionWithNoData()
    {
        $builder = $this->db->table('jobs');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must use the "set" method to update an entry.');

        $builder->replace();
    }

    public function testReplaceBatch()
    {
        $builder = $this->db->table('jobs');

        $replaceData = [
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
        $builder->replaceBatch($replaceData, true);

        $query = $this->db->getLastQuery();
        $this->assertInstanceOf(Query::class, $query);

        $raw = <<<'SQL'
            INSERT INTO "jobs" ("description", "id", "name") VALUES ('There''s something in your teeth',2,'Commedian'), ('I am yellow',3,'Cab Driver') ON DUPLICATE KEY UPDATE "description" = VALUES("description"), "id" = VALUES("id"), "name" = VALUES("name")
            SQL;
        $this->assertSame($raw, str_replace("\n", ' ', $query->getOriginalQuery()));

        $expected = "INSERT INTO \"jobs\" (\"description\", \"id\", \"name\") VALUES ('There''s something in your teeth',2,'Commedian'), ('I am yellow',3,'Cab Driver') ON DUPLICATE KEY UPDATE \"description\" = VALUES(\"description\"), \"id\" = VALUES(\"id\"), \"name\" = VALUES(\"name\")";
        $this->assertSame($expected, str_replace("\n", ' ', $query->getQuery()));
    }
}
