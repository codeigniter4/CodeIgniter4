<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use Throwable;

/**
 * @internal
 *
 * @group Others
 */
final class BaseConnectionTest extends CIUnitTestCase
{
    private array $options = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'first',
        'password' => 'last',
        'database' => 'dbname',
        'DBDriver' => 'MockDriver',
        'DBPrefix' => 'test_',
        'pConnect' => true,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
    ];
    private array $failoverOptions = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'failover',
        'password' => 'one',
        'database' => 'failover',
        'DBDriver' => 'MockDriver',
        'DBPrefix' => 'test_',
        'pConnect' => true,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
    ];

    public function testSavesConfigOptions(): void
    {
        $db = new MockConnection($this->options);

        $this->assertSame('localhost', $db->hostname);
        $this->assertSame('first', $db->username);
        $this->assertSame('last', $db->password);
        $this->assertSame('dbname', $db->database);
        $this->assertSame('MockDriver', $db->DBDriver);
        $this->assertTrue($db->pConnect);
        $this->assertTrue($db->DBDebug);
        $this->assertSame('utf8', $db->charset);
        $this->assertSame('utf8_general_ci', $db->DBCollat);
        $this->assertSame('', $db->swapPre);
        $this->assertFalse($db->encrypt);
        $this->assertFalse($db->compress);
        $this->assertTrue($db->strictOn);
        $this->assertSame([], $db->failover);
    }

    public function testConnectionThrowExceptionWhenCannotConnect(): void
    {
        try {
            $db = new MockConnection($this->options);
            $db->shouldReturn('connect', false)->initialize();
        } catch (Throwable $e) {
            $this->assertInstanceOf(DatabaseException::class, $e);
            $this->assertStringContainsString('Unable to connect to the database.', $e->getMessage());
        }
    }

    public function testCanConnectAndStoreConnection(): void
    {
        $db = new MockConnection($this->options);
        $db->shouldReturn('connect', 123)->initialize();

        $this->assertSame(123, $db->getConnection());
    }

    public function testCanConnectToFailoverWhenNoConnectionAvailable(): void
    {
        $options             = $this->options;
        $options['failover'] = [$this->failoverOptions];

        $db = new class ($options) extends MockConnection {
            protected $returnValues = [
                'connect' => [false, 345],
            ];
        };

        $this->assertSame(345, $db->getConnection());
        $this->assertSame('failover', $db->username);
    }

    public function testStoresConnectionTimings(): void
    {
        $start = microtime(true);

        $db = new MockConnection($this->options);
        $db->initialize();

        $this->assertGreaterThan($start, $db->getConnectStart());
        $this->assertGreaterThan(0.0, $db->getConnectDuration());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5535
     */
    public function testStoresConnectionTimingsNotConnected(): void
    {
        $db = new MockConnection($this->options);

        $this->assertSame('0.000000', $db->getConnectDuration());
    }

    public function testMagicIssetTrue(): void
    {
        $db = new MockConnection($this->options);

        $this->assertTrue(isset($db->charset));
    }

    public function testMagicIssetFalse(): void
    {
        $db = new MockConnection($this->options);

        $this->assertFalse(isset($db->foobar));
    }

    public function testMagicGet(): void
    {
        $db = new MockConnection($this->options);

        $this->assertSame('utf8', $db->charset);
    }

    public function testMagicGetMissing(): void
    {
        $db = new MockConnection($this->options);

        $this->assertNull($db->foobar);
    }

    /**
     * These tests are intended to confirm the current behavior.
     * We do not know if all of these are the correct behavior.
     *
     * @dataProvider provideProtectIdentifiers
     */
    public function testProtectIdentifiers(
        bool $prefixSingle,
        bool $protectIdentifiers,
        bool $fieldExists,
        string $item,
        string $expected
    ): void {
        $db = new MockConnection($this->options);

        $return = $db->protectIdentifiers($item, $prefixSingle, $protectIdentifiers, $fieldExists);

        $this->assertSame($expected, $return);
    }

    public static function provideProtectIdentifiers(): iterable
    {
        yield from [
            // $prefixSingle, $protectIdentifiers, $fieldExists, $item, $expected
            'empty string'        => [false, true, true, '', ''],
            'empty string prefix' => [true, true, true, '', '"test_"'], // Incorrect usage? or should be ''?

            'single table'        => [false, true, false, 'jobs', '"jobs"'],
            'single table prefix' => [true, true, false, 'jobs', '"test_jobs"'],

            'string'        => [false, true, true, "'Accountant'", "'Accountant'"],
            'single prefix' => [true, true, true, "'Accountant'", "'Accountant'"],

            'numbers only'        => [false, true, false, '12345', '12345'], // Should be quoted?
            'numbers only prefix' => [true, true, false, '12345', '"test_12345"'],

            'table AS alias'        => [false, true, false, 'role AS myRole', '"role" AS "myRole"'],
            'table AS alias prefix' => [true, true, false, 'role AS myRole', '"test_role" AS "myRole"'],

            'quoted table'        => [false, true, false, '"jobs"', '"jobs"'],
            'quoted table prefix' => [true, true, false, '"jobs"', '"test_jobs"'],

            'quoted table alias'        => [false, true, false, '"jobs" "j"', '"jobs" "j"'],
            'quoted table alias prefix' => [true, true, false, '"jobs" "j"', '"test_jobs" "j"'],

            'table.*'             => [false, true, true, 'jobs.*', '"test_jobs".*'], // Prefixed because it has segments
            'table.* prefix'      => [true, true, true, 'jobs.*', '"test_jobs".*'],
            'table.column'        => [false, true, true, 'users.id', '"test_users"."id"'], // Prefixed because it has segments
            'table.column prefix' => [true, true, true, 'users.id', '"test_users"."id"'],
            'table.column AS'     => [
                false, true, true,
                'users.id AS user_id',
                '"test_users"."id" AS "user_id"', // Prefixed because it has segments
            ],
            'table.column AS prefix' => [
                true, true, true,
                'users.id AS user_id',
                '"test_users"."id" AS "user_id"',
            ],

            'function table.column'        => [false, true, true, 'LOWER(jobs.name)', 'LOWER(jobs.name)'],
            'function table.column prefix' => [true, true, true, 'LOWER(jobs.name)', 'LOWER(jobs.name)'],

            'function only'   => [false, true, true, 'RAND()', 'RAND()'],
            'function column' => [false, true, true, 'SUM(id)', 'SUM(id)'],

            'function column AS' => [
                false, true, true,
                'COUNT(payments) AS myAlias',
                'COUNT(payments) AS myAlias',
            ],
            'function column AS prefix' => [
                true, true, true,
                'COUNT(payments) AS myAlias',
                'COUNT(payments) AS myAlias',
            ],

            'function quoted table column AS' => [
                false, true, true,
                'MAX("db"."payments") AS "payments"',
                'MAX("db"."payments") AS "payments"',
            ],

            'quoted column operator AS' => [
                false, true, true,
                '"numericValue1" + "numericValue2" AS "numericResult"',
                '"numericValue1"" + ""numericValue2" AS "numericResult"', // Cannot process correctly
            ],
            'quoted column operator AS no-protect' => [
                false, false, true,
                '"numericValue1" + "numericValue2" AS "numericResult"',
                '"numericValue1" + "numericValue2" AS "numericResult"',
            ],

            'sub query' => [
                false, true, true,
                '(SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4) AS amount_paid)',
                '(SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4) AS amount_paid)',
            ],
            'sub query with missing `)` at the end' => [
                false, true, true,
                '(SELECT MAX(advance_amount) FROM "orders" WHERE "id" > 2',
                '(SELECT MAX(advance_amount) FROM "orders" WHERE "id" > 2',
            ],
        ];
    }
}
