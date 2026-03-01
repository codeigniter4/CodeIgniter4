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

use JsonException;

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

    /**
     * Encodes the context array as a JSON string.
     * Returns the JSON string on success, or a descriptive error string if
     * encoding fails (e.g. context contains a resource or invalid UTF-8).
     *
     * @param array<string, mixed> $context
     */
    protected function encodeContext(array $context): string
    {
        try {
            return json_encode($context, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return '[context: JSON encoding failed - ' . $e->getMessage() . ']';
        }
    }
}
