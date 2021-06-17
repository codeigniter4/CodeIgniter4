<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Log\Handlers;

/**
 * Expected behavior for a Log handler
 */
interface HandlerInterface
{
    /**
     * Handles logging the message.
     * If the handler returns false, then execution of handlers
     * will stop. Any handlers that have not run, yet, will not
     * be run.
     *
     * @param string $level
     * @param string $message
     *
     * @return bool
     */
    public function handle($level, $message): bool;

    //--------------------------------------------------------------------

    /**
     * Checks whether the Handler will handle logging items of this
     * log Level.
     *
     * @param string $level
     *
     * @return bool
     */
    public function canHandle(string $level): bool;

    //--------------------------------------------------------------------

    /**
     * Sets the preferred date format to use when logging.
     *
     * @param string $format
     *
     * @return HandlerInterface
     */
    public function setDateFormat(string $format);

    //--------------------------------------------------------------------
}
