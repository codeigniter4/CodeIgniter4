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

namespace CodeIgniter\Commands\Utilities\Routes;

/**
 * Collects data for auto route listing.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\AutoRouteCollectorTest
 */
final class AutoRouteCollector
{
    /**
     * @param string $namespace namespace to search
     */
    public function __construct(private readonly string $namespace, private readonly string $defaultController, private readonly string $defaultMethod)
    {
    }

    /**
     * @return list<list<string>>
     */
    public function get(): array
    {
        $finder = new ControllerFinder($this->namespace);
        $reader = new ControllerMethodReader($this->namespace);

        $tbody = [];

        foreach ($finder->find() as $class) {
            $output = $reader->read(
                $class,
                $this->defaultController,
                $this->defaultMethod,
            );

            foreach ($output as $item) {
                $tbody[] = [
                    'auto',
                    $item['route'],
                    '',
                    $item['handler'],
                ];
            }
        }

        return $tbody;
    }
}
