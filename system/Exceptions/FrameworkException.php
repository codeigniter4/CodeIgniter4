<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Exceptions;

use RuntimeException;

/**
 * Class FrameworkException
 *
 * A collection of exceptions thrown by the framework
 * that can only be determined at run time.
 */
class FrameworkException extends RuntimeException implements ExceptionInterface
{
    use DebugTraceableTrait;

    public static function forEnabledZlibOutputCompression()
    {
        return new static(lang('Core.enabledZlibOutputCompression'));
    }

    public static function forInvalidFile(string $path)
    {
        return new static(lang('Core.invalidFile', [$path]));
    }

    public static function forCopyError(string $path)
    {
        return new static(lang('Core.copyError', [$path]));
    }

    public static function forMissingExtension(string $extension)
    {
        if (strpos($extension, 'intl') !== false) {
            // @codeCoverageIgnoreStart
            $message = sprintf(
                'The framework needs the following extension(s) installed and loaded: %s.',
                $extension
            );
        // @codeCoverageIgnoreEnd
        } else {
            $message = lang('Core.missingExtension', [$extension]);
        }

        return new static($message);
    }

    public static function forNoHandlers(string $class)
    {
        return new static(lang('Core.noHandlers', [$class]));
    }

    public static function forFabricatorCreateFailed(string $table, string $reason)
    {
        return new static(lang('Fabricator.createFailed', [$table, $reason]));
    }

    public static function forMissingContext()
    {
        $message = <<<'MESSAGE'
            Context must be set before $app->run() is called. If you are upgrading from 4.1.x, you need to merge the following files:
                - public/index.php
                - spark
            from vendor/codeigniter4/framework
            MESSAGE;

        return new static($message);
    }
}
