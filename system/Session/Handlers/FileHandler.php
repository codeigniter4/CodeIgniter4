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

use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use ReturnTypeWillChange;

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

    /**
     * Re-initialize existing session, or creates a new one.
     *
     * @param string $path The path where to store/retrieve the session
     * @param string $name The session name
     *
     * @throws SessionException
     */
    public function open($path, $name): bool
    {
        if (! is_dir($path) && ! mkdir($path, 0700, true)) {
            throw SessionException::forInvalidSavePath($this->savePath);
        }

        if (! is_writable($path)) {
            throw SessionException::forWriteProtectedSavePath($this->savePath);
        }

        $this->savePath = $path;

        // we'll use the session name as prefix to avoid collisions
        $this->filePath = $this->savePath . '/' . $name . ($this->matchIP ? md5($this->ipAddress) : '');

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
        // This might seem weird, but PHP 5.6 introduced session_reset(),
        // which re-reads session data
        if ($this->fileHandle === null) {
            $this->fileNew = ! is_file($this->filePath . $id);

            if (($this->fileHandle = fopen($this->filePath . $id, 'c+b')) === false) {
                $this->logger->error("Session: Unable to open file '" . $this->filePath . $id . "'.");

                return false;
            }

            if (flock($this->fileHandle, LOCK_EX) === false) {
                $this->logger->error("Session: Unable to obtain lock for file '" . $this->filePath . $id . "'.");
                fclose($this->fileHandle);
                $this->fileHandle = null;

                return false;
            }

            if (! isset($this->sessionID)) {
                $this->sessionID = $id;
            }

            if ($this->fileNew) {
                chmod($this->filePath . $id, 0600);
                $this->fingerprint = md5('');

                return '';
            }
        } else {
            rewind($this->fileHandle);
        }

        $data   = '';
        $buffer = 0;
        clearstatcache(); // Address https://github.com/codeigniter4/CodeIgniter4/issues/2056

        for ($read = 0, $length = filesize($this->filePath . $id); $read < $length; $read += strlen($buffer)) {
            if (($buffer = fread($this->fileHandle, $length - $read)) === false) {
                break;
            }

            $data .= $buffer;
        }

        $this->fingerprint = md5($data);

        return $data;
    }

    /**
     * Writes the session data to the session storage.
     *
     * @param string $id   The session ID
     * @param string $data The encoded session data
     */
    public function write($id, $data): bool
    {
        // If the two IDs don't match, we have a session_regenerate_id() call
        if ($id !== $this->sessionID) {
            $this->sessionID = $id;
        }

        if (! is_resource($this->fileHandle)) {
            return false;
        }

        if ($this->fingerprint === md5($data)) {
            return ($this->fileNew) ? true : touch($this->filePath . $id);
        }

        if (! $this->fileNew) {
            ftruncate($this->fileHandle, 0);
            rewind($this->fileHandle);
        }

        if (($length = strlen($data)) > 0) {
            $result = null;

            for ($written = 0; $written < $length; $written += $result) {
                if (($result = fwrite($this->fileHandle, substr($data, $written))) === false) {
                    break;
                }
            }

            if (! is_int($result)) {
                $this->fingerprint = md5(substr($data, 0, $written));
                $this->logger->error('Session: Unable to write data.');

                return false;
            }
        }

        $this->fingerprint = md5($data);

        return true;
    }

    /**
     * Closes the current session.
     */
    public function close(): bool
    {
        if (is_resource($this->fileHandle)) {
            flock($this->fileHandle, LOCK_UN);
            fclose($this->fileHandle);

            $this->fileHandle = null;
            $this->fileNew    = false;
        }

        return true;
    }

    /**
     * Destroys a session
     *
     * @param string $id The session ID being destroyed
     */
    public function destroy($id): bool
    {
        if ($this->close()) {
            return is_file($this->filePath . $id)
                ? (unlink($this->filePath . $id) && $this->destroyCookie())
                : true;
        }

        if ($this->filePath !== null) {
            clearstatcache();

            return is_file($this->filePath . $id)
                ? (unlink($this->filePath . $id) && $this->destroyCookie())
                : true;
        }

        return false;
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
        if (! is_dir($this->savePath) || ($directory = opendir($this->savePath)) === false) {
            $this->logger->debug("Session: Garbage collector couldn't list files under directory '" . $this->savePath . "'.");

            return false;
        }

        $ts = time() - $max_lifetime;

        $pattern = $this->matchIP === true ? '[0-9a-f]{32}' : '';

        $pattern = sprintf(
            '#\A%s' . $pattern . $this->sessionIDRegex . '\z#',
            preg_quote($this->cookieName, '#')
        );

        $collected = 0;

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
            $collected++;
        }

        closedir($directory);

        return $collected;
    }

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
