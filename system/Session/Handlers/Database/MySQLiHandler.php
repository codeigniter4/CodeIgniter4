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

/**
 * Session handler for MySQLi
 */
class MySQLiHandler extends DatabaseHandler
{
    /**
     * Lock the session.
     */
    protected function lockSession(string $sessionID): bool
    {
        $arg = md5($sessionID . ($this->matchIP ? '_' . $this->ipAddress : ''));
        if ($this->db->query("SELECT GET_LOCK('{$arg}', 300) AS ci_session_lock")->getRow()->ci_session_lock) {
            $this->lock = $arg;

            return true;
        }

        return $this->fail();
    }

    /**
     * Releases the lock, if any.
     */
    protected function releaseLock(): bool
    {
        if (! $this->lock) {
            return true;
        }

        if ($this->db->query("SELECT RELEASE_LOCK('{$this->lock}') AS ci_session_lock")->getRow()->ci_session_lock) {
            $this->lock = false;

            return true;
        }

        return $this->fail();
    }
}
