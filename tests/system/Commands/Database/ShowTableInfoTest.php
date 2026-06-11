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

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ShowTableInfoTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use StreamFilterTrait;

    protected $seed = CITestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->resetDataCache();

        putenv('NO_COLOR=1');
        CLI::init();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('NO_COLOR');
        CLI::init();
    }

    private function getNormalizedResult(): string
    {
        return str_replace(PHP_EOL, "\n", $this->getStreamFilterBuffer());
    }

    public function testDbTable(): void
    {
        command('db:table db_migrations');

        $result = $this->getNormalizedResult();

        $expected = 'Data of "db_migrations" table:';
        $this->assertStringContainsString($expected, $result);

        $expectedPattern = '/\| Id[[:blank:]]+\| Version[[:blank:]]+\| Class[[:blank:]]+\| Group[[:blank:]]+\| Namespace[[:blank:]]+\| Time[[:blank:]]+\| Batch \|/';
        $this->assertMatchesRegularExpression($expectedPattern, $result);
    }

    public function testDbTableShowsWithInvalidDBGroup(): void
    {
        command('db:table --show --dbgroup invalid');

        $result = $this->getNormalizedResult();

        $expected = '"invalid" is not a valid database connection group.';
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableShowsDBConfig(): void
    {
        command('db:table --show');

        $result = $this->getNormalizedResult();

        $expectedPattern = '/\| Hostname[[:blank:]]+\| Database[[:blank:]]+\| Username[[:blank:]]+\| DB Driver[[:blank:]]+\| DB Prefix[[:blank:]]+\| Port[[:blank:]]+\|/';
        $this->assertMatchesRegularExpression($expectedPattern, $result);
    }

    public function testDbTableShow(): void
    {
        command('db:table --show');

        $result = $this->getNormalizedResult();

        $expected = 'The following is a list of the names of all database tables:';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+---------------------------+-------------+---------------+
            | Id | Table Name                | Num of Rows | Num of Fields |
            +----+---------------------------+-------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $result);

        // The seeded `db_user` table has 4 rows and 7 fields.
        $this->assertMatchesRegularExpression('/\|\s+db_user\s+\|\s+4\s+\|\s+7\s+\|/', $result);
    }

    public function testDbTableMetadata(): void
    {
        command('db:table db_migrations --metadata');

        $result = $this->getNormalizedResult();

        $expected = 'List of metadata information in "db_migrations" table:';
        $this->assertStringContainsString($expected, $result);

        $result   = preg_replace('/\s+/', ' ', $result);
        $expected = <<<'EOL'
            | Field Name | Type | Max Length | Nullable? | Default | Primary Key? |
            EOL;
        $this->assertStringContainsString($expected, (string) $result);
    }

    public function testDbTableDesc(): void
    {
        command('db:table db_user --desc');

        $result = $this->getNormalizedResult();

        $expected = 'Data of "db_user" table:';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+--------------------+--------------------+---------+------------+------------+------------+
            | Id | Name               | Email              | Country | Created_at | Updated_at | Deleted_at |
            +----+--------------------+--------------------+---------+------------+------------+------------+
            | 4  | Chris Martin       | chris@world.com    | UK      |            |            |            |
            | 3  | Richard A Cause... | richard@world.c... | US      |            |            |            |
            | 2  | Ahmadinejad        | ahmadinejad@wor... | Iran    |            |            |            |
            | 1  | Derek Jones        | derek@world.com    | US      |            |            |            |
            +----+--------------------+--------------------+---------+------------+------------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableLimitFieldValueLength(): void
    {
        command('db:table db_user --limit-field-value 5');

        $result = $this->getNormalizedResult();

        $expected = 'Data of "db_user" table:';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+----------+----------+---------+------------+------------+------------+
            | Id | Name     | Email    | Country | Created_at | Updated_at | Deleted_at |
            +----+----------+----------+---------+------------+------------+------------+
            | 1  | Derek... | derek... | US      |            |            |            |
            | 2  | Ahmad... | ahmad... | Iran    |            |            |            |
            | 3  | Richa... | richa... | US      |            |            |            |
            | 4  | Chris... | chris... | UK      |            |            |            |
            +----+----------+----------+---------+------------+------------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableLimitRows(): void
    {
        command('db:table db_user --limit-rows 2');

        $result = $this->getNormalizedResult();

        $expected = 'Data of "db_user" table:';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+-------------+--------------------+---------+------------+------------+------------+
            | Id | Name        | Email              | Country | Created_at | Updated_at | Deleted_at |
            +----+-------------+--------------------+---------+------------+------------+------------+
            | 1  | Derek Jones | derek@world.com    | US      |            |            |            |
            | 2  | Ahmadinejad | ahmadinejad@wor... | Iran    |            |            |            |
            +----+-------------+--------------------+---------+------------+------------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableAllOptions(): void
    {
        command('db:table db_user --limit-rows 2 --limit-field-value 5 --desc');

        $result = $this->getNormalizedResult();

        $expected = 'Data of "db_user" table:';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +----+----------+----------+---------+------------+------------+------------+
            | Id | Name     | Email    | Country | Created_at | Updated_at | Deleted_at |
            +----+----------+----------+---------+------------+------------+------------+
            | 4  | Chris... | chris... | UK      |            |            |            |
            | 3  | Richa... | richa... | US      |            |            |            |
            +----+----------+----------+---------+------------+------------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }

    public function testDbTableWithInvalidDBGroupSkipsThePrompt(): void
    {
        command('db:table --dbgroup invalid');

        $this->assertStringContainsString(
            '"invalid" is not a valid database connection group.',
            $this->getNormalizedResult(),
        );
    }

    public function testDbTableErrorsWhenNoTableSpecifiedAndNonInteractive(): void
    {
        $exitCode = service('commands')->runCommand('db:table', [], ['no-interaction' => null]);

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertStringContainsString('No table name was specified.', $this->getNormalizedResult());
    }

    public function testDbTableErrorsWhenTableNotFoundAndNonInteractive(): void
    {
        $exitCode = service('commands')->runCommand('db:table', ['missing_table'], ['no-interaction' => null]);

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertStringContainsString('Table "missing_table" was not found in the database.', $this->getNormalizedResult());
    }

    public function testDbTableReportsNoTablesWhenDatabaseIsEmpty(): void
    {
        // A fresh in-memory SQLite database has no tables, regardless of the
        // driver the suite runs against. Route the `default` group to it.
        $original = $this->getPrivateProperty(Database::class, 'instances');
        $empty    = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '']);
        $this->setPrivateProperty(Database::class, 'instances', ['default' => $empty] + $original);

        try {
            command('db:table --dbgroup default');

            $this->assertStringContainsString('Database has no tables!', $this->getNormalizedResult());
        } finally {
            $this->setPrivateProperty(Database::class, 'instances', $original);
            $empty->close();
        }
    }

    public function testDbTableSortsDescWhenTableHasNoIdColumn(): void
    {
        // `db_team_members` has a composite key and no `id` column, so --desc
        // reverses the seeded rows (person_id 33 before 22) instead of adding an
        // ORDER BY clause.
        command('db:table db_team_members --desc');

        $result = $this->getNormalizedResult();

        $expected = 'Data of "db_team_members" table:';
        $this->assertStringContainsString($expected, $result);

        $expected = <<<'EOL'
            +---------+-----------+--------+--------+------------+
            | Team_id | Person_id | Role   | Status | Created_at |
            +---------+-----------+--------+--------+------------+
            | 1       | 33        | mentor | active |            |
            | 1       | 22        | member | active |            |
            +---------+-----------+--------+--------+------------+
            EOL;
        $this->assertStringContainsString($expected, $result);
    }
}
