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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use Config\Database;
use ReturnTypeWillChange;

/**
 * Session handler using current Database for storage
 */
class DatabaseHandler extends BaseHandler
{
    /**
     * The database group to use for storage.
     *
     * @var string
     */
    protected $DBGroup;

    /**
     * The name of the table to store session info.
     *
     * @var string
     */
    protected $table;

    /**
     * The DB Connection instance.
     *
     * @var BaseConnection
     */
    protected $db;

    /**
     * The database type, for locking purposes.
     *
     * @var string
     */
    protected $platform;

    /**
     * Row exists flag
     *
     * @var bool
     */
    protected $rowExists = false;

    /**
     * @throws SessionException
     */
    public function __construct(AppConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);
        $this->table = $config->sessionSavePath;

        if (empty($this->table)) {
            throw SessionException::forMissingDatabaseTable();
        }

        // @phpstan-ignore-next-line
        $this->DBGroup = $config->sessionDBGroup ?? config(Database::class)->defaultGroup;

        $this->db = Database::connect($this->DBGroup);

        $driver = strtolower(get_class($this->db));

        if (strpos($driver, 'mysql') !== false) {
            $this->platform = 'mysql';
        } elseif (strpos($driver, 'postgre') !== false) {
            $this->platform = 'postgre';
        }
    }

    /**
     * Re-initialize existing session, or creates a new one.
     *
     * @param string $path The path where to store/retrieve the session
     * @param string $name The session name
     */
    public function open($path, $name): bool
    {
        if (empty($this->db->connID)) {
            $this->db->initialize();
        }

        return true;
    }

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
            ->select($this->platform === 'postgre' ? "encode(data, 'base64') AS data" : 'data')
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

        if (is_bool($result)) {
            $result = '';
        } else {
            $result = ($this->platform === 'postgre') ? base64_decode(rtrim($result->data), true) : $result->data;
        }

        $this->fingerprint = md5($result);
        $this->rowExists   = true;

        return $result;
    }

    /**
     * Writes the session data to the session storage.
     *
     * @param string $id   The session ID
     * @param string $data The encoded session data
     */
    public function write($id, $data): bool
    {
        if ($this->lock === false) {
            return $this->fail();
        }

        if ($this->sessionID !== $id) {
            $this->rowExists = false;
            $this->sessionID = $id;
        }

        if ($this->rowExists === false) {
            $insertData = [
                'id'         => $id,
                'ip_address' => $this->ipAddress,
                'data'       => $this->platform === 'postgre' ? '\x' . bin2hex($data) : $data,
            ];

            if (! $this->db->table($this->table)->set('timestamp', 'now()', false)->insert($insertData)) {
                return $this->fail();
            }

            $this->fingerprint = md5($data);
            $this->rowExists   = true;

            return true;
        }

        $builder = $this->db->table($this->table)->where('id', $id);

        if ($this->matchIP) {
            $builder = $builder->where('ip_address', $this->ipAddress);
        }

        $updateData = [];

        if ($this->fingerprint !== md5($data)) {
            $updateData['data'] = ($this->platform === 'postgre') ? '\x' . bin2hex($data) : $data;
        }

        if (! $builder->set('timestamp', 'now()', false)->update($updateData)) {
            return $this->fail();
        }

        $this->fingerprint = md5($data);

        return true;
    }

    /**
     * Closes the current session.
     */
    public function close(): bool
    {
        return ($this->lock && ! $this->releaseLock()) ? $this->fail() : true;
    }

    /**
     * Destroys a session
     *
     * @param string $id The session ID being destroyed
     */
    public function destroy($id): bool
    {
        if ($this->lock) {
            $builder = $this->db->table($this->table)->where('id', $id);

            if ($this->matchIP) {
                $builder = $builder->where('ip_address', $this->ipAddress);
            }

            if (! $builder->delete()) {
                return $this->fail();
            }
        }

        if ($this->close()) {
            $this->destroyCookie();

            return true;
        }

        return $this->fail();
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
        $separator = $this->platform === 'postgre' ? '\'' : ' ';
        $interval  = implode($separator, ['', "{$max_lifetime} second", '']);

        return $this->db->table($this->table)->where('timestamp <', "now() - INTERVAL {$interval}", false)->delete() ? 1 : $this->fail();
    }

    /**
     * Lock the session.
     */
    protected function lockSession(string $sessionID): bool
    {
        if ($this->platform === 'mysql') {
            $arg = md5($sessionID . ($this->matchIP ? '_' . $this->ipAddress : ''));
            if ($this->db->query("SELECT GET_LOCK('{$arg}', 300) AS ci_session_lock")->getRow()->ci_session_lock) {
                $this->lock = $arg;

                return true;
            }

            return $this->fail();
        }

        if ($this->platform === 'postgre') {
            $arg = "hashtext('{$sessionID}')" . ($this->matchIP ? ", hashtext('{$this->ipAddress}')" : '');
            if ($this->db->simpleQuery("SELECT pg_advisory_lock({$arg})")) {
                $this->lock = $arg;

                return true;
            }

            return $this->fail();
        }

        // Unsupported DB? Let the parent handle the simplified version.
        return parent::lockSession($sessionID);
    }

    /**
     * Releases the lock, if any.
     */
    protected function releaseLock(): bool
    {
        if (! $this->lock) {
            return true;
        }

        if ($this->platform === 'mysql') {
            if ($this->db->query("SELECT RELEASE_LOCK('{$this->lock}') AS ci_session_lock")->getRow()->ci_session_lock) {
                $this->lock = false;

                return true;
            }

            return $this->fail();
        }

        if ($this->platform === 'postgre') {
            if ($this->db->simpleQuery("SELECT pg_advisory_unlock({$this->lock})")) {
                $this->lock = false;

                return true;
            }

            return $this->fail();
        }

        // Unsupported DB? Let the parent handle the simple version.
        return parent::releaseLock();
    }
}
