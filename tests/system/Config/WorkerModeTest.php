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

namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\WorkerMode;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class WorkerModeTest extends CIUnitTestCase
{
    public function testRoutesServiceIsNotPersistentByDefault(): void
    {
        $config = new WorkerMode();

        $this->assertNotContains(
            'routes',
            $config->persistentServices,
            'The routes service must not be persistent, or the hostname/subdomain route options are decided once per worker process instead of once per request.',
        );
    }
}
