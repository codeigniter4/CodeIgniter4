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
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use CodeIgniter\Test\Mock\MockResponse;
use Config\App;
use Config\Services;
use stdClass;

/**
 * @internal
 */
final class ChromeLoggerHandlerTest extends CIUnitTestCase
{
    public function testCanHandleLogLevel()
    {
        $config = new LoggerConfig();

        $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

        $logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
        $this->assertFalse($logger->canHandle('foo'));
    }

    public function testHandle()
    {
        $config = new LoggerConfig();

        $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

        $logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
        $this->assertTrue($logger->handle('warning', 'This a log test'));
    }

    public function testSendLogs()
    {
        $config = new LoggerConfig();

        $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

        $logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
        $logger->sendLogs();

        $response = Services::response(null, true);

        $this->assertTrue($response->hasHeader('X-ChromeLogger-Data'));
    }

    public function testSetDateFormat()
    {
        $config = new LoggerConfig();

        $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

        $logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
        $result = $logger->setDateFormat('F j, Y');

        $this->assertObjectHasAttribute('dateFormat', $result);
        $this->assertObjectHasAttribute('dateFormat', $logger);
    }

    public function testChromeLoggerHeaderSent()
    {
        Services::injectMock('response', new MockResponse(new App()));
        $response = service('response');

        $config = new LoggerConfig();

        $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

        $logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);

        $data              = new stdClass();
        $data->code        = 123;
        $data->explanation = "That's no moon, it's a pumpkin";
        $logger->setDateFormat('F j, Y');

        $logger->handle('warning', $data);

        $this->assertTrue($response->hasHeader('x-chromelogger-data'));
    }
}
