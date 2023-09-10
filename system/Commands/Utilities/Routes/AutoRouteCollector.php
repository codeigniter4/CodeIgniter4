<?php

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
     * @var string namespace to search
     */
    private string $namespace;

    private string $defaultController;
    private string $defaultMethod;

    /**
     * @param string $namespace namespace to search
     */
    public function __construct(string $namespace, string $defaultController, string $defaultMethod)
    {
        $this->namespace         = $namespace;
        $this->defaultController = $defaultController;
        $this->defaultMethod     = $defaultMethod;
    }

    /**
     * @return array<int, array<int, string>>
     * @phpstan-return list<list<string>>
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
                $this->defaultMethod
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
