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

use CodeIgniter\Session\Handlers\DatabaseHandler;
use Config\App as AppConfig;
use Config\Database as DatabaseConfig;

/**
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
            'sessionDriver'            => DatabaseHandler::class,
            'sessionCookieName'        => 'ci_session',
            'sessionExpiration'        => 7200,
            'sessionSavePath'          => 'ci_sessions',
            'sessionMatchIP'           => false,
            'sessionTimeToUpdate'      => 300,
            'sessionRegenerateDestroy' => false,
            'cookieDomain'             => '',
            'cookiePrefix'             => '',
            'cookiePath'               => '/',
            'cookieSecure'             => false,
            'cookieSameSite'           => 'Lax',
        ];

        $config    = array_merge($defaults, $options);
        $appConfig = new AppConfig();

        foreach ($config as $key => $c) {
            $appConfig->{$key} = $c;
        }

        return new PostgreHandler($appConfig, '127.0.0.1');
    }
}
