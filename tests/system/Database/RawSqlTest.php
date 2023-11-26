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

/**
 * @internal
 *
 * @group Others
 */
final class RawSqlTest extends CIUnitTestCase
{
    public function testCanConvertToString(): void
    {
        $expected = 'REGEXP_SUBSTR(ral_anno,"[0-9]{1,2}([,.][0-9]{1,3})([,.][0-9]{1,3})") AS ral';
        $rawSql   = new RawSql($expected);

        $this->assertSame($expected, (string) $rawSql);
    }

    public function testCanCreateNewObject(): void
    {
        $firstSql = 'a = 1 AND b = 2';
        $rawSql   = new RawSql($firstSql);

        $secondSql = 'a = 1 AND b = 2 OR c = 3';
        $newRawSQL = $rawSql->with($secondSql);

        $this->assertSame($firstSql, (string) $rawSql);
        $this->assertSame($secondSql, (string) $newRawSQL);
    }

    public function testGetBindingKey(): void
    {
        $firstSql = 'a = 1 AND b = 2';
        $rawSql   = new RawSql($firstSql);

        $key = $rawSql->getBindingKey();

        $this->assertMatchesRegularExpression('/\ARawSql[0-9]+\z/', $key);
    }
}
