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

use Exception;

/**
 * Session handler using static array for storage.
 * Intended only for use during testing.
 */
class ArrayHandler extends BaseHandler
{
    protected static $cache = [];

    /**
     * Open
     *
     * Ensures we have an initialized database connection.
     *
     * @param string $savePath Path to session files' directory
     * @param string $name     Session cookie name
     *
     * @throws Exception
     */
    public function open($savePath, $name): bool
    {
        return true;
    }

    /**
     * Read
     *
     * Reads session data and acquires a lock
     *
     * @param string $sessionID Session ID
     *
     * @return string Serialized session data
     */
    public function read($sessionID): string
    {
        return '';
    }

    /**
     * Write
     *
     * Writes (create / update) session data
     *
     * @param string $sessionID   Session ID
     * @param string $sessionData Serialized session data
     */
    public function write($sessionID, $sessionData): bool
    {
        return true;
    }

    /**
     * Close
     *
     * Releases locks and closes file descriptor.
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroy
     *
     * Destroys the current session.
     *
     * @param string $sessionID
     */
    public function destroy($sessionID): bool
    {
        return true;
    }

    /**
     * Garbage Collector
     *
     * Deletes expired sessions
     *
     * @param int $maxlifetime Maximum lifetime of sessions
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }
}
