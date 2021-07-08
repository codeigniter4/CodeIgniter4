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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use Config\Database;
use Exception;

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

    //--------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param AppConfig $config
     * @param string    $ipAddress
     */
    public function __construct(AppConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);

        // Determine Table
        $this->table = $config->sessionSavePath;

        if (empty($this->table)) {
            throw SessionException::forMissingDatabaseTable();
        }

        // Get DB Connection
        // @phpstan-ignore-next-line
        $this->DBGroup = $config->sessionDBGroup ?? config(Database::class)->defaultGroup;

        $this->db = Database::connect($this->DBGroup);

        // Determine Database type
        $driver = strtolower(get_class($this->db));
        if (strpos($driver, 'mysql') !== false) {
            $this->platform = 'mysql';
        } elseif (strpos($driver, 'postgre') !== false) {
            $this->platform = 'postgre';
        }
    }

    //--------------------------------------------------------------------

    /**
     * Open
     *
     * Ensures we have an initialized database connection.
     *
     * @param string $savePath Path to session files' directory
     * @param string $name     Session cookie name
     *
     * @throws Exception
     *
     * @return bool
     */
    public function open($savePath, $name): bool
    {
        if (empty($this->db->connID)) {
            $this->db->initialize();
        }

        return true;
    }

    //--------------------------------------------------------------------

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
        if ($this->lockSession($sessionID) === false) {
            $this->fingerprint = md5('');

            return '';
        }

        // Needed by write() to detect session_regenerate_id() calls
        if (! isset($this->sessionID)) {
            $this->sessionID = $sessionID;
        }

        $builder = $this->db->table($this->table)
            ->select($this->platform === 'postgre' ? "encode(data, 'base64') AS data" : 'data')
            ->where('id', $sessionID);

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

    //--------------------------------------------------------------------

    /**
     * Write
     *
     * Writes (create / update) session data
     *
     * @param string $sessionID   Session ID
     * @param string $sessionData Serialized session data
     *
     * @return bool
     */
    public function write($sessionID, $sessionData): bool
    {
        if ($this->lock === false) {
            return $this->fail();
        }

        // Was the ID regenerated?
        if ($sessionID !== $this->sessionID) {
            $this->rowExists = false;
            $this->sessionID = $sessionID;
        }

        if ($this->rowExists === false) {
            $insertData = [
                'id'         => $sessionID,
                'ip_address' => $this->ipAddress,
                'timestamp'  => 'now()',
                'data'       => $this->platform === 'postgre' ? '\x' . bin2hex($sessionData) : $sessionData,
            ];

            if (! $this->db->table($this->table)->insert($insertData)) {
                return $this->fail();
            }

            $this->fingerprint = md5($sessionData);
            $this->rowExists   = true;

            return true;
        }

        $builder = $this->db->table($this->table)->where('id', $sessionID);

        if ($this->matchIP) {
            $builder = $builder->where('ip_address', $this->ipAddress);
        }

        $updateData = [
            'timestamp' => 'now()',
        ];

        if ($this->fingerprint !== md5($sessionData)) {
            $updateData['data'] = ($this->platform === 'postgre') ? '\x' . bin2hex($sessionData) : $sessionData;
        }

        if (! $builder->update($updateData)) {
            return $this->fail();
        }

        $this->fingerprint = md5($sessionData);

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Close
     *
     * Releases locks and closes file descriptor.
     *
     * @return bool
     */
    public function close(): bool
    {
        return ($this->lock && ! $this->releaseLock()) ? $this->fail() : true;
    }

    //--------------------------------------------------------------------

    /**
     * Destroy
     *
     * Destroys the current session.
     *
     * @param string $sessionID
     *
     * @return bool
     */
    public function destroy($sessionID): bool
    {
        if ($this->lock) {
            $builder = $this->db->table($this->table)->where('id', $sessionID);

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

    //--------------------------------------------------------------------

    /**
     * Garbage Collector
     *
     * Deletes expired sessions
     *
     * @param int $maxlifetime Maximum lifetime of sessions
     *
     * @return bool
     */
    public function gc($maxlifetime): bool
    {
        $separator = $this->platform === 'postgre' ? '\'' : ' ';
        $interval  = implode($separator, ['', "{$maxlifetime} second", '']);

        return $this->db->table($this->table)->delete("timestamp < now() - INTERVAL {$interval}") ? true : $this->fail();
    }

    //--------------------------------------------------------------------

    /**
     * Lock the session.
     *
     * @param string $sessionID
     *
     * @return bool
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

    //--------------------------------------------------------------------

    /**
     * Releases the lock, if any.
     *
     * @return bool
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

    //--------------------------------------------------------------------
}
