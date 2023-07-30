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
use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class CreateTableTest extends CIUnitTestCase
{
    public function testCreateTableWithDefaultRawSql(): void
    {
        $sql = <<<'SQL'
            CREATE TABLE "foo" (
            	"id" INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
            	"ts" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
            )
            SQL;
        $dbMock = $this->getMockBuilder(MockConnection::class)
            ->setConstructorArgs([[]])
            ->onlyMethods(['query'])
            ->getMock();
        $dbMock
            ->method('query')
            ->with($sql)
            ->willReturn(true);

        $forge = new class ($dbMock) extends Forge {};

        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ts' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ];
        $forge->addField($fields);
        $actual = $forge->createTable('foo');

        $this->assertTrue($actual);
    }
}
