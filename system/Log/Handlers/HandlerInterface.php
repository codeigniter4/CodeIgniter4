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

/**
 * Expected behavior for a Log handler
 */
interface HandlerInterface
{
    /**
     * The reserved key under which global CI context data is stored
     * in the log context array. This data comes from the Context service
     * and is injected by the Logger when $logGlobalContext is enabled.
     */
    public const GLOBAL_CONTEXT_KEY = '_ci_context';

    /**
     * Returned by `handle()` to let the remaining handlers run.
     */
    public const RESULT_CONTINUE = 1;

    /**
     * Returned by `handle()` to stop the chain. Any handlers that
     * have not run, yet, will not be run.
     */
    public const RESULT_STOP = 2;

    /**
     * Handles logging the message.
     * Must return either RESULT_CONTINUE or RESULT_STOP. When RESULT_STOP
     * is returned, execution of handlers will stop and any handlers that
     * have not run, yet, will not be run. Any other value lets the
     * remaining handlers run.
     *
     * @param string               $level
     * @param string               $message
     * @param array<string, mixed> $context Full context array; may contain
     *                                      GLOBAL_CONTEXT_KEY with CI global data
     *
     * @return int One of the RESULT_* constants
     */
    public function handle($level, $message, array $context = []): int;

    /**
     * Checks whether the Handler will handle logging items of this
     * log Level.
     */
    public function canHandle(string $level): bool;

    /**
     * Sets the preferred date format to use when logging.
     *
     * @return HandlerInterface
     */
    public function setDateFormat(string $format);
}
