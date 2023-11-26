<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Log\Logger;

/**
 * @see \CodeIgniter\Test\TestLoggerTest
 */
class TestLogger extends Logger
{
    protected static $op_logs = [];

    /**
     * The log method is overridden so that we can store log history during
     * the tests to allow us to check ->assertLogged() methods.
     *
     * @param string $level
     * @param string $message
     */
    public function log($level, $message, array $context = []): bool
    {
        // While this requires duplicate work, we want to ensure
        // we have the final message to test against.
        $logMessage = $this->interpolate($message, $context);

        // Determine the file and line by finding the first
        // backtrace that is not part of our logging system.
        $trace = debug_backtrace();
        $file  = null;

        foreach ($trace as $row) {
            if (! in_array($row['function'], ['log', 'log_message'], true)) {
                $file = basename($row['file'] ?? '');
                break;
            }
        }

        self::$op_logs[] = [
            'level'   => $level,
            'message' => $logMessage,
            'file'    => $file,
        ];

        // Let the parent do it's thing.
        return parent::log($level, $message, $context);
    }

    /**
     * Used by CIUnitTestCase class to provide ->assertLogged() methods.
     *
     * @param string $message
     *
     * @return bool
     */
    public static function didLog(string $level, $message, bool $useExactComparison = true)
    {
        $lowerLevel = strtolower($level);

        foreach (self::$op_logs as $log) {
            if (strtolower($log['level']) !== $lowerLevel) {
                continue;
            }

            if ($useExactComparison) {
                if ($log['message'] === $message) {
                    return true;
                }

                continue;
            }

            if (strpos($log['message'], $message) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Expose filenames.
     *
     * @param string $file
     *
     * @deprecated No longer needed as underlying protected method is also deprecated.
     */
    public function cleanup($file)
    {
        return clean_path($file);
    }
}
