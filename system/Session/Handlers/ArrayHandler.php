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

namespace CodeIgniter\Session\Handlers;

/**
 * Session handler using static array for storage.
 * Intended only for use during testing.
 */
class ArrayHandler extends BaseHandler
{
    /**
     * @var array<string, mixed>
     */
    protected static $cache = [];

    /**
     * Re-initialize existing session, or creates a new one.
     *
     * @param string $path The path where to store/retrieve the session.
     * @param string $name The session name.
     */
    public function open($path, $name): bool
    {
        return true;
    }

    /**
     * Reads the session data from the session storage, and returns the results.
     *
     * @param string $id The session ID.
     */
    public function read($id): string
    {
        return '';
    }

    /**
     * Writes the session data to the session storage.
     *
     * @param string $id   The session ID.
     * @param string $data The encoded session data.
     */
    public function write($id, $data): bool
    {
        return true;
    }

    /**
     * Closes the current session.
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroys a session.
     *
     * @param string $id The session ID being destroyed.
     */
    public function destroy($id): bool
    {
        return true;
    }

    /**
     * Cleans up expired sessions.
     *
     * @param int $max_lifetime Sessions that have not updated
     *                          for the last max_lifetime seconds will be removed.
     */
    public function gc($max_lifetime): int
    {
        return 1;
    }
}
