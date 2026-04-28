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

namespace CodeIgniter\Database;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Throwable;
use TypeError;

/**
 * @internal
 */
#[Group('Others')]
final class BaseConnectionTest extends CIUnitTestCase
{
    private array $options = [
        'DSN'        => '',
        'hostname'   => 'localhost',
        'username'   => 'first',
        'password'   => 'last',
        'database'   => 'dbname',
        'DBDriver'   => 'MockDriver',
        'DBPrefix'   => 'test_',
        'pConnect'   => true,
        'DBDebug'    => true,
        'charset'    => 'utf8mb4',
        'DBCollat'   => 'utf8mb4_general_ci',
        'swapPre'    => '',
        'encrypt'    => false,
        'compress'   => false,
        'failover'   => [],
        'dateFormat' => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
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
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
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
        $this->assertSame('utf8mb4', $db->charset);
        $this->assertSame('utf8mb4_general_ci', $db->DBCollat);
        $this->assertSame('', $db->swapPre);
        $this->assertFalse($db->encrypt);
        $this->assertFalse($db->compress);
        $this->assertSame([], $db->failover);
        $this->assertSame([
            'date'        => 'Y-m-d',
            'datetime'    => 'Y-m-d H:i:s',
            'datetime-ms' => 'Y-m-d H:i:s.v',
            'datetime-us' => 'Y-m-d H:i:s.u',
            'time'        => 'H:i:s',
        ], $db->dateFormat);
    }

    public function testCastsStringConfigValuesToTypedProperties(): void
    {
        $db = new class ([...$this->options, 'synchronous' => '1', 'busyTimeout' => '4000', 'typedBool' => '0', 'nullInt' => 'null']) extends MockConnection {
            protected ?int $synchronous = null;
            protected ?int $busyTimeout = null;
            protected bool $typedBool   = true;
            protected ?int $nullInt     = 1;

            public function getSynchronous(): ?int
            {
                return $this->synchronous;
            }

            public function getBusyTimeout(): ?int
            {
                return $this->busyTimeout;
            }

            public function isTypedBool(): bool
            {
                return $this->typedBool;
            }

            public function getNullInt(): ?int
            {
                return $this->nullInt;
            }
        };

        $this->assertSame(1, $db->getSynchronous());
        $this->assertSame(4000, $db->getBusyTimeout());
        $this->assertFalse($db->isTypedBool());
        $this->assertNull($db->getNullInt());
    }

    public function testCastsExtendedBoolStringsToBool(): void
    {
        $db = new class ([...$this->options, 'enabledYes' => 'yes', 'enabledOn' => 'on', 'disabledNo' => 'no', 'disabledOff' => 'off']) extends MockConnection {
            protected bool $enabledYes  = false;
            protected bool $enabledOn   = false;
            protected bool $disabledNo  = true;
            protected bool $disabledOff = true;

            public function isEnabledYes(): bool
            {
                return $this->enabledYes;
            }

            public function isEnabledOn(): bool
            {
                return $this->enabledOn;
            }

            public function isDisabledNo(): bool
            {
                return $this->disabledNo;
            }

            public function isDisabledOff(): bool
            {
                return $this->disabledOff;
            }
        };

        $this->assertTrue($db->isEnabledYes());
        $this->assertTrue($db->isEnabledOn());
        $this->assertFalse($db->isDisabledNo());
        $this->assertFalse($db->isDisabledOff());
    }

    public function testCastsFalseAndTrueStandaloneUnionTypes(): void
    {
        $db = new class ([...$this->options, 'withFalse' => 'false', 'withTrue' => 'true']) extends MockConnection {
            protected false|int $withFalse = 0;
            protected int|true $withTrue   = 0;

            public function getWithFalse(): false|int
            {
                return $this->withFalse;
            }

            public function getWithTrue(): int|true
            {
                return $this->withTrue;
            }
        };

        $this->assertFalse($db->getWithFalse());
        $this->assertTrue($db->getWithTrue());
    }

    public function testCachesTypedPropertiesIncrementally(): void
    {
        $factory = static fn (array $options): MockConnection => new class ($options) extends MockConnection {
            protected ?int $synchronous = null;
            protected ?int $busyTimeout = null;

            public function getSynchronous(): ?int
            {
                return $this->synchronous;
            }

            public function getBusyTimeout(): ?int
            {
                return $this->busyTimeout;
            }
        };

        $first  = $factory([...$this->options, 'synchronous' => '1']);
        $second = $factory([...$this->options, 'busyTimeout' => '4000']);

        $this->assertSame(1, $first->getSynchronous());
        $this->assertSame(4000, $second->getBusyTimeout());
    }

