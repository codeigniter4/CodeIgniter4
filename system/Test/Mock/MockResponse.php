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

namespace CodeIgniter\Test\Mock;

use CodeIgniter\HTTP\Response;

class MockResponse extends Response
{
    /**
     * If true, will not write output. Useful during testing.
     *
     * @var bool
     */
    protected $pretend = true;

    /**
     * For testing.
     *
     * @return bool
     */
    public function getPretend()
    {
        return $this->pretend;
    }

    /**
     * Artificial error for testing
     *
     * @return void
     */
    public function misbehave()
    {
        $this->statusCode = 0;
    }
}
