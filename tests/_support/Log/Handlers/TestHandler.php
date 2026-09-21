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

namespace Tests\Support\Log\Handlers;

use CodeIgniter\I18n\Time;
use CodeIgniter\Log\Handlers\FileHandler;

/**
 * Class TestHandler
 *
 * A simple LogHandler that stores the logs in memory.
 * Only used for testing purposes.
 */
class TestHandler extends FileHandler
{
    /**
     * Local storage for logs.
     *
     * @var array
     */
    protected static $logs = [];

    /**
     * Local storage for log contexts.
     *
     * @var array<int, array<string, mixed>>
     */
    protected static array $contexts = [];

    protected string $destination;

    /**
     * Where would the log be written?
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->handles     = $config['handles'] ?? [];
        $this->destination = $this->path . 'log-' . Time::now()->format('Y-m-d') . '.' . $this->fileExtension;

        self::$logs     = [];
        self::$contexts = [];
    }

    /**
     * Handles logging the message.
     * Always lets the remaining handlers run.
     *
     * @param string               $level
     * @param string               $message
     * @param array<string, mixed> $context
     */
    public function handle($level, $message, array $context = []): int
    {
        $date = Time::now()->format($this->dateFormat);

        self::$logs[]     = strtoupper($level) . ' - ' . $date . ' --> ' . $message;
        self::$contexts[] = $context;

        return self::RESULT_CONTINUE;
    }

    public static function getLogs()
    {
        return self::$logs;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getContexts(): array
    {
        return self::$contexts;
    }
}
