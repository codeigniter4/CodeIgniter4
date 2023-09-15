<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved;

use ReflectionClass;
use ReflectionMethod;

/**
 * Reads a controller and returns a list of auto route listing.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved\ControllerMethodReaderTest
 */
final class ControllerMethodReader
{
    /**
     * @var string the default namespace
     */
    private string $namespace;

    /**
     * @var array<int, string>
     * @phpstan-var list<string>
     */
    private array $httpMethods;

    /**
     * @param string $namespace the default namespace
     */
    public function __construct(string $namespace, array $httpMethods)
    {
        $this->namespace   = $namespace;
        $this->httpMethods = $httpMethods;
    }

    /**
     * Returns found route info in the controller.
     *
     * @phpstan-param class-string $class
     *
     * @return array<int, array<string, array|string>>
     * @phpstan-return list<array<string, string|array>>
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
        $classInUri = $this->getUriByClass($classname);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            foreach ($this->httpMethods as $httpVerb) {
                if (strpos($methodName, $httpVerb) === 0) {
                    // Remove HTTP verb prefix.
                    $methodInUri = lcfirst(substr($methodName, strlen($httpVerb)));

                    // Check if it is the default method.
                    if ($methodInUri === $defaultMethod) {
                        $routeForDefaultController = $this->getRouteForDefaultController(
                            $classShortname,
                            $defaultController,
                            $classInUri,
                            $classname,
                            $methodName,
                            $httpVerb,
                            $method
                        );

                        if ($routeForDefaultController !== []) {
                            // The controller is the default controller. It only
                            // has a route for the default method. Other methods
                            // will not be routed even if they exist.
                            $output = [...$output, ...$routeForDefaultController];

                            continue;
                        }

                        [$params, $routeParams] = $this->getParameters($method);

                        // Route for the default method.
                        $output[] = [
                            'method'       => $httpVerb,
                            'route'        => $classInUri,
                            'route_params' => $routeParams,
                            'handler'      => '\\' . $classname . '::' . $methodName,
                            'params'       => $params,
                        ];

                        continue;
                    }

                    $route = $classInUri . '/' . $methodInUri;

                    [$params, $routeParams] = $this->getParameters($method);

                    // If it is the default controller, the method will not be
                    // routed.
                    if ($classShortname === $defaultController) {
                        $route = 'x ' . $route;
                    }

                    $output[] = [
                        'method'       => $httpVerb,
                        'route'        => $route,
                        'route_params' => $routeParams,
                        'handler'      => '\\' . $classname . '::' . $methodName,
                        'params'       => $params,
                    ];
                }
            }
        }

        return $output;
    }

    private function getParameters(ReflectionMethod $method): array
    {
        $params      = [];
        $routeParams = '';
        $refParams   = $method->getParameters();

        foreach ($refParams as $param) {
            $required = true;
            if ($param->isOptional()) {
                $required = false;

                $routeParams .= '[/..]';
            } else {
                $routeParams .= '/..';
            }

            // [variable_name => required?]
            $params[$param->getName()] = $required;
        }

        return [$params, $routeParams];
    }

    /**
     * @phpstan-param class-string $classname
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
     * Gets a route for the default controller.
     *
     * @phpstan-return list<array>
     */
    private function getRouteForDefaultController(
        string $classShortname,
        string $defaultController,
        string $uriByClass,
        string $classname,
        string $methodName,
        string $httpVerb,
        ReflectionMethod $method
    ): array {
        $output = [];

        if ($classShortname === $defaultController) {
            $pattern                = '#' . preg_quote(lcfirst($defaultController), '#') . '\z#';
            $routeWithoutController = rtrim(preg_replace($pattern, '', $uriByClass), '/');
            $routeWithoutController = $routeWithoutController ?: '/';

            [$params, $routeParams] = $this->getParameters($method);

            if ($routeWithoutController === '/' && $routeParams !== '') {
                $routeWithoutController = '';
            }

            $output[] = [
                'method'       => $httpVerb,
                'route'        => $routeWithoutController,
                'route_params' => $routeParams,
                'handler'      => '\\' . $classname . '::' . $methodName,
                'params'       => $params,
            ];
        }

        return $output;
    }
}
