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
use ReturnTypeWillChange;

/**
 * Session handler for Postgre
 */
class PostgreHandler extends DatabaseHandler
{
    /**
     * Reads the session data from the session storage, and returns the results.
     *
     * @param string $id The session ID
     *
     * @return false|string Returns an encoded string of the read data.
     *                      If nothing was read, it must return false.
     */
    #[ReturnTypeWillChange]
    public function read($id)
    {
        if ($this->lockSession($id) === false) {
            $this->fingerprint = md5('');

            return '';
        }

        if (! isset($this->sessionID)) {
            $this->sessionID = $id;
        }

        $builder = $this->db->table($this->table)
            ->select("encode(data, 'base64') AS data")
            ->where('id', $id);

        if ($this->matchIP) {
            $builder = $builder->where('ip_address', $this->ipAddress);
        }

        $result = $builder->get()->getRow();

        if ($result === null) {
            // PHP7 will reuse the same SessionHandler object after
            // ID regeneration, so we need to explicitly set this to
            // FALSE instead of relying on the default ...
            $this->rowExists   = false;
            $this->fingerprint = md5('');

            return '';
        }

        $result = is_bool($result) ? '' : base64_decode(rtrim($result->data), true);

        $this->fingerprint = md5($result);
        $this->rowExists   = true;

        return $result;
    }

    /**
     * Prepare data to insert/update
     */
    protected function prepareData(string $data): string
    {
        return '\x' . bin2hex($data);
    }

    /**
     * Cleans up expired sessions.
     *
     * @param int $max_lifetime Sessions that have not updated
     *                          for the last max_lifetime seconds will be removed.
     *
     * @return false|int Returns the number of deleted sessions on success, or false on failure.
     */
    #[ReturnTypeWillChange]
    public function gc($max_lifetime)
    {
        $separator = '\'';
        $interval  = implode($separator, ['', "{$max_lifetime} second", '']);

        return $this->db->table($this->table)->where('timestamp <', "now() - INTERVAL {$interval}", false)->delete() ? 1 : $this->fail();
    }

    /**
     * Lock the session.
     */
    protected function lockSession(string $sessionID): bool
    {
        $arg = "hashtext('{$sessionID}')" . ($this->matchIP ? ", hashtext('{$this->ipAddress}')" : '');
        if ($this->db->simpleQuery("SELECT pg_advisory_lock({$arg})")) {
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

        if ($this->db->simpleQuery("SELECT pg_advisory_unlock({$this->lock})")) {
            $this->lock = false;

            return true;
        }

        return $this->fail();
    }
}
