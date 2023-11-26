<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers\Database;

use Config\Database as DatabaseConfig;
use Config\Session as SessionConfig;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class PostgreHandlerTest extends AbstractHandlerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config(DatabaseConfig::class)->tests['DBDriver'] !== 'Postgre') {
            $this->markTestSkipped('This test case needs Postgre');
        }
    }

    protected function getInstance($options = [])
    {
        $defaults = [
            'driver'            => $this->sessionDriver,
            'cookieName'        => $this->sessionName,
            'expiration'        => 7200,
            'savePath'          => $this->sessionSavePath,
            'matchIP'           => false,
            'timeToUpdate'      => 300,
            'regenerateDestroy' => false,
        ];
        $sessionConfig = new SessionConfig();
        $config        = array_merge($defaults, $options);

        foreach ($config as $key => $value) {
            $sessionConfig->{$key} = $value;
        }

        return new PostgreHandler($sessionConfig, $this->userIpAddress);
    }
}
