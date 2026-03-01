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
     * Handles logging the message.
     * If the handler returns false, then execution of handlers
     * will stop. Any handlers that have not run, yet, will not
     * be run.
     *
     * @param string               $level
     * @param string               $message
     * @param array<string, mixed> $context Full context array; may contain
     *                                      GLOBAL_CONTEXT_KEY with CI global data
     */
    public function handle($level, $message, array $context = []): bool;

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
