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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockFileLogger;
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use stdClass;
use Tests\Support\Log\Handlers\TestHandler;

/**
 * @internal
 */
final class FileHandlerTest extends CIUnitTestCase
{
    private vfsStreamDirectory $root;
    private string $start;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root  = vfsStream::setup('root');
        $this->start = $this->root->url() . '/';
    }

    public function testHandle()
    {
        $config = new LoggerConfig();

        $config->handlers[TestHandler::class]['handles'] = ['critical'];

        $logger = new MockFileLogger($config->handlers[TestHandler::class]);
        $logger->setDateFormat('Y-m-d H:i:s:u');
        $this->assertTrue($logger->handle('warning', 'This is a test log'));
    }

    public function testBasicHandle()
    {
        $config                                          = new LoggerConfig();
        $config->handlers[TestHandler::class]['path']    = $this->start . 'charlie/';
        $config->handlers[TestHandler::class]['handles'] = ['critical'];

        $logger = new MockFileLogger($config->handlers[TestHandler::class]);
        $logger->setDateFormat('Y-m-d H:i:s:u');
        $expected = 'log-' . date('Y-m-d') . '.log';
        vfsStream::newFile($expected)->at(vfsStream::setup('root/charlie'))->withContent('This is a test log');
        $this->assertTrue($logger->handle('warning', 'This is a test log'));
    }

    public function testHandleCreateFile()
    {
        $config                                       = new LoggerConfig();
        $config->handlers[TestHandler::class]['path'] = $this->start;
        $logger                                       = new MockFileLogger($config->handlers[TestHandler::class]);

        $logger->setDateFormat('Y-m-d H:i:s:u');
        $expected = 'log-' . date('Y-m-d') . '.log';
        vfsStream::newFile($expected)->at(vfsStream::setup('root'))->withContent('This is a test log');
        $logger->handle('warning', 'This is a test log');

        $fp   = fopen($config->handlers[TestHandler::class]['path'] . $expected, 'rb');
        $line = fgets($fp);
        fclose($fp);

        // did the log file get created?
        $expectedResult = 'This is a test log';
        $this->assertStringContainsString($expectedResult, $line);
    }

    public function testHandleCreateFilePHP()
    {
        $config                                                = new LoggerConfig();
        $config->handlers[TestHandler::class]['path']          = $this->start;
        $config->handlers[TestHandler::class]['fileExtension'] = 'php';
        $logger                                                = new MockFileLogger($config->handlers[TestHandler::class]);

        $logger->setDateFormat('Y-m-d');
        $date     = date('Y-m-d');
        $expected = 'log-' . $date . '.php';
        $logger->handle('info', 'This is a test log');

        $fp     = fopen($config->handlers[TestHandler::class]['path'] . $expected, 'rb');
        $result = stream_get_contents($fp);
        fclose($fp);

        // did the log file get created?
        $expectedResult = "<?php defined('SYSTEMPATH') || exit('No direct script access allowed'); ?>\n\nINFO - " . $date . " --> This is a test log\n";
        $this->assertSame($expectedResult, $result);
    }

    public function testHandleWithInteger()
    {
        $config                                       = new LoggerConfig();
        $config->handlers[TestHandler::class]['path'] = $this->start;
        $logger                                       = new MockFileLogger($config->handlers[TestHandler::class]);

        $logger->setDateFormat('Y-m-d');
        $date     = date('Y-m-d');
        $expected = 'log-' . $date . '.log';
        $logger->handle('info', 123456);

        $fp     = fopen($config->handlers[TestHandler::class]['path'] . $expected, 'rb');
        $result = stream_get_contents($fp);
        fclose($fp);

        // did the log file get created?
        $expectedResult = 'INFO - ' . $date . " --> 123456\n";
        $this->assertSame($expectedResult, $result);
    }

    public function testHandleWithArray()
    {
        $config                                       = new LoggerConfig();
        $config->handlers[TestHandler::class]['path'] = $this->start;
        $logger                                       = new MockFileLogger($config->handlers[TestHandler::class]);

        $logger->setDateFormat('Y-m-d');
        $date    = date('Y-m-d');
        $arrData = [
            'firstName' => 'John',
            'lastName'  => 'Doe',
        ];
        $expected = 'log-' . $date . '.log';
        $logger->handle('info', print_r($arrData, true));

        $fp     = fopen($config->handlers[TestHandler::class]['path'] . $expected, 'rb');
        $result = stream_get_contents($fp);
        fclose($fp);

        // did the log file get created?
        $expectedResult = 'INFO - ' . $date . " --> Array\n(
    [firstName] => John
    [lastName] => Doe\n)\n\n";
        $this->assertSame($expectedResult, $result);
    }

    public function testHandleWithObject()
    {
        $config                                       = new LoggerConfig();
        $config->handlers[TestHandler::class]['path'] = $this->start;
        $logger                                       = new MockFileLogger($config->handlers[TestHandler::class]);

        $logger->setDateFormat('Y-m-d');
        $date = date('Y-m-d');

        $obj            = new stdClass();
        $obj->firstName = 'John';
        $obj->lastName  = 'Doe';

        $expected = 'log-' . $date . '.log';
        $logger->handle('info', print_r($obj, true));

        $fp     = fopen($config->handlers[TestHandler::class]['path'] . $expected, 'rb');
        $result = stream_get_contents($fp);
        fclose($fp);

        // did the log file get created?
        $expectedResult = 'INFO - ' . $date . " --> stdClass Object\n(
    [firstName] => John
    [lastName] => Doe\n)\n\n";
        $this->assertSame($expectedResult, $result);
    }

    public function testHandleDateTimeCorrectly()
    {
        $config                                       = new LoggerConfig();
        $config->handlers[TestHandler::class]['path'] = $this->start;
        $logger                                       = new MockFileLogger($config->handlers[TestHandler::class]);

        $logger->setDateFormat('Y-m-d');
        $expected = 'log-' . date('Y-m-d') . '.log';
        vfsStream::newFile($expected)->at(vfsStream::setup('root'))->withContent('Test message');
        $logger->handle('debug', 'Test message');
        $fp   = fopen($config->handlers[TestHandler::class]['path'] . $expected, 'rb');
        $line = fgets($fp); // and get the second line
        fclose($fp);

        $expectedResult = 'Test message';
        $this->assertStringContainsString($expectedResult, $line);
    }
}
