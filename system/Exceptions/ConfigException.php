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

namespace CodeIgniter\Exceptions;

/**
 * Exception thrown if the value of the Config class is invalid or the type is
 * incorrect.
 */
class ConfigException extends RuntimeException implements HasExitCodeInterface
{
    use DebugTraceableTrait;

    public function getExitCode(): int
    {
        return EXIT_CONFIG;
    }

    /**
     * @return static
     */
    public static function forDisabledMigrations()
    {
        return new static(lang('Migrations.disabled'));
    }
}
