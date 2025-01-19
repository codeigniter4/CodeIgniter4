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

use CodeIgniter\Autoloader\FileLocatorInterface;

/**
 * Finds all controllers in a namespace for auto route listing.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\ControllerFinderTest
 */
final class ControllerFinder
{
    private readonly FileLocatorInterface $locator;

    /**
     * @param string $namespace namespace to search
     */
    public function __construct(
        private readonly string $namespace,
    ) {
        $this->locator = service('locator');
    }

    /**
     * @return list<class-string>
     */
    public function find(): array
    {
        $nsArray = explode('\\', trim($this->namespace, '\\'));
        $count   = count($nsArray);
        $ns      = '';
        $files   = [];

        for ($i = 0; $i < $count; $i++) {
            $ns .= '\\' . array_shift($nsArray);
            $path = implode('\\', $nsArray);

            $files = $this->locator->listNamespaceFiles($ns, $path);

            if ($files !== []) {
                break;
            }
        }

        $classes = [];

        foreach ($files as $file) {
            if (\is_file($file)) {
                $classnameOrEmpty = $this->locator->getClassname($file);

                if ($classnameOrEmpty !== '') {
                    /** @var class-string $classname */
                    $classname = $classnameOrEmpty;

                    $classes[] = $classname;
                }
            }
        }

        return $classes;
    }
}
