<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Forge;

use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class CreateTableTest extends CIUnitTestCase
{
    public function testCreateTableWithExists()
    {
        $dbMock = $this->getMockBuilder(MockConnection::class)
            ->setConstructorArgs([[]])
            ->onlyMethods(['listTables'])
            ->getMock();
        $dbMock->expects($this->any())
            ->method('listTables')
            ->willReturn(['foo']);

        $forge                          = new class ($dbMock) extends Forge {
            protected $createTableIfStr = false;
        };

        $forge->addField('id');
        $actual = $forge->createTable('foo', true);

        $this->assertTrue($actual);
    }
}
