<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers;

use Config\App as AppConfig;
use Psr\Log\LoggerAwareTrait;
use SessionHandlerInterface;

/**
 * Base class for session handling
 */
abstract class BaseHandler implements SessionHandlerInterface
{
    use LoggerAwareTrait;

    /**
     * The Data fingerprint.
     *
     * @var string
     */
    protected $fingerprint;

    /**
     * Lock placeholder.
     *
     * @var mixed
     */
    protected $lock = false;

    /**
     * Cookie prefix
     *
     * @var string
     */
    protected $cookiePrefix = '';

    /**
     * Cookie domain
     *
     * @var string
     */
    protected $cookieDomain = '';

    /**
     * Cookie path
     *
     * @var string
     */
    protected $cookiePath = '/';

    /**
     * Cookie secure?
     *
     * @var bool
     */
    protected $cookieSecure = false;

    /**
     * Cookie name to use
     *
     * @var string
     */
    protected $cookieName;

    /**
     * Match IP addresses for cookies?
     *
     * @var bool
     */
    protected $matchIP = false;

    /**
     * Current session ID
     *
     * @var string
     */
    protected $sessionID;

    /**
     * The 'save path' for the session
     * varies between
     *
     * @var array|string
     */
    protected $savePath;

    /**
     * User's IP address.
     *
     * @var string
     */
    protected $ipAddress;

    public function __construct(AppConfig $config, string $ipAddress)
    {
        $this->cookiePrefix = $config->cookiePrefix;
        $this->cookieDomain = $config->cookieDomain;
        $this->cookiePath   = $config->cookiePath;
        $this->cookieSecure = $config->cookieSecure;
        $this->cookieName   = $config->sessionCookieName;
        $this->matchIP      = $config->sessionMatchIP;
        $this->savePath     = $config->sessionSavePath;
        $this->ipAddress    = $ipAddress;
    }

    /**
     * Internal method to force removal of a cookie by the client
     * when session_destroy() is called.
     */
    protected function destroyCookie(): bool
    {
        return setcookie(
            $this->cookieName,
            '',
            1,
            $this->cookiePath,
            $this->cookieDomain,
            $this->cookieSecure,
            true
        );
    }

    /**
     * A dummy method allowing drivers with no locking functionality
     * (databases other than PostgreSQL and MySQL) to act as if they
     * do acquire a lock.
     */
    protected function lockSession(string $sessionID): bool
    {
        $this->lock = true;

        return true;
    }

    /**
     * Releases the lock, if any.
     */
    protected function releaseLock(): bool
    {
        $this->lock = false;

        return true;
    }

    /**
     * Drivers other than the 'files' one don't (need to) use the
     * session.save_path INI setting, but that leads to confusing
     * error messages emitted by PHP when open() or write() fail,
     * as the message contains session.save_path ...
     *
     * To work around the problem, the drivers will call this method
     * so that the INI is set just in time for the error message to
     * be properly generated.
     */
    protected function fail(): bool
    {
        ini_set('session.save_path', $this->savePath);

        return false;
    }
}
