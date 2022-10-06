<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Log\Exceptions\LogException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @internal
 */
final class ErrorlogHandlerTest extends CIUnitTestCase
{
    public function testHandlerThrowsOnInvalidMessageType(): void
    {
        $this->expectException(LogException::class);
        $this->getMockedHandler(['messageType' => 2]);
    }

    public function testErrorLoggingWithErrorLog(): void
    {
        $logger = $this->getMockedHandler(['handles' => ['critical', 'error']]);
        $logger->method('errorLog')->willReturn(true);
        $logger->expects($this->once())->method('errorLog')->with("ERROR --> Test message.\n", 0);
        $this->assertTrue($logger->handle('error', 'Test message.'));
    }

    public function testErrorLoggingWithArray(): void
    {
        $logger = $this->getMockedHandler(['handles' => ['critical', 'error']]);
        $logger->method('errorLog')->willReturn(true);
        $logger->expects($this->once())->method('errorLog')->with("ERROR --> Array\n(
    [firstName] => John
    [lastName] => Doe\n)\n\n", 0);
        $this->assertTrue($logger->handle('error', ['firstName' => 'John', 'lastName' => 'Doe']));
    }

    public function testErrorLoggingWithInteger(): void
    {
        $logger = $this->getMockedHandler(['handles' => ['critical', 'error']]);
        $logger->method('errorLog')->willReturn(true);
        $logger->expects($this->once())->method('errorLog')->with("ERROR --> 123456\n", 0);
        $this->assertTrue($logger->handle('error', 123456));
    }

    public function testErrorLoggingWithObject(): void
    {
        $logger = $this->getMockedHandler(['handles' => ['critical', 'error']]);

        $obj            = new stdClass();
        $obj->firstName = 'John';
        $obj->lastName  = 'Doe';

        $logger->method('errorLog')->willReturn(true);
        $logger->expects($this->once())->method('errorLog')->with("ERROR --> stdClass Object\n(
    [firstName] => John
    [lastName] => Doe\n)\n\n", 0);
        $this->assertTrue($logger->handle('error', $obj));
    }

    /**
     * @return ErrorlogHandler&MockObject
     */
    private function getMockedHandler(array $config = [])
    {
        return $this->getMockBuilder(ErrorlogHandler::class)
            ->onlyMethods(['errorLog'])
            ->setConstructorArgs([$config])
            ->getMock();
    }
}
