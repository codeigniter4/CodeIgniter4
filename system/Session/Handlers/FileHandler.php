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

use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use Exception;

/**
 * Session handler using file system for storage
 */
class FileHandler extends BaseHandler
{
    /**
     * Where to save the session files to.
     *
     * @var string
     */
    protected $savePath;

    /**
     * The file handle
     *
     * @var resource|null
     */
    protected $fileHandle;

    /**
     * File Name
     *
     * @var string
     */
    protected $filePath;

    /**
     * Whether this is a new file.
     *
     * @var bool
     */
    protected $fileNew;

    /**
     * Whether IP addresses should be matched.
     *
     * @var bool
     */
    protected $matchIP = false;

    /**
     * Regex of session ID
     *
     * @var string
     */
    protected $sessionIDRegex = '';

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

        if (! empty($config->sessionSavePath)) {
            $this->savePath = rtrim($config->sessionSavePath, '/\\');
            ini_set('session.save_path', $config->sessionSavePath);
        } else {
            $sessionPath = rtrim(ini_get('session.save_path'), '/\\');

            if (! $sessionPath) {
                $sessionPath = WRITEPATH . 'session';
            }

            $this->savePath = $sessionPath;
        }

        $this->matchIP = $config->sessionMatchIP;

        $this->configureSessionIDRegex();
    }

    //--------------------------------------------------------------------

    /**
     * Open
     *
     * Sanitizes the save_path directory.
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
        if (! is_dir($savePath)) {
            if (! mkdir($savePath, 0700, true)) {
                throw SessionException::forInvalidSavePath($this->savePath);
            }
        } elseif (! is_writable($savePath)) {
            throw SessionException::forWriteProtectedSavePath($this->savePath);
        }

        $this->savePath = $savePath;
        $this->filePath = $this->savePath . '/'
                          . $name // we'll use the session cookie name as a prefix to avoid collisions
                          . ($this->matchIP ? md5($this->ipAddress) : '');

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
     * @return bool|string Serialized session data
     */
    public function read($sessionID)
    {
        // This might seem weird, but PHP 5.6 introduced session_reset(),
        // which re-reads session data
        if ($this->fileHandle === null) {
            $this->fileNew = ! is_file($this->filePath . $sessionID);

            if (($this->fileHandle = fopen($this->filePath . $sessionID, 'c+b')) === false) {
                $this->logger->error("Session: Unable to open file '" . $this->filePath . $sessionID . "'.");

                return false;
            }

            if (flock($this->fileHandle, LOCK_EX) === false) {
                $this->logger->error("Session: Unable to obtain lock for file '" . $this->filePath . $sessionID . "'.");
                fclose($this->fileHandle);
                $this->fileHandle = null;

                return false;
            }

            // Needed by write() to detect session_regenerate_id() calls
            if (! isset($this->sessionID)) {
                $this->sessionID = $sessionID;
            }

            if ($this->fileNew) {
                chmod($this->filePath . $sessionID, 0600);
                $this->fingerprint = md5('');

                return '';
            }
        } else {
            rewind($this->fileHandle);
        }

        $sessionData = '';
        clearstatcache();    // Address https://github.com/codeigniter4/CodeIgniter4/issues/2056

        for ($read = 0, $length = filesize($this->filePath . $sessionID); $read < $length; $read += strlen($buffer)) {
            if (($buffer = fread($this->fileHandle, $length - $read)) === false) {
                break;
            }

            $sessionData .= $buffer;
        }

        $this->fingerprint = md5($sessionData);

        return $sessionData;
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
        // If the two IDs don't match, we have a session_regenerate_id() call
        if ($sessionID !== $this->sessionID) {
            $this->sessionID = $sessionID;
        }

        if (! is_resource($this->fileHandle)) {
            return false;
        }

        if ($this->fingerprint === md5($sessionData)) {
            return ($this->fileNew) ? true : touch($this->filePath . $sessionID);
        }

        if (! $this->fileNew) {
            ftruncate($this->fileHandle, 0);
            rewind($this->fileHandle);
        }

        if (($length = strlen($sessionData)) > 0) {
            $result = null;

            for ($written = 0; $written < $length; $written += $result) {
                if (($result = fwrite($this->fileHandle, substr($sessionData, $written))) === false) {
                    break;
                }
            }

            if (! is_int($result)) {
                $this->fingerprint = md5(substr($sessionData, 0, $written));
                $this->logger->error('Session: Unable to write data.');

                return false;
            }
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
        if (is_resource($this->fileHandle)) {
            flock($this->fileHandle, LOCK_UN);
            fclose($this->fileHandle);

            $this->fileHandle = null;
            $this->fileNew    = false;

            return true;
        }

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Destroy
     *
     * Destroys the current session.
     *
     * @param string $sessionId Session ID
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        if ($this->close()) {
            return is_file($this->filePath . $sessionId)
                ? (unlink($this->filePath . $sessionId) && $this->destroyCookie()) : true;
        }

        if ($this->filePath !== null) {
            clearstatcache();

            return is_file($this->filePath . $sessionId)
                ? (unlink($this->filePath . $sessionId) && $this->destroyCookie()) : true;
        }

        return false;
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
        if (! is_dir($this->savePath) || ($directory = opendir($this->savePath)) === false) {
            $this->logger->debug("Session: Garbage collector couldn't list files under directory '" . $this->savePath . "'.");

            return false;
        }

        $ts = time() - $maxlifetime;

        $pattern = $this->matchIP === true
            ? '[0-9a-f]{32}'
            : '';

        $pattern = sprintf(
            '#\A%s' . $pattern . $this->sessionIDRegex . '\z#',
            preg_quote($this->cookieName, '#')
        );

        while (($file = readdir($directory)) !== false) {
            // If the filename doesn't match this pattern, it's either not a session file or is not ours
            if (! preg_match($pattern, $file)
                || ! is_file($this->savePath . DIRECTORY_SEPARATOR . $file)
                || ($mtime = filemtime($this->savePath . DIRECTORY_SEPARATOR . $file)) === false
                || $mtime > $ts
            ) {
                continue;
            }

            unlink($this->savePath . DIRECTORY_SEPARATOR . $file);
        }

        closedir($directory);

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Configure Session ID regular expression
     */
    protected function configureSessionIDRegex()
    {
        $bitsPerCharacter = (int) ini_get('session.sid_bits_per_character');
        $SIDLength        = (int) ini_get('session.sid_length');

        if (($bits = $SIDLength * $bitsPerCharacter) < 160) {
            // Add as many more characters as necessary to reach at least 160 bits
            $SIDLength += (int) ceil((160 % $bits) / $bitsPerCharacter);
            ini_set('session.sid_length', (string) $SIDLength);
        }

        // Yes, 4,5,6 are the only known possible values as of 2016-10-27
        switch ($bitsPerCharacter) {
            case 4:
                $this->sessionIDRegex = '[0-9a-f]';
                break;

            case 5:
                $this->sessionIDRegex = '[0-9a-v]';
                break;

            case 6:
                $this->sessionIDRegex = '[0-9a-zA-Z,-]';
                break;
        }

        $this->sessionIDRegex .= '{' . $SIDLength . '}';
    }
}
