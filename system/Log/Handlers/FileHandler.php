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

namespace CodeIgniter\Log\Handlers;

use DateTime;
use Exception;

/**
 * Log error messages to file system
 *
 * @see \CodeIgniter\Log\Handlers\FileHandlerTest
 */
class FileHandler extends BaseHandler
{
    /**
     * Folder to hold logs
     *
     * @var string
     */
    protected $path;

    /**
     * Extension to use for log files
     *
     * @var string
     */
    protected $fileExtension;

    /**
     * Permissions for new log files
     *
     * @var int
     */
    protected $filePermissions;

    /**
     * @param array{handles?: list<string>, path?: string, fileExtension?: string, filePermissions?: int} $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $defaults = ['path' => WRITEPATH . 'logs/', 'fileExtension' => 'log', 'filePermissions' => 0644];
        $config   = [...$defaults, ...$config];

        $this->path = $config['path'] === '' ? $defaults['path'] : $config['path'];

        $this->fileExtension = $config['fileExtension'] === ''
            ? $defaults['fileExtension']
            : ltrim($config['fileExtension'], '.');

        $this->filePermissions = $config['filePermissions'];
    }

    /**
     * Handles logging the message.
     * If the handler returns false, then execution of handlers
     * will stop. Any handlers that have not run, yet, will not
     * be run.
     *
     * @param string $level
     * @param string $message
     *
     * @throws Exception
     */
    public function handle($level, $message): bool
    {
        $filepath = $this->path . 'log-' . date('Y-m-d') . '.' . $this->fileExtension;

        $msg = '';

        $newfile = false;
        if (! is_file($filepath)) {
            $newfile = true;

            // Only add protection to php files
            if ($this->fileExtension === 'php') {
                $msg .= "<?php defined('SYSTEMPATH') || exit('No direct script access allowed'); ?>\n\n";
            }
        }

        if (! $fp = @fopen($filepath, 'ab')) {
            return false;
        }

        // Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
        if (str_contains($this->dateFormat, 'u')) {
            $microtimeFull  = microtime(true);
            $microtimeShort = sprintf('%06d', ($microtimeFull - floor($microtimeFull)) * 1_000_000);
            $date           = new DateTime(date('Y-m-d H:i:s.' . $microtimeShort, (int) $microtimeFull));
            $date           = $date->format($this->dateFormat);
        } else {
            $date = date($this->dateFormat);
        }

        $msg .= strtoupper($level) . ' - ' . $date . ' --> ' . $message . "\n";

        flock($fp, LOCK_EX);

        $result = null;

        for ($written = 0, $length = strlen($msg); $written < $length; $written += $result) {
            if (($result = fwrite($fp, substr($msg, $written))) === false) {
                // if we get this far, we'll never see this during unit testing
                break; // @codeCoverageIgnore
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        if ($newfile) {
            chmod($filepath, $this->filePermissions);
        }

        return is_int($result);
    }
}
