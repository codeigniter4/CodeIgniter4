<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Commands\AppInfo;

/**
 * @internal
 *
 * @group Others
 */
final class BaseCommandTest extends CIUnitTestCase
{
    private Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = Services::logger();
    }

    public function testMagicIssetTrue(): void
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertTrue(isset($command->group));
    }

    public function testMagicIssetFalse(): void
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertFalse(isset($command->foobar));
    }

    public function testMagicGet(): void
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertSame('demo', $command->group);
    }

    public function testMagicGetMissing(): void
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertNull($command->foobar);
    }
}
