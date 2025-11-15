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
 * Base class for logging
 */
abstract class BaseHandler implements HandlerInterface
{
    /**
     * Handles
     *
     * @var list<string>
     */
    protected $handles;

    /**
     * Date format for logging
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * @param array{handles?: list<string>} $config
     */
    public function __construct(array $config)
    {
        $this->handles = $config['handles'] ?? [];
    }

    /**
     * Checks whether the Handler will handle logging items of this
     * log Level.
     */
    public function canHandle(string $level): bool
    {
        return in_array($level, $this->handles, true);
    }

    /**
     * Stores the date format to use while logging messages.
     */
    public function setDateFormat(string $format): HandlerInterface
    {
        $this->dateFormat = $format;

        return $this;
    }
}
