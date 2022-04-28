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

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Commands\AppInfo;

/**
 * @internal
 */
final class BaseCommandTest extends CIUnitTestCase
{
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = Services::logger();
    }

    public function testMagicIssetTrue()
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertObjectHasAttribute('group', $command);
    }

    public function testMagicIssetFalse()
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertObjectNotHasAttribute('foobar', $command);
    }

    public function testMagicGet()
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertSame('demo', $command->group);
    }

    public function testMagicGetMissing()
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertNull($command->foobar);
    }
}
