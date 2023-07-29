<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class EscapeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    private string $char;

    protected function setUp(): void
    {
        parent::setUp();

        $this->char = $this->db->DBDriver === 'MySQLi' ? '\\' : "'";
    }

    /**
     * Ensures we don't have escaped - values...
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/606
     */
    public function testDoesNotEscapeNegativeNumbers(): void
    {
        $this->assertSame(-100, $this->db->escape(-100));
    }

    public function testEscape(): void
    {
        $expected = "SELECT * FROM brands WHERE name = 'O" . $this->char . "'Doules'";
        $sql      = 'SELECT * FROM brands WHERE name = ' . $this->db->escape("O'Doules");

        $this->assertSame($expected, $sql);
    }

    public function testEscapeString(): void
    {
        $expected = "SELECT * FROM brands WHERE name = 'O" . $this->char . "'Doules'";
        $sql      = "SELECT * FROM brands WHERE name = '" . $this->db->escapeString("O'Doules") . "'";

        $this->assertSame($expected, $sql);
    }

    public function testEscapeLikeString(): void
    {
        $expected = "SELECT * FROM brands WHERE column LIKE '%10!% more%' ESCAPE '!'";
        $sql      = "SELECT * FROM brands WHERE column LIKE '%" . $this->db->escapeLikeString('10% more') . "%' ESCAPE '!'";

        $this->assertSame($expected, $sql);
    }

    public function testEscapeLikeStringDirect(): void
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $expected = "SHOW COLUMNS FROM brands WHERE column LIKE 'wild\\_chars%'";
            $sql      = "SHOW COLUMNS FROM brands WHERE column LIKE '" . $this->db->escapeLikeStringDirect('wild_chars') . "%'";

            $this->assertSame($expected, $sql);
        } else {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testEscapeStringArray(): void
    {
        $stringArray = [' A simple string ', new RawSql('CURRENT_TIMESTAMP()'), false, null];

        $escapedString = $this->db->escape($stringArray);

        $this->assertSame("' A simple string '", $escapedString[0]);
        $this->assertSame('CURRENT_TIMESTAMP()', $escapedString[1]);

        if ($this->db->DBDriver === 'Postgre') {
            $this->assertSame('FALSE', $escapedString[2]);
        } else {
            $this->assertSame(0, $escapedString[2]);
        }

        $this->assertSame('NULL', $escapedString[3]);
    }
}
