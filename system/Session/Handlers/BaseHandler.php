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
use Config\Cookie as CookieConfig;
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
     * The name of the session which is used as cookie name.
     * It should only contain alphanumeric characters.
     *
     * @var string
     */
    protected $cookieName = 'ci_session';

    /**
     * The number of SECONDS you want the session to last.
     * Set to `0` means expire when the browser is closed.
     *
     * @var int
     */
    protected $lifetime = 7200;

    /**
     * The 'save path' for the session varies between
     *
     * @var array|string
     */
    protected $savePath;

    /**
     * Whether to match the user's IP address when reading the session data.
     *
     * NOTE: If you're using the database driver, don't forget to update
     *       your session table's PRIMARY KEY when changing this setting.
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
     * User's IP address.
     *
     * @var string
     */
    protected $ipAddress;

    public function __construct(AppConfig $config, string $ipAddress)
    {
        $session = config(SessionConfig::class);

        $this->cookieName = $session->name ?? $config->sessionCookieName ?? $this->cookieName;
        $this->lifetime   = $session->lifetime ?? $config->sessionExpiration ?? $this->lifetime;
        $this->savePath   = $session->savePath ?? $config->sessionSavePath;
        $this->matchIP    = $session->matchIP ?? $config->sessionMatchIP ?? $this->matchIP;

        $cookie = config(CookieConfig::class);

        $this->cookiePrefix = $cookie->prefix ?? $config->cookiePrefix;
        $this->cookiePath   = $cookie->path ?? $config->cookiePath;
        $this->cookieDomain = $cookie->domain ?? $config->cookieDomain;
        $this->cookieSecure = $cookie->secure ?? $config->cookieSecure;
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
