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

namespace CodeIgniter;

use CodeIgniter\Exceptions\InvalidArgumentException;

/**
 * Provides a simple way to determine the current environment of the application.
 *
 * Primarily intended as a mockable seam for testing environment-specific code
 * paths that resolve this class via the `environment` service.
 *
 * It does not redefine the `ENVIRONMENT` constant. It affects only code paths
 * that resolve and use this class, while code that still reads `ENVIRONMENT`
 * directly keeps its current behavior.
 *
 * For custom environment names beyond the built-in production/development/testing,
 * use {@see self::is()}.
 *
 * @see \CodeIgniter\EnvironmentDetectorTest
 */
final readonly class EnvironmentDetector
{
    private string $environment;

    /**
     * @param non-empty-string|null $environment The environment to use, or null to
     *                                           fall back to the `ENVIRONMENT` constant.
     */
    public function __construct(?string $environment = null)
    {
        $environment = $environment !== null ? trim($environment) : ENVIRONMENT;

        if ($environment === '') {
            throw new InvalidArgumentException('Environment cannot be an empty string.');
        }

        $this->environment = $environment;
    }

    public function get(): string
    {
        return $this->environment;
    }

    /**
     * Checks if the current environment matches any of the given environments.
     *
     * @param string ...$environments One or more environment names to check against.
     */
    public function is(string ...$environments): bool
    {
        return in_array($this->environment, $environments, true);
    }

    public function isProduction(): bool
    {
        return $this->is('production');
    }

    public function isDevelopment(): bool
    {
        return $this->is('development');
    }

    public function isTesting(): bool
    {
        return $this->is('testing');
    }
}