    public function testInvalidStringValueForTypedPropertyThrowsTypeError(): void
    {
        $this->expectException(TypeError::class);

        new class ([...$this->options, 'synchronous' => 'not-an-int']) extends MockConnection {
            protected ?int $synchronous = null;
        };
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
        $conn = new class () {};

        $db = new MockConnection($this->options);
        $db->shouldReturn('connect', $conn)->initialize();

        $this->assertSame($conn, $db->getConnection());
    }

    public function testCanConnectToFailoverWhenNoConnectionAvailable(): void
    {
        $options = [
            ...$this->options,
            ...['failover' => [$this->failoverOptions]],
        ];

        $conn = new class () {};

        $db = new class ($options, $conn) extends MockConnection {
            /**
             * @param array<string, mixed> $params
             */
            public function __construct(array $params, object $return)
            {
                // need to call it here before any initialization
                // we cannot do it directly in the property as objects
                // cannot be set directly in properties
                $this->shouldReturn('connect', [false, $return]);

                parent::__construct($params);
            }
        };

        $this->assertSame($conn, $db->getConnection());
        $this->assertSame('failover', $db->username);
    }

    public function testStoresConnectionTimings(): void
    {
        $start = microtime(true);

        $db = new MockConnection($this->options);
        $db->initialize();

        $this->assertGreaterThan($start, $db->getConnectStart());
        $this->assertGreaterThanOrEqual(0.0, $db->getConnectDuration());
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

        $this->assertSame($db->charset !== null, isset($db->charset)); // @phpstan-ignore isset.property
    }

    public function testMagicIssetFalse(): void
    {
        $db = new MockConnection($this->options);

        $this->assertFalse(isset($db->foobar)); // @phpstan-ignore property.notFound
    }

    public function testMagicGet(): void
    {
        $db = new MockConnection($this->options);

        $this->assertSame('utf8mb4', $db->charset);
    }

    public function testMagicGetMissing(): void
    {
        $db = new MockConnection($this->options);

        $this->assertNull($db->foobar); // @phpstan-ignore property.notFound
    }

