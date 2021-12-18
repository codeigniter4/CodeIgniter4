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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class BaseQueryTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new MockConnection([]);
    }

    public function testQueryStoresSQL()
    {
        $query = new Query($this->db);

        $sql = 'SELECT * FROM users';

        $query->setQuery($sql);

        $this->assertSame($sql, $query->getQuery());
    }

    public function testStoresDuration()
    {
        $query = new Query($this->db);

        $start = microtime(true);

        $query->setDuration($start, $start + 5);

        $this->assertSame(5, (int) $query->getDuration());
    }

    public function testGetStartTime()
    {
        $query = new Query($this->db);

        $start = round(microtime(true));

        $query->setDuration($start, $start + 5);

        $this->assertSame($start, $query->getStartTime(true));
    }

    public function testGetStartTimeNumberFormat()
    {
        $query = new Query($this->db);

        $start = microtime(true);

        $query->setDuration($start, $start + 5);

        $this->assertSame(number_format($start, 6), $query->getStartTime());
    }

    public function testsStoresErrorInformation()
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

    public function testSwapPrefix()
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

    public function queryTypes()
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

    /**
     * @dataProvider queryTypes
     *
     * @param mixed $expected
     * @param mixed $sql
     */
    public function testIsWriteType($expected, $sql)
    {
        $query = new Query($this->db);

        $query->setQuery($sql);
        $this->assertSame($expected, $query->isWriteType());
    }

    public function testSingleBindingOutsideOfArray()
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE id = ?', 13);

        $expected = 'SELECT * FROM users WHERE id = 13';

        $this->assertSame($expected, $query->getQuery());
    }

    public function testBindingSingleElementInArray()
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE id = ?', [13]);

        $expected = 'SELECT * FROM users WHERE id = 13';

        $this->assertSame($expected, $query->getQuery());
    }

    public function testBindingMultipleItems()
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE id = ? OR name = ?', [13, 'Vader']);

        $expected = "SELECT * FROM users WHERE id = 13 OR name = 'Vader'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testBindingAutoEscapesParameters()
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE name = ?', ["O'Reilly"]);

        $expected = "SELECT * FROM users WHERE name = 'O''Reilly'";

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5114
     */
    public function testBindingWithTwoColons()
    {
        $query = new Query($this->db);

        $query->setQuery(
            "SELECT mytable.id, DATE_FORMAT(mytable.created_at,'%d/%m/%Y %H:%i:%s') AS created_at_uk FROM mytable WHERE mytable.id = ?",
            [1]
        );

        $expected = "SELECT mytable.id, DATE_FORMAT(mytable.created_at,'%d/%m/%Y %H:%i:%s') AS created_at_uk FROM mytable WHERE mytable.id = 1";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBinds()
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE id = :id: OR name = :name:', ['id' => 13, 'name' => 'Geoffrey']);

        $expected = "SELECT * FROM users WHERE id = 13 OR name = 'Geoffrey'";

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3566
     */
    public function testNamedBindsWithColonElsewhere()
    {
        $query = new Query($this->db);
        $query->setQuery('SELECT `email`, @total:=(total+1) FROM `users` WHERE `id` = :id:', ['id' => 10]);

        $sql = 'SELECT `email`, @total:=(total+1) FROM `users` WHERE `id` = 10';
        $this->assertSame($sql, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/5138
     */
    public function testNamedBindsWithBindMarkerElsewhere()
    {
        $query = new Query($this->db);
        $query->setQuery('SELECT * FROM posts WHERE id = :id: AND title = \'The default bind marker is "?"\'', ['id' => 10]);

        $sql = 'SELECT * FROM posts WHERE id = 10 AND title = \'The default bind marker is "?"\'';
        $this->assertSame($sql, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/5138
     */
    public function testSimpleBindsWithNamedBindPlaceholderElsewhere()
    {
        $query = new Query($this->db);
        $query->setQuery('SELECT * FROM posts WHERE id = ? AND title = \'A named bind placeholder looks like ":foobar:"\'', 10);

        $sql = 'SELECT * FROM posts WHERE id = 10 AND title = \'A named bind placeholder looks like ":foobar:"\'';
        $this->assertSame($sql, $query->getQuery());
    }

    /**
     * @group single
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/201
     */
    public function testSimilarNamedBinds()
    {
        $query = new Query($this->db);

        $query->setQuery('SELECT * FROM users WHERE sitemap = :sitemap: OR site = :site:', ['sitemap' => 'sitemap', 'site' => 'site']);

        $expected = "SELECT * FROM users WHERE sitemap = 'sitemap' OR site = 'site'";

        $this->assertSame($expected, $query->getQuery());
    }

    public function testNamedBindsDontGetReplacedAgain()
    {
        $query = new Query($this->db);

        $query->setQuery(
            'SELECT * FROM posts WHERE content = :content: OR foobar = :foobar:',
            ['content' => 'a placeholder looks like :foobar:', 'foobar' => 'bazqux']
        );

        $expected = "SELECT * FROM posts WHERE content = 'a placeholder looks like :foobar:' OR foobar = 'bazqux'";

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1705
     */
    public function testSetQueryBindsWithSetEscapeTrue()
    {
        $query = new Query($this->db);

        $query->setQuery('UPDATE user_table SET `x` = NOW() WHERE `id` = :id:', ['id' => 22], true);

        $expected = 'UPDATE user_table SET `x` = NOW() WHERE `id` = 22';

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1705
     */
    public function testSetQueryBindsWithSetEscapeFalse()
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
    public function testSetQueryBindsWithSetEscapeNegativeIntegers()
    {
        $query = new Query($this->db);

        $query->setQuery(
            'SELECT * FROM product WHERE date_pickup < DateAdd(month, ?, Convert(date, GetDate())',
            [-6],
            true
        );

        $expected = 'SELECT * FROM product WHERE date_pickup < DateAdd(month, -6, Convert(date, GetDate())';

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSetQueryNamedBindsWithNegativeIntegers()
    {
        $query = new Query($this->db);

        $query->setQuery(
            'SELECT * FROM product WHERE date_pickup < DateAdd(month, :num:, Convert(date, GetDate())',
            ['num' => -6]
        );

        $expected = 'SELECT * FROM product WHERE date_pickup < DateAdd(month, -6, Convert(date, GetDate())';

        $this->assertSame($expected, $query->getQuery());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2762
     */
    public function testSetQueryBinds()
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

    public function queryKeywords()
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

    /**
     * @dataProvider queryKeywords
     *
     * @param mixed $expected
     * @param mixed $sql
     */
    public function testHighlightQueryKeywords($expected, $sql)
    {
        $query = new Query($this->db);
        $query->setQuery($sql);
        $query->getQuery();

        $this->assertSame($expected, $query->debugToolbarDisplay());
    }
}
