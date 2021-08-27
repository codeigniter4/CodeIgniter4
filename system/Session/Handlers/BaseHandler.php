<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers;

use Config\App as AppConfig;
use Config\Session as SessionConfig;
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
     * @var string|array
     */
    protected $savePath;

    /**
     * User's IP address.
     *
     * @var string
     */
    protected $ipAddress;

    //--------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param AppConfig $config
     * @param string    $ipAddress
     */
    public function __construct(AppConfig $config, string $ipAddress)
    {
        /** @var SessionConfig */
        $session = config('Session');
        
        $this->cookieName = $session->cookieName ?? $config->sessionCookieName;
        $this->matchIP    = $session->matchIP ?? $config->sessionMatchIP ?? $this->matchIP;
        $this->savePath   = $session->savePath ?? $config->sessionSavePath;

        $this->cookiePrefix = $config->cookiePrefix;
        $this->cookieDomain = $config->cookieDomain;
        $this->cookiePath   = $config->cookiePath;
        $this->cookieSecure = $config->cookieSecure;
        $this->ipAddress    = $ipAddress;
    }

    //--------------------------------------------------------------------

    /**
     * Internal method to force removal of a cookie by the client
     * when session_destroy() is called.
     *
     * @return bool
     */
    protected function destroyCookie(): bool
    {
        return setcookie(
                $this->cookieName, '', 1, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, true
        );
    }

    //--------------------------------------------------------------------

    /**
     * A dummy method allowing drivers with no locking functionality
     * (databases other than PostgreSQL and MySQL) to act as if they
     * do acquire a lock.
     *
     * @param string $sessionID
     *
     * @return bool
     */
    protected function lockSession(string $sessionID): bool
    {
        $this->lock = true;

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Releases the lock, if any.
     *
     * @return bool
     */
    protected function releaseLock(): bool
    {
        $this->lock = false;

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Fail
     *
     * Drivers other than the 'files' one don't (need to) use the
     * session.save_path INI setting, but that leads to confusing
     * error messages emitted by PHP when open() or write() fail,
     * as the message contains session.save_path ...
     * To work around the problem, the drivers will call this method
     * so that the INI is set just in time for the error message to
     * be properly generated.
     *
     * @return bool
     */
    protected function fail(): bool
    {
        ini_set('session.save_path', $this->savePath);

        return false;
    }
}
