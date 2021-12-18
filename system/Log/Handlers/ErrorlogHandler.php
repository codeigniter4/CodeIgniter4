<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Log\Exceptions\LogException;

/**
 * Log handler that writes to PHP's `error_log()`
 */
class ErrorlogHandler extends BaseHandler
{
    /**
     * Message is sent to PHP's system logger, using the Operating System's
     * system logging mechanism or a file, depending on what the error_log
     * configuration directive is set to.
     */
    public const TYPE_OS = 0;

    /**
     * Message is sent directly to the SAPI logging handler.
     */
    public const TYPE_SAPI = 4;

    /**
     * Says where the error should go. Currently supported are
     * 0 (`TYPE_OS`) and 4 (`TYPE_SAPI`).
     *
     * @var int
     */
    protected $messageType = 0;

    /**
     * Constructor.
     *
     * @param mixed[] $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $messageType = $config['messageType'] ?? self::TYPE_OS;

        if (! is_int($messageType) || ! in_array($messageType, [self::TYPE_OS, self::TYPE_SAPI], true)) {
            throw LogException::forInvalidMessageType(print_r($messageType, true));
        }

        $this->messageType = $messageType;
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
        $message = strtoupper($level) . ' --> ' . $message . "\n";

        return $this->errorLog($message, $this->messageType);
    }

    /**
     * Extracted call to `error_log()` in order to be tested.
     *
     * @codeCoverageIgnore
     */
    protected function errorLog(string $message, int $messageType): bool
    {
        return error_log($message, $messageType);
    }
}
