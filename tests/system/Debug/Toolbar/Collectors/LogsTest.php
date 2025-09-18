<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Logger as LoggerConfig;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class LogsTest extends CIUnitTestCase
{
    private Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        // The logs collector relies on the logger being in debug mode
        // so it would populate logCache.
        $this->logger = new Logger(new LoggerConfig(), debug: true);
        Services::injectMock('logger', $this->logger);
    }

    public function testDisplay(): void
    {
        // log_message() always creates a new TestLogger instance while
        // testing, so we need to log directly to our instance.
        $this->logger->error('Test error');
        $this->logger->info('Test info');

        $collector = new Logs();
        $result    = $collector->display();

        $this->assertArrayHasKey('logs', $result);
        $this->assertCount(2, $result['logs']);
        $this->assertSame('error', $result['logs'][0]['level']);
        $this->assertSame('Test error', $result['logs'][0]['msg']);
        $this->assertSame('info', $result['logs'][1]['level']);
        $this->assertSame('Test info', $result['logs'][1]['msg']);
    }

    public function testEmpty(): void
    {
        $collector = new Logs();
        $this->assertTrue($collector->isEmpty());
    }

    public function testNotEmpty(): void
    {
        $this->logger->warning('Test warning');

        $collector = new Logs();
        $this->assertFalse($collector->isEmpty());
    }
}
