<?php

namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockFileLogger;
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use org\bovigo\vfs\vfsStream;

/**
 * @internal
 */
final class FileHandlerTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->root  = vfsStream::setup('root');
        $this->start = $this->root->url() . '/';
    }

    public function testHandle()
    {
        $config = new LoggerConfig();

        $config->handlers['Tests\Support\Log\Handlers\TestHandler']['handles'] = ['critical'];

        $logger = new MockFileLogger($config->handlers['Tests\Support\Log\Handlers\TestHandler']);
        $logger->setDateFormat('Y-m-d H:i:s:u');
        $this->assertTrue($logger->handle('warning', 'This is a test log'));
    }

    //--------------------------------------------------------------------

    public function testBasicHandle()
    {
        $config                                                                = new LoggerConfig();
        $config->handlers['Tests\Support\Log\Handlers\TestHandler']['path']    = $this->start . 'charlie/';
        $config->handlers['Tests\Support\Log\Handlers\TestHandler']['handles'] = ['critical'];

        $logger = new MockFileLogger($config->handlers['Tests\Support\Log\Handlers\TestHandler']);
        $logger->setDateFormat('Y-m-d H:i:s:u');
        $expected = 'log-' . date('Y-m-d') . '.log';
        vfsStream::newFile($expected)->at(vfsStream::setup('root/charlie'))->withContent('This is a test log');
        $this->assertTrue($logger->handle('warning', 'This is a test log'));
    }

    public function testHandleCreateFile()
    {
        $config                                                             = new LoggerConfig();
        $config->handlers['Tests\Support\Log\Handlers\TestHandler']['path'] = $this->start;
        $logger                                                             = new MockFileLogger($config->handlers['Tests\Support\Log\Handlers\TestHandler']);

        $logger->setDateFormat('Y-m-d H:i:s:u');
        $expected = 'log-' . date('Y-m-d') . '.log';
        vfsStream::newFile($expected)->at(vfsStream::setup('root'))->withContent('This is a test log');
        $logger->handle('warning', 'This is a test log');

        $fp   = fopen($config->handlers['Tests\Support\Log\Handlers\TestHandler']['path'] . $expected, 'rb');
        $line = fgets($fp);
        fclose($fp);

        // did the log file get created?
        $expectedResult = 'This is a test log';
        $this->assertStringContainsString($expectedResult, $line);
    }

    public function testHandleDateTimeCorrectly()
    {
        $config                                                             = new LoggerConfig();
        $config->handlers['Tests\Support\Log\Handlers\TestHandler']['path'] = $this->start;
        $logger                                                             = new MockFileLogger($config->handlers['Tests\Support\Log\Handlers\TestHandler']);

        $logger->setDateFormat('Y-m-d');
        $expected = 'log-' . date('Y-m-d') . '.log';
        vfsStream::newFile($expected)->at(vfsStream::setup('root'))->withContent('Test message');
        $logger->handle('debug', 'Test message');
        $fp   = fopen($config->handlers['Tests\Support\Log\Handlers\TestHandler']['path'] . $expected, 'rb');
        $line = fgets($fp); // and get the second line
        fclose($fp);

        $expectedResult = 'Test message';
        $this->assertStringContainsString($expectedResult, $line);
    }
}
