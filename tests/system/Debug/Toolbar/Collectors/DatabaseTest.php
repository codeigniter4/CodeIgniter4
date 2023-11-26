<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

use CodeIgniter\Database\Query;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @group Others
 */
final class DatabaseTest extends CIUnitTestCase
{
    public function testDisplay(): void
    {
        /** @var MockObject&Query $query */
        $query = $this->createMock(Query::class);

        // set mock returns
        $query->method('getQuery')->willReturn('SHOW TABLES;');
        $query->method('debugToolbarDisplay')->willReturn('<strong>SHOW</strong> TABLES;');
        $query->method('getDuration')->with(5)->willReturn('1.23456');

        Database::collect($query); // <== $query will be called here
        $collector = new Database();

        $queries = $collector->display()['queries'];

        $this->assertSame('1234.56 ms', $queries[0]['duration']);
        $this->assertSame('<strong>SHOW</strong> TABLES;', $queries[0]['sql']);
        $this->assertSame(clean_path(__FILE__) . ':' . (__LINE__ - 7), $queries[0]['trace-file']);

        foreach ($queries[0]['trace'] as $i => $trace) {
            // since we added the index numbering
            $this->assertArrayHasKey('index', $trace);
            $this->assertSame(
                sprintf('%s', $i + 1),
                preg_replace(sprintf('/%s/', chr(0xC2) . chr(0xA0)), '', $trace['index'])
            );

            // since we merged file & line together
            $this->assertArrayNotHasKey('line', $trace);
            $this->assertArrayHasKey('file', $trace);

            // since we dropped object & args in the backtrace for performance
            // but args MAY STILL BE present in internal calls
            $this->assertArrayNotHasKey('object', $trace);
        }
    }
}
