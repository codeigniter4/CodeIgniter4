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

use ReflectionClass;
use ReflectionMethod;

/**
 * Reads a controller and returns a list of auto route listing.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\ControllerMethodReaderTest
 */
final class ControllerMethodReader
{
    /**
     * @var string the default namespace
     */
    private string $namespace;

    /**
     * @param string $namespace the default namespace
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @param class-string $class
     *
     * @return list<array{route: string, handler: string}>
     */
    public function read(string $class, string $defaultController = 'Home', string $defaultMethod = 'index'): array
    {
        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract()) {
            return [];
        }

        $classname      = $reflection->getName();
        $classShortname = $reflection->getShortName();

        $output     = [];
        $uriByClass = $this->getUriByClass($classname);

        if ($this->hasRemap($reflection)) {
            $methodName = '_remap';

            $routeWithoutController = $this->getRouteWithoutController(
                $classShortname,
                $defaultController,
                $uriByClass,
                $classname,
                $methodName
            );
            $output = [...$output, ...$routeWithoutController];

            $output[] = [
                'route'   => $uriByClass . '[/...]',
                'handler' => '\\' . $classname . '::' . $methodName,
            ];

            return $output;
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            $route = $uriByClass . '/' . $methodName;

            // Exclude BaseController and initController
            // See system/Config/Routes.php
            if (preg_match('#\AbaseController.*#', $route) === 1) {
                continue;
            }
            if (preg_match('#.*/initController\z#', $route) === 1) {
                continue;
            }

            if ($methodName === $defaultMethod) {
                $routeWithoutController = $this->getRouteWithoutController(
                    $classShortname,
                    $defaultController,
                    $uriByClass,
                    $classname,
                    $methodName
                );
                $output = [...$output, ...$routeWithoutController];

                $output[] = [
                    'route'   => $uriByClass,
                    'handler' => '\\' . $classname . '::' . $methodName,
                ];
            }

            $output[] = [
                'route'   => $route . '[/...]',
                'handler' => '\\' . $classname . '::' . $methodName,
            ];
        }

        return $output;
    }

    /**
     * Whether the class has a _remap() method.
     */
    private function hasRemap(ReflectionClass $class): bool
    {
        if ($class->hasMethod('_remap')) {
            $remap = $class->getMethod('_remap');

            return $remap->isPublic();
        }

        return false;
    }

    /**
     * @param class-string $classname
     *
     * @return string URI path part from the folder(s) and controller
     */
    private function getUriByClass(string $classname): string
    {
        // remove the namespace
        $pattern = '/' . preg_quote($this->namespace, '/') . '/';
        $class   = ltrim(preg_replace($pattern, '', $classname), '\\');

        $classParts = explode('\\', $class);
        $classPath  = '';

        foreach ($classParts as $part) {
            // make the first letter lowercase, because auto routing makes
            // the URI path's first letter uppercase and search the controller
            $classPath .= lcfirst($part) . '/';
        }

        return rtrim($classPath, '/');
    }

    /**
     * Gets a route without default controller.
     */
    private function getRouteWithoutController(
        string $classShortname,
        string $defaultController,
        string $uriByClass,
        string $classname,
        string $methodName
    ): array {
        if ($classShortname !== $defaultController) {
            return [];
        }

        $pattern                = '#' . preg_quote(lcfirst($defaultController), '#') . '\z#';
        $routeWithoutController = rtrim(preg_replace($pattern, '', $uriByClass), '/');
        $routeWithoutController = $routeWithoutController ?: '/';

        return [[
            'route'   => $routeWithoutController,
            'handler' => '\\' . $classname . '::' . $methodName,
        ]];
    }
}
