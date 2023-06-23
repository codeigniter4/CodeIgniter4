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

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\I18n\Time;
use CodeIgniter\Session\Session;

/**
 * Class MockSession
 *
 * Provides a safe way to test the Session class itself,
 * that doesn't interact with the session or cookies at all.
 */
class MockSession extends Session
{
    /**
     * Holds our "cookie" data.
     *
     * @var Cookie[]
     */
    public $cookies = [];

    public $didRegenerate = false;

    /**
     * Sets the driver as the session handler in PHP.
     * Extracted for easier testing.
     */
    protected function setSaveHandler()
    {
        // session_set_save_handler($this->driver, true);
    }

    /**
     * Starts the session.
     * Extracted for testing reasons.
     */
    protected function startSession()
    {
        // session_start();
        $this->setCookie();
    }

    /**
     * Takes care of setting the cookie on the client side.
     * Extracted for testing reasons.
     */
    protected function setCookie()
    {
        $expiration   = $this->config->expiration === 0 ? 0 : Time::now()->getTimestamp() + $this->config->expiration;
        $this->cookie = $this->cookie->withValue(session_id())->withExpires($expiration);

        $this->cookies[] = $this->cookie;
    }

    public function regenerate(bool $destroy = false)
    {
        $this->didRegenerate              = true;
        $_SESSION['__ci_last_regenerate'] = Time::now()->getTimestamp();
    }
}
