<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Log\Handlers;

use CodeIgniter\I18n\Time;

/**
 * Class TestHandler
 *
 * A simple LogHandler that stores the logs in memory.
 * Only used for testing purposes.
 */
class TestHandler extends \CodeIgniter\Log\Handlers\FileHandler
{
    /**
     * Local storage for logs.
     *
     * @var array
     */
    protected static $logs = [];

    protected string $destination;

    /**
     * Where would the log be written?
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->handles     = $config['handles'] ?? [];
        $this->destination = $this->path . 'log-' . Time::now()->format('Y-m-d') . '.' . $this->fileExtension;

        self::$logs = [];
    }

    /**
     * Handles logging the message.
     * If the handler returns false, then execution of handlers
     * will stop. Any handlers that have not run, yet, will not
     * be run.
     *
     * @param string $level
     * @param string $message
     */
    public function handle($level, $message): bool
    {
        $date = Time::now()->format($this->dateFormat);

        self::$logs[] = strtoupper($level) . ' - ' . $date . ' --> ' . $message;

        return true;
    }

    public static function getLogs()
    {
        return self::$logs;
    }
}
