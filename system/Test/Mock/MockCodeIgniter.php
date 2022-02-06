<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\CodeIgniter;

class MockCodeIgniter extends CodeIgniter
{
    /**
     * Context
     *  web:     Invoked by HTTP request
     *  php-cli: Invoked by CLI via `php public/index.php`
     *  spark:   Invoked by CLI via the `spark` command
     *
     * @phpstan-var 'php-cli'|'spark'|'web'
     */
    protected string $context = 'web';

    protected function callExit($code)
    {
        // Do not call exit() in testing.
    }
}
