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

use CodeIgniter\Database\MySQLi\Connection as MySQLiConnection;
use CodeIgniter\Database\Postgre\Connection as PostgreConnection;
use CodeIgniter\Database\SQLite3\Connection as SQLite3Connection;
use CodeIgniter\Database\SQLSRV\Connection as SQLSRVConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Enum\RoleEnum;
use Tests\Support\Enum\StatusEnum;

/**
 * @internal
 */
#[Group('Others')]
final class BaseQueryTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new MockConnection([]);
    }

    public function testQueryStoresSQL(): void
    {
        $query = new Query($this->db);

        $sql = 'SELECT * FROM users';

        $query->setQuery($sql);

        $this->assertSame($sql, $query->getQuery());
    }

    public function testStoresDuration(): void
    {
        $query = new Query($this->db);

        $start = microtime(true);

        $query->setDuration($start, $start + 5);

        $this->assertSame(5, (int) $query->getDuration());
    }

    public function testGetStartTime(): void
    {
        $query = new Query($this->db);

        $start = round(microtime(true));

        $query->setDuration($start, $start + 5);

        $this->assertSame($start, $query->getStartTime(true));
    }

    public function testGetStartTimeNumberFormat(): void
    {
        $query = new Query($this->db);

        $start = microtime(true);

        $query->setDuration($start, $start + 5);

        $this->assertSame(number_format($start, 6), $query->getStartTime());
    }

    public function testsStoresErrorInformation(): void
    {
        $query = new Query($this->db);

        $code = 13;
        $msg  = 'Oops, yo!';

        $this->assertFalse($query->hasError());

        $query->setError($code, $msg);
        $this->assertTrue($query->hasError());
        $this->assertSame($code, $query->getErrorCode());
        $this->assertSame($msg, $query->getErrorMessage());
    }

    public function testSwapPrefix(): void
    {
        $query = new Query($this->db);

        $origPrefix = 'db_';
        $newPrefix  = 'ci_';

        $origSQL = 'SELECT * FROM db_users WHERE db_users.id = 1';
        $newSQL  = 'SELECT * FROM ci_users WHERE ci_users.id = 1';

        $query->setQuery($origSQL);
        $query->swapPrefix($origPrefix, $newPrefix);

        $this->assertSame($newSQL, $query->getQuery());
    }

    #[DataProvider('provideIsWriteType')]
    public function testIsWriteType(bool $expected, string $sql): void
    {
        $query = new Query($this->db);

        $query->setQuery($sql);
        $this->assertSame($expected, $query->isWriteType());
    }

    /**
     * @return iterable<string, array{bool, string}>
     */
    public static function provideIsWriteType(): iterable
    {
        return [
            'select' => [
                false,
                'SELECT * FROM users',
            ],
            'set' => [
                true,
                'SET ...',
            ],
            'insert' => [
                true,
                'INSERT INTO ...',
            ],
            'update' => [
                true,
                'UPDATE ...',
            ],
            'delete' => [
                true,
                'DELETE ...',
            ],
            'replace' => [
                true,
                'REPLACE ...',
            ],
            'create' => [
                true,
                'CREATE ...',
            ],
            'drop' => [
                true,
                'DROP ...',
            ],
            'truncate' => [
                true,
                'TRUNCATE ...',
            ],
            'load' => [
                true,
                'LOAD ...',
            ],
            'copy' => [
                true,
                'COPY ...',
            ],
            'alter' => [
                true,
                'ALTER ...',
            ],
            'rename' => [
                true,
                'RENAME ...',
            ],
            'grant' => [
                true,
                'GRANT ...',
            ],
            'revoke' => [
                true,
                'REVOKE ...',
            ],
            'lock' => [
                true,
                'LOCK ...',
            ],
            'unlock' => [
                true,
                'UNLOCK ...',
            ],
            'reindex' => [
                true,
                'REINDEX ...',
            ],
        ];
    }

    public function testSingleBindingOutsideOfArray(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE id = ?', 13);

        $expected = 'SELECT * FROM users WHERE id = 13';

        $this->assertSame($expected, $query->getQuery());
    }

    public function testBindingSingleElementInArray(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE id = ?', [13]);

        $expected = 'SELECT * FROM users WHERE id = 13';

        $this->assertSame($expected, $query->getQuery());
    }

    public function testBindingMultipleItems(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE id = ? OR name = ?', [13, 'Vader']);

        $expected = "SELECT * FROM users WHERE id = 13 OR name = 'Vader'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testBindingAutoEscapesParameters(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE name = ?', ["O'Reilly"]);

        $expected = "SELECT * FROM users WHERE name = 'O''Reilly'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testBindingBackedEnum(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE status = ? AND role = ?', [StatusEnum::ACTIVE, RoleEnum::ADMIN]);

        $expected = "SELECT * FROM users WHERE status = 'active' AND role = 2";

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5114
     */
    public function testBindingWithTwoColons(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            "SELECT mytable.id, DATE_FORMAT(mytable.created_at,'%d/%m/%Y %H:%i:%s') AS created_at_uk FROM mytable WHERE mytable.id = ?",
            [1],
        );

        $expected = "SELECT mytable.id, DATE_FORMAT(mytable.created_at,'%d/%m/%Y %H:%i:%s') AS created_at_uk FROM mytable WHERE mytable.id = 1";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBinds(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE id = :id: OR name = :name:', ['id' => 13, 'name' => 'Geoffrey']);

        $expected = "SELECT * FROM users WHERE id = 13 OR name = 'Geoffrey'";

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3566
     */
    public function testNamedBindsWithColonElsewhere(): void
    {
        $query = new Query($this->db);
        $query->setQuery('SELECT `email`, @total:=(total+1) FROM `users` WHERE `id` = :id:', ['id' => 10]);

        $sql = 'SELECT `email`, @total:=(total+1) FROM `users` WHERE `id` = 10';
        $this->assertSame($sql, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/5138
     */
    public function testNamedBindsWithBindMarkerElsewhere(): void
    {
        $query = new Query($this->db);
        $query->setQuery('SELECT * FROM posts WHERE id = :id: AND title = \'The default bind marker is "?"\'', ['id' => 10]);

        $sql = 'SELECT * FROM posts WHERE id = 10 AND title = \'The default bind marker is "?"\'';
        $this->assertSame($sql, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/5138
     */
    public function testSimpleBindsWithNamedBindPlaceholderElsewhere(): void
    {
        $query = new Query($this->db);
        $query->setQuery('SELECT * FROM posts WHERE id = ? AND title = \'A named bind placeholder looks like ":foobar:"\'', 10);

        $sql = 'SELECT * FROM posts WHERE id = 10 AND title = \'A named bind placeholder looks like ":foobar:"\'';
        $this->assertSame($sql, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/201
     */
    public function testSimilarNamedBinds(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE sitemap = :sitemap: OR site = :site:', ['sitemap' => 'sitemap', 'site' => 'site']);

        $expected = "SELECT * FROM users WHERE sitemap = 'sitemap' OR site = 'site'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsDontGetReplacedAgain(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            'SELECT * FROM posts WHERE content = :content: OR foobar = :foobar:',
            ['content' => 'a placeholder looks like :foobar:', 'foobar' => 'bazqux'],
        );

        $expected = "SELECT * FROM posts WHERE content = 'a placeholder looks like :foobar:' OR foobar = 'bazqux'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsAreNotReplacedInsideEscapedValues(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            'SELECT * FROM users WHERE name = ' . $this->db->escape(':status:') . ' AND status = :status:',
            ['status' => ' OR is_admin = 1 OR '],
        );

        $expected = "SELECT * FROM users WHERE name = ':status:' AND status = ' OR is_admin = 1 OR '";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsAreNotReplacedAfterEscapedValueEndingWithBackslash(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            'SELECT * FROM users WHERE a = ' . $this->db->escape('x\\') . ' AND name = ' . $this->db->escape(':status:') . ' AND status = :status:',
            ['status' => ' OR is_admin = 1 OR '],
        );

        $expected = "SELECT * FROM users WHERE a = 'x\\' AND name = ':status:' AND status = ' OR is_admin = 1 OR '";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsAreNotReplacedInsideBackslashEscapedValues(): void
    {
        $db = new class ([]) extends MockConnection {
            protected function _escapeString(string $str): string
            {
                return addslashes($str);
            }
        };

        $query = new Query($db);

        $query->setQuery(
            'SELECT * FROM users WHERE name = ' . $db->escape("it's :status:") . ' AND status = :status:',
            ['status' => ' OR is_admin = 1 OR '],
        );

        $expected = "SELECT * FROM users WHERE name = 'it\\'s :status:' AND status = ' OR is_admin = 1 OR '";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsAreNotReplacedInsidePostgreEscapeStrings(): void
    {
        // Emulates standard_conforming_strings = on, where only E'...'
        // strings use backslash escapes.
        $db = new class ([]) extends PostgreConnection {
            protected function _escapeString(string $str): string
            {
                return $str;
            }
        };

        $query = new Query($db);

        $query->setQuery(
            "SELECT * FROM users WHERE name = E'it\\'s :status:' AND status = :status:",
            ['status' => [5, false]],
            false,
        );

        $expected = "SELECT * FROM users WHERE name = E'it\\'s :status:' AND status = 5";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsEscapeStringsAreOnlyRecognizedOnPostgre(): void
    {
        $query = new Query($this->db);

        // Outside PostgreSQL, E'...' is a regular string, so the backslash
        // does not escape the quote and the string ends after "it\".
        $query->setQuery(
            "SELECT * FROM users WHERE name = E'it\\' AND status = :status:",
            ['status' => 5],
        );

        $expected = "SELECT * FROM users WHERE name = E'it\\' AND status = 5";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsAreNotReplacedInsideMySQLBackslashEscapedDoubleQuotedStrings(): void
    {
        $db = $this->createBackslashEscapingMySQLiConnection('utf8mb4');

        $query = new Query($db);

        $query->setQuery(
            'SELECT * FROM users WHERE name = "it\\"s :status:" AND status = :status:',
            ['status' => ' OR 1=1 OR '],
        );

        $expected = "SELECT * FROM users WHERE name = \"it\\\"s :status:\" AND status = ' OR 1=1 OR '";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsWithPostgreQuotedIdentifierEndingWithBackslash(): void
    {
        // Emulates standard_conforming_strings = off, where a backslash
        // escapes inside '...' strings but never inside "..." identifiers.
        $db = new class ([]) extends PostgreConnection {
            protected function _escapeString(string $str): string
            {
                return addcslashes($str, "\\'");
            }
        };

        $query = new Query($db);

        $query->setQuery(
            'SELECT "a\\" FROM users WHERE name = \':id:\' AND id = :id:',
            ['id' => [1, false]],
            false,
        );

        $this->assertSame('SELECT "a\\" FROM users WHERE name = \':id:\' AND id = 1', $query->getQuery());
    }

    public function testNamedBindsAreNotReplacedInsideStringLiterals(): void
    {
        $query = new Query($this->db);

        $query->setQuery("SELECT * FROM users WHERE name LIKE '%:name:%' OR name = :name:", ['name' => 'Geoffrey']);

        $expected = "SELECT * FROM users WHERE name LIKE '%:name:%' OR name = 'Geoffrey'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsWithQuotesInPlaceholder(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            "SELECT * FROM users WHERE CONCAT(first, ' ', last) = :CONCAT(first, ' ', last):",
            ["CONCAT(first, ' ', last)" => 'John Doe'],
        );

        $expected = "SELECT * FROM users WHERE CONCAT(first, ' ', last) = 'John Doe'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsWithQuotesInIdentifiersAndComments(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            "SELECT \"it's\", `it's` /* it's :id: */ FROM users -- it's :id:\nWHERE id = :id:",
            ['id' => 1],
        );

        $expected = "SELECT \"it's\", `it's` /* it's :id: */ FROM users -- it's :id:\nWHERE id = 1";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsWithDoubleColonAndStringLiteral(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            "SELECT id::text, 'a:b:c' FROM users WHERE created_at::date = :date: AND x::int = :x::int:",
            ['date' => '2026-01-01', 'x::int' => 5],
        );

        $expected = "SELECT id::text, 'a:b:c' FROM users WHERE created_at::date = '2026-01-01' AND x::int = 5";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsWithQuotesInSQLSRVBracketIdentifiers(): void
    {
        $query = new Query(new SQLSRVConnection([]));

        $query->setQuery("SELECT [it's] FROM users WHERE id = :id: AND name = ':id:'", ['id' => 1]);

        $expected = "SELECT [it's] FROM users WHERE id = 1 AND name = ':id:'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsAreNotReplacedInsideSQLSRVBracketIdentifiers(): void
    {
        $query = new Query(new SQLSRVConnection([]));

        $query->setQuery('SELECT [:id:] FROM users WHERE id = :id:', ['id' => 1]);

        $this->assertSame('SELECT [:id:] FROM users WHERE id = 1', $query->getQuery());
    }

    public function testNamedBindsWithSQLiteBracketIdentifiers(): void
    {
        $query = new Query(new SQLite3Connection([]));

        $query->setQuery("SELECT [it's], [:id:] FROM users WHERE id = :id: AND name = ':id:'", ['id' => 1]);

        $expected = "SELECT [it's], [:id:] FROM users WHERE id = 1 AND name = ':id:'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsWithPostgreDollarQuotedStrings(): void
    {
        $query = new Query(new PostgreConnection([]));

        $query->setQuery(
            "SELECT \$\$it's :id:\$\$ AS a, \$tag\$it's :id:\$tag\$ AS b, :id: AS id",
            ['id' => [1, false]],
            false,
        );

        $expected = "SELECT \$\$it's :id:\$\$ AS a, \$tag\$it's :id:\$tag\$ AS b, 1 AS id";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsAreNotReplacedInsideNestedComments(): void
    {
        foreach ([new PostgreConnection([]), new SQLSRVConnection([])] as $db) {
            $query = new Query($db);

            $query->setQuery(
                "SELECT 1 /* outer /* it's */ :id: */ FROM users WHERE id = :id: /*!50000 :id: */",
                ['id' => [1, false]],
                false,
            );

            $expected = "SELECT 1 /* outer /* it's */ :id: */ FROM users WHERE id = 1 /*!50000 :id: */";

            $this->assertSame($expected, $query->getQuery(), $db::class);
        }
    }

    #[DataProvider('provideNamedBindsWithMySQLMultibyteCharsets')]
    public function testNamedBindsWithMySQLMultibyteCharsets(string $charset, string $char): void
    {
        $db = $this->createBackslashEscapingMySQLiConnection($charset);

        $query = new Query($db);

        // mysqli::real_escape_string() is charset-aware and does not escape
        // the trailing 0x5C byte of a multibyte character.
        $query->setQuery(
            "SELECT * FROM users WHERE a = 'x{$char}' AND name = ':status:' AND status = :status:",
            ['status' => ' OR 1=1 OR '],
        );

        $expected = "SELECT * FROM users WHERE a = 'x{$char}' AND name = ':status:' AND status = ' OR 1=1 OR '";

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideNamedBindsWithMySQLMultibyteCharsets(): iterable
    {
        yield 'big5' => ['big5', "\xb3\x5c"];

        yield 'cp932' => ['cp932', "\x95\x5c"];

        yield 'gb18030' => ['gb18030', "\x81\x5c"];

        yield 'gbk' => ['gbk', "\xbf\x5c"];

        yield 'sjis' => ['SJIS', "\x95\x5c"];
    }

    public function testNamedBindsWithMySQLInvalidMultibyteSequence(): void
    {
        $db = $this->createBackslashEscapingMySQLiConnection('gbk');

        $query = new Query($db);

        // 0xBF is a GBK lead byte, but a quote is not a valid trailing byte,
        // so MySQL ends the string literal at that quote.
        $query->setQuery(
            "SELECT * FROM users WHERE a = 'x\xbf' AND name = ':status:' AND status = :status:",
            ['status' => ' OR 1=1 OR '],
        );

        $expected = "SELECT * FROM users WHERE a = 'x\xbf' AND name = ':status:' AND status = ' OR 1=1 OR '";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsWithMySQLUtf8DoesNotSkipMultibyteSequences(): void
    {
        $db = $this->createBackslashEscapingMySQLiConnection('utf8mb4');

        $query = new Query($db);

        $query->setQuery("SELECT * FROM users WHERE a = 'x\xbf\\' AND b = ':id:' AND id = :id:", ['id' => 1]);

        $expected = "SELECT * FROM users WHERE a = 'x\xbf\\' AND b = '1' AND id = :id:";

        $this->assertSame($expected, $query->getQuery());
    }

    #[DataProvider('provideNamedBindsWithPostgreMultibyteCharsets')]
    public function testNamedBindsWithPostgreMultibyteCharsets(string $charset, string $char, string $suffix): void
    {
        $db = new class (['charset' => $charset]) extends PostgreConnection {
            protected function _escapeString(string $str): string
            {
                return $str;
            }
        };

        $query = new Query($db);

        // pg_escape_literal() creates E'...' strings for values with a backslash.
        $query->setQuery(
            "SELECT * FROM users WHERE a = E'\\\\x{$char}{$suffix}' AND name = ':status:' AND status = :status:",
            ['status' => [5, false]],
            false,
        );

        $expected = "SELECT * FROM users WHERE a = E'\\\\x{$char}{$suffix}' AND name = ':status:' AND status = 5";

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function provideNamedBindsWithPostgreMultibyteCharsets(): iterable
    {
        yield 'BIG5' => ['BIG5', "\xb3\x5c", ''];

        yield 'GB18030' => ['GB18030', "\x81\x5c", ''];

        yield 'GBK' => ['GBK', "\xbf\x5c", ''];

        yield 'Windows936' => ['Windows936', "\xbf\x5c", ''];

        yield 'SJIS' => ['SJIS', "\x95\x5c", ''];

        yield 'Shift_JIS_2004' => ['Shift_JIS_2004', "\x95\x5c", ''];

        yield 'SJIS half-width katakana' => ['SJIS', "\xb1", '\\\\'];
    }

    public function testNamedBindsWithMySQLComments(): void
    {
        $query = new Query(new MySQLiConnection([]));

        $query->setQuery(
            "SELECT 10--2, ':id:' FROM users # it's\nWHERE id = :id: -- it's\nAND x = :x: /* /* it's */ /*!50000 AND y = :x: */",
            ['id' => 1, 'x' => 2],
        );

        $expected = "SELECT 10--2, ':id:' FROM users # it's\nWHERE id = 1 -- it's\nAND x = 2 /* /* it's */ /*!50000 AND y = 2 */";

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1705
     */
    public function testSetQueryBindsWithSetEscapeTrue(): void
    {
        $query = new Query($this->db);

        $query->setQuery('UPDATE user_table SET `x` = NOW() WHERE `id` = :id:', ['id' => 22], true);

        $expected = 'UPDATE user_table SET `x` = NOW() WHERE `id` = 22';

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1705
     */
    public function testSetQueryBindsWithSetEscapeFalse(): void
    {
        $query = new Query($this->db);

        // The only time setQuery is called with setEscape = false
        // is when the query builder has already stored the escaping info...
        $binds = [
            'id' => [
                22,
                1,
            ],
        ];

        $query->setQuery('UPDATE user_table SET `x` = NOW() WHERE `id` = :id:', $binds, false);

        $expected = 'UPDATE user_table SET `x` = NOW() WHERE `id` = 22';

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4973
     */
    public function testSetQueryBindsWithSetEscapeNegativeIntegers(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            'SELECT * FROM product WHERE date_pickup < DateAdd(month, ?, Convert(date, GetDate())',
            [-6],
            true,
        );

        $expected = 'SELECT * FROM product WHERE date_pickup < DateAdd(month, -6, Convert(date, GetDate())';

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSetQueryNamedBindsWithNegativeIntegers(): void
    {
        $query = new Query($this->db);

        $query->setQuery(
            'SELECT * FROM product WHERE date_pickup < DateAdd(month, :num:, Convert(date, GetDate())',
            ['num' => -6],
        );

        $expected = 'SELECT * FROM product WHERE date_pickup < DateAdd(month, -6, Convert(date, GetDate())';

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2762
     */
    public function testSetQueryBinds(): void
    {
        $query = new Query($this->db);

        $binds = [
            1,
            2,
        ];

        $query->setQuery('SELECT @factorA := ?, @factorB := ?', $binds);

        $expected = 'SELECT @factorA := 1, @factorB := 2';

        $this->assertSame($expected, $query->getQuery());
    }

    public function testGetQueryMultipleTimes(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE name = :name:', ['name' => 'placeholder :name: in value']);

        $expected = "SELECT * FROM users WHERE name = 'placeholder :name: in value'";

        // Triggers compileBinds()
        $query->getQuery();

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSetBindsMultipleTimes(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE name = :name:', ['name' => 'John']);

        // Triggers compileBinds()
        $query->getQuery();

        $query->setBinds(['name' => 'Jane']);

        $expected = "SELECT * FROM users WHERE name = 'Jane'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSetQueryMultipleTimesKeepingBinds(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE name = :name:', ['name' => 'John']);

        // Triggers compileBinds()
        $query->getQuery();

        $query->setQuery('SELECT * FROM users WHERE name = :name: OR id = 1');

        $expected = "SELECT * FROM users WHERE name = 'John' OR id = 1";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSetQueryMultipleTimesReplacingBinds(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE name = :name:', ['name' => 'John']);

        // Triggers compileBinds()
        $query->getQuery();

        $query->setQuery('SELECT * FROM users WHERE name = :name: OR id = 1', ['name' => 'Jane']);

        $expected = "SELECT * FROM users WHERE name = 'Jane' OR id = 1";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSetQueryMultipleTimesRemovingBinds(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE name = :name:', ['name' => 'John']);

        // Triggers compileBinds()
        $query->getQuery();

        $query->setQuery('SELECT * FROM users WHERE name = :name: OR id = 1', []);

        $expected = 'SELECT * FROM users WHERE name = :name: OR id = 1';

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSwapPrefixMultipleTimes(): void
    {
        $query = new Query($this->db);

        $origPrefix = 'db_';
        $newPrefix1 = 'ci_';
        $newPrefix2 = 'zz_';

        $origSQL = 'SELECT * FROM db_users WHERE db_users.id = 1';
        $newSQL  = 'SELECT * FROM zz_users WHERE zz_users.id = 1';

        $query->setQuery($origSQL);
        $query->swapPrefix($origPrefix, $newPrefix1);
        $query->swapPrefix($newPrefix1, $newPrefix2);

        $this->assertSame($newSQL, $query->getQuery());
    }

    public function testSwapPrefixBeforeSetBinds(): void
    {
        $query = new Query($this->db);

        $origSQL = 'SELECT * FROM db_users WHERE db_users.name = ?';
        $newSQL  = "SELECT * FROM ci_users WHERE ci_users.name = 'John'";

        $query->setQuery($origSQL);

        $query->swapPrefix('db_', 'ci_');
        $query->setBinds(['John']);

        $this->assertSame($newSQL, $query->getQuery());
    }

    public function testSwapPrefixAfterSetBinds(): void
    {
        $query = new Query($this->db);

        $origSQL = 'SELECT * FROM db_users WHERE db_users.name = ?';
        $newSQL  = "SELECT * FROM ci_users WHERE ci_users.name = 'John'";

        $query->setQuery($origSQL);

        $query->setBinds(['John']);
        $query->swapPrefix('db_', 'ci_');

        $this->assertSame($newSQL, $query->getQuery());
    }

    public function testSwapPrefixIsAppliedBeforeBinds(): void
    {
        $query = new Query($this->db);

        $origSQL = 'SELECT * FROM db_users WHERE db_users.name = ?';
        $newSQL  = "SELECT * FROM ci_users WHERE ci_users.name = 'John db_foobar John'";

        $query->setQuery($origSQL);

        $query->setBinds(['John db_foobar John']);
        $query->swapPrefix('db_', 'ci_');

        $this->assertSame($newSQL, $query->getQuery());
    }

    public function testSwapPrefixAfterGetQuery(): void
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM db_users WHERE db_users.id = 1');

        // Triggers compileBinds()
        $query->getQuery();

        $query->swapPrefix('db_', 'ci_');

        $expected = 'SELECT * FROM ci_users WHERE ci_users.id = 1';

        $this->assertSame($expected, $query->getQuery());
    }

    #[DataProvider('provideHighlightQueryKeywords')]
    public function testHighlightQueryKeywords(string $expected, string $sql): void
    {
        $query = new Query($this->db);
        $query->setQuery($sql);

        $this->assertSame($expected, $query->debugToolbarDisplay());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideHighlightQueryKeywords(): iterable
    {
        return [
            'highlightKeyWords' => [
                '<strong>SELECT</strong> `a`.*, `b`.`id` <strong>AS</strong> `b_id` <strong>FROM</strong> `a` <strong>LEFT</strong> <strong>JOIN</strong> `b` <strong>ON</strong> `b`.`a_id` = `a`.`id` <strong>WHERE</strong> `b`.`id` <strong>IN</strong> (&#039;1&#039;) <strong>AND</strong> `a`.`deleted_at` <strong>IS</strong> <strong>NOT</strong> <strong>NULL</strong> <strong>LIMIT</strong> 1',
                'SELECT `a`.*, `b`.`id` AS `b_id` FROM `a` LEFT JOIN `b` ON `b`.`a_id` = `a`.`id` WHERE `b`.`id` IN (\'1\') AND `a`.`deleted_at` IS NOT NULL LIMIT 1',
            ],
            'ignoreKeyWordsInValues' => [
                '<strong>SELECT</strong> * <strong>FROM</strong> `a` <strong>WHERE</strong> `a`.`col` = &#039;SELECT escaped keyword in value&#039; <strong>LIMIT</strong> 1',
                'SELECT * FROM `a` WHERE `a`.`col` = \'SELECT escaped keyword in value\' LIMIT 1',
            ],
            'escapeHtmlValues' => [
                '<strong>SELECT</strong> &#039;&lt;s&gt;&#039; <strong>FROM</strong> dual',
                'SELECT \'<s>\' FROM dual',
            ],
        ];
    }

    private function createBackslashEscapingMySQLiConnection(string $charset): MySQLiConnection
    {
        return new class (['charset' => $charset]) extends MySQLiConnection {
            protected function _escapeString(string $str): string
            {
                return addcslashes($str, "\\'");
            }
        };
    }
}
