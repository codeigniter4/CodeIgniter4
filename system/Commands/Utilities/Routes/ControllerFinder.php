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

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Config\Services;

/**
 * Finds all controllers in a namespace for auto route listing.
 */
final class ControllerFinder
{
    /**
     * @var string namespace to search
     */
    private string $namespace;

    private FileLocator $locator;

    /**
     * @param string $namespace namespace to search
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
        $this->locator   = Services::locator();
    }

    /**
     * @return string[]
     * @phpstan-return class-string[]
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
                    /** @phpstan-var class-string $classname */
                    $classname = $classnameOrEmpty;

                    $classes[] = $classname;
                }
            }
        }

        return $classes;
    }
}
