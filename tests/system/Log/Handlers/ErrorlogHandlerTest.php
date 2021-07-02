<?php

namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Log\Exceptions\LogException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

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

    /**
     * @param array $config
     *
     * @return MockObject&ErrorlogHandler
     */
    private function getMockedHandler(array $config = [])
    {
        return $this->getMockBuilder(ErrorlogHandler::class)
            ->onlyMethods(['errorLog'])
            ->setConstructorArgs([$config])
            ->getMock();
    }
}