    /**
     * These tests are intended to confirm the current behavior.
     * We do not know if all of these are the correct behavior.
     */
    #[DataProvider('provideProtectIdentifiers')]
    public function testProtectIdentifiers(
        bool $prefixSingle,
        bool $protectIdentifiers,
        bool $fieldExists,
        string $item,
        string $expected,
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

    /**
     * These tests are intended to confirm the current behavior.
     */
    #[DataProvider('provideEscapeIdentifiers')]
    public function testEscapeIdentifiers(string $item, string $expected): void
    {
        $db = new MockConnection($this->options);

        $return = $db->escapeIdentifiers($item);

        $this->assertSame($expected, $return);
    }

    /**
     * @return iterable<string, list<string>>
     */
    public static function provideEscapeIdentifiers(): iterable
    {
        yield from [
            // $item, $expected
            'simple'    => ['test', '"test"'],
            'with dots' => ['com.sitedb.web', '"com"."sitedb"."web"'],
        ];
    }

    #[DataProvider('provideEscapeIdentifier')]
    public function testEscapeIdentifier(string $item, string $expected): void
    {
        $db = new MockConnection($this->options);

        $return = $db->escapeIdentifier($item);

        $this->assertSame($expected, $return);
    }

    /**
     * @return iterable<string, list<string>>
     */
    public static function provideEscapeIdentifier(): iterable
    {
        yield from [
            // $item, $expected
            'simple'    => ['test', '"test"'],
            'with dots' => ['com.sitedb.web', '"com.sitedb.web"'],
        ];
    }

    public function testConvertTimezoneToOffsetWithOffset(): void
    {
        $db = new MockConnection($this->options);

        // Offset strings should be returned as-is
        $result = $this->getPrivateMethodInvoker($db, 'convertTimezoneToOffset')('+05:30');
        $this->assertSame('+05:30', $result);

        $result = $this->getPrivateMethodInvoker($db, 'convertTimezoneToOffset')('-08:00');
        $this->assertSame('-08:00', $result);

        $result = $this->getPrivateMethodInvoker($db, 'convertTimezoneToOffset')('+00:00');
        $this->assertSame('+00:00', $result);
    }

    public function testConvertTimezoneToOffsetWithNamedTimezone(): void
    {
        $db = new MockConnection($this->options);

        // UTC should always be +00:00
        $result = $this->getPrivateMethodInvoker($db, 'convertTimezoneToOffset')('UTC');
        $this->assertSame('+00:00', $result);

        $result = $this->getPrivateMethodInvoker($db, 'convertTimezoneToOffset')('America/New_York');
        $this->assertContains($result, ['-05:00', '-04:00']); // EST/EDT

        $result = $this->getPrivateMethodInvoker($db, 'convertTimezoneToOffset')('Europe/Paris');
        $this->assertContains($result, ['+01:00', '+02:00']); // CET/CEST

        $result = $this->getPrivateMethodInvoker($db, 'convertTimezoneToOffset')('Asia/Tokyo');
        $this->assertSame('+09:00', $result); // JST (no DST)
    }

    public function testConvertTimezoneToOffsetWithInvalidTimezone(): void
    {
        $db = new MockConnection($this->options);

        $result = $this->getPrivateMethodInvoker($db, 'convertTimezoneToOffset')('Invalid/Timezone');
        $this->assertSame('+00:00', $result);
        $this->assertLogged('error', "Invalid timezone 'Invalid/Timezone'. Falling back to UTC. DateTimeZone::__construct(): Unknown or bad timezone (Invalid/Timezone).");
    }

    public function testGetSessionTimezoneWithFalse(): void
    {
        $options             = $this->options;
        $options['timezone'] = false;
        $db                  = new MockConnection($options);

        $result = $this->getPrivateMethodInvoker($db, 'getSessionTimezone')();
        $this->assertNull($result);
    }

    public function testGetSessionTimezoneWithTrue(): void
    {
        $options             = $this->options;
        $options['timezone'] = true;
        $db                  = new MockConnection($options);

        $result = $this->getPrivateMethodInvoker($db, 'getSessionTimezone')();
        $this->assertSame('+00:00', $result); // UTC = +00:00
    }

    public function testGetSessionTimezoneWithSpecificOffset(): void
    {
        $options             = $this->options;
        $options['timezone'] = '+05:30';
        $db                  = new MockConnection($options);

        $result = $this->getPrivateMethodInvoker($db, 'getSessionTimezone')();
        $this->assertSame('+05:30', $result);
    }

    public function testGetSessionTimezoneWithSpecificNamedTimezone(): void
    {
        $options             = $this->options;
        $options['timezone'] = 'America/Chicago';
        $db                  = new MockConnection($options);

        $result = $this->getPrivateMethodInvoker($db, 'getSessionTimezone')();
        $this->assertContains($result, ['-06:00', '-05:00']);
    }

    public function testGetSessionTimezoneWithoutTimezoneKey(): void
    {
        $db = new MockConnection($this->options);

        $result = $this->getPrivateMethodInvoker($db, 'getSessionTimezone')();
        $this->assertNull($result);
    }

    public function testAfterCommitCallbacksRemainQueuedWhenDriverCommitFails(): void
    {
        $callbacks = [];

        $db = new class ($this->options) extends MockConnection {
            public int $commitAttempts = 0;

            protected function _transCommit(): bool
            {
                $this->commitAttempts++;

                return $this->commitAttempts > 1;
            }
        };

        $this->assertTrue($db->transBegin());
        $db->afterCommit(static function () use (&$callbacks): void {
            $callbacks[] = 'committed';
        });

        $this->assertFalse($db->transCommit());
        $this->assertSame([], $callbacks);
        $this->assertSame(1, $db->transDepth);

        $this->assertTrue($db->transCommit());
        $this->assertSame(['committed'], $callbacks);
        $this->assertSame(0, $db->transDepth);

        $this->assertTrue($db->transBegin());
        $this->assertTrue($db->transCommit());
        $this->assertSame(['committed'], $callbacks);
    }

    public function testAfterRollbackCallbacksRemainQueuedWhenDriverRollbackFails(): void
    {
        $callbacks = [];

        $db = new class ($this->options) extends MockConnection {
            public int $rollbackAttempts = 0;

            protected function _transRollback(): bool
            {
                $this->rollbackAttempts++;

                return $this->rollbackAttempts > 1;
            }
        };

        $this->assertTrue($db->transBegin());
        $db->afterRollback(static function () use (&$callbacks): void {
            $callbacks[] = 'rolled back';
        });

        $this->assertFalse($db->transRollback());
        $this->assertSame([], $callbacks);
        $this->assertSame(1, $db->transDepth);

        $this->assertTrue($db->transRollback());
        $this->assertSame(['rolled back'], $callbacks);
        $this->assertSame(0, $db->transDepth);

        $this->assertTrue($db->transBegin());
        $this->assertTrue($db->transRollback());
        $this->assertSame(['rolled back'], $callbacks);
    }

    public function testCallFunctionDoesNotDoublePrefixAlreadyPrefixedName(): void
    {
        $db = new class ($this->options) extends MockConnection {
            protected function getDriverFunctionPrefix(): string
            {
                return 'str_';
            }
        };

        $this->assertTrue($db->callFunction('str_contains', 'CodeIgniter', 'Ignite'));
    }

    public function testCallFunctionPrefixesUnprefixedName(): void
    {
        $db = new class ($this->options) extends MockConnection {
            protected function getDriverFunctionPrefix(): string
            {
                return 'str_';
            }
        };

        $this->assertTrue($db->callFunction('contains', 'CodeIgniter', 'Ignite'));
    }
}
