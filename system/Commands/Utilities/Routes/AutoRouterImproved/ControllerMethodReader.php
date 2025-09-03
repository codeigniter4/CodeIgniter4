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

namespace CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved;

use Config\Routing;
use ReflectionClass;
use ReflectionMethod;

/**
 * Reads a controller and returns a list of auto route listing.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved\ControllerMethodReaderTest
 */
final readonly class ControllerMethodReader
{
    private bool $translateURIDashes;
    private bool $translateUriToCamelCase;

    /**
     * @param string       $namespace   the default namespace
     * @param list<string> $httpMethods
     */
    public function __construct(
        private string $namespace,
        private array $httpMethods,
    ) {
        $config                        = config(Routing::class);
        $this->translateURIDashes      = $config->translateURIDashes;
        $this->translateUriToCamelCase = $config->translateUriToCamelCase;
    }

    /**
     * Returns found route info in the controller.
     *
     * @param class-string $class
     *
     * @return list<array<string, array|string>>
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
        $classInUri = $this->convertClassNameToUri($classname);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            foreach ($this->httpMethods as $httpVerb) {
                if (str_starts_with($methodName, strtolower($httpVerb))) {
                    // Remove HTTP verb prefix.
                    $methodInUri = $this->convertMethodNameToUri($httpVerb, $methodName);

                    // Check if it is the default method.
                    if ($methodInUri === $defaultMethod) {
                        $routeForDefaultController = $this->getRouteForDefaultController(
                            $classShortname,
                            $defaultController,
                            $classInUri,
                            $classname,
                            $methodName,
                            $httpVerb,
                            $method,
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
     * @param class-string $classname
     *
     * @return string URI path part from the folder(s) and controller
     */
    private function convertClassNameToUri(string $classname): string
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

        $classUri = rtrim($classPath, '/');

        return $this->translateToUri($classUri);
    }

    /**
     * @return string URI path part from the method
     */
    private function convertMethodNameToUri(string $httpVerb, string $methodName): string
    {
        $methodUri = lcfirst(substr($methodName, strlen($httpVerb)));

        return $this->translateToUri($methodUri);
    }

    /**
     * @param string $string classname or method name
     */
    private function translateToUri(string $string): string
    {
        if ($this->translateUriToCamelCase) {
            $string = strtolower(
                preg_replace('/([a-z\d])([A-Z])/', '$1-$2', $string),
            );
        } elseif ($this->translateURIDashes) {
            $string = str_replace('_', '-', $string);
        }

        return $string;
    }

    /**
     * Gets a route for the default controller.
     *
     * @return list<array>
     */
    private function getRouteForDefaultController(
        string $classShortname,
        string $defaultController,
        string $uriByClass,
        string $classname,
        string $methodName,
        string $httpVerb,
        ReflectionMethod $method,
    ): array {
        $output = [];

        if ($classShortname === $defaultController) {
            $pattern                = '#' . preg_quote(lcfirst($defaultController), '#') . '\z#';
            $routeWithoutController = rtrim(preg_replace($pattern, '', $uriByClass), '/');
            $routeWithoutController = $routeWithoutController !== '' && $routeWithoutController !== '0' ? $routeWithoutController : '/';

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
