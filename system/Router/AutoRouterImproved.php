<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Router;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Router\Exceptions\MethodNotFoundException;
use Config\Routing;
use ReflectionClass;
use ReflectionException;

/**
 * New Secure Router for Auto-Routing
 *
 * @see \CodeIgniter\Router\AutoRouterImprovedTest
 */
final class AutoRouterImproved implements AutoRouterInterface
{
    /**
     * List of controllers in Defined Routes that should not be accessed via this Auto-Routing.
     *
     * @var class-string[]
     */
    private array $protectedControllers;

    /**
     * Sub-directory that contains the requested controller class.
     */
    private ?string $directory = null;

    /**
     * The name of the controller class.
     */
    private string $controller;

    /**
     * The name of the method to use.
     */
    private string $method;

    /**
     * An array of params to the controller method.
     *
     * @phpstan-var list<string>
     */
    private array $params = [];

    /**
     * Whether dashes in URI's should be converted
     * to underscores when determining method names.
     */
    private bool $translateURIDashes;

    /**
     * The namespace for controllers.
     */
    private string $namespace;

    /**
     * The name of the default controller class.
     */
    private string $defaultController;

    /**
     * The name of the default method without HTTP verb prefix.
     */
    private string $defaultMethod;

    /**
     * The URI segments.
     *
     * @phpstan-var list<string>
     */
    private array $segments = [];

    /**
     * The position of the Controller in the URI segments.
     * Null for the default controller.
     */
    private ?int $controllerPos = null;

    /**
     * The position of the Method in the URI segments.
     * Null for the default method.
     */
    private ?int $methodPos = null;

    /**
     * The position of the first Parameter in the URI segments.
     * Null for the no parameters.
     */
    private ?int $paramPos = null;

    /**
     * @param class-string[] $protectedControllers
     * @param string         $defaultController    Short classname
     *
     * @deprecated $httpVerb is deprecated. No longer used.
     */
    public function __construct(// @phpstan-ignore-line
        array $protectedControllers,
        string $namespace,
        string $defaultController,
        string $defaultMethod,
        bool $translateURIDashes,
        string $httpVerb
    ) {
        $this->protectedControllers = $protectedControllers;
        $this->namespace            = rtrim($namespace, '\\');
        $this->translateURIDashes   = $translateURIDashes;
        $this->defaultController    = $defaultController;
        $this->defaultMethod        = $defaultMethod;

        // Set the default values
        $this->controller = $this->defaultController;
    }

    private function createSegments(string $uri): array
    {
        $segments = explode('/', $uri);
        $segments = array_filter($segments, static fn ($segment) => $segment !== '');

        // numerically reindex the array, removing gaps
        return array_values($segments);
    }

    /**
     * Search for the first controller corresponding to the URI segment.
     *
     * If there is a controller corresponding to the first segment, the search
     * ends there. The remaining segments are parameters to the controller.
     *
     * @return bool true if a controller class is found.
     */
    private function searchFirstController(): bool
    {
        $segments = $this->segments;

        $controller = '\\' . $this->namespace;

        $controllerPos = -1;

        while ($segments !== []) {
            $segment = array_shift($segments);
            $controllerPos++;

            $class = $this->translateURIDashes(ucfirst($segment));

            // as soon as we encounter any segment that is not PSR-4 compliant, stop searching
            if (! $this->isValidSegment($class)) {
                return false;
            }

            $controller .= '\\' . $class;

            if (class_exists($controller)) {
                $this->controller    = $controller;
                $this->controllerPos = $controllerPos;

                // The first item may be a method name.
                $this->params = $segments;
                if ($segments !== []) {
                    $this->paramPos = $this->controllerPos + 1;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Search for the last default controller corresponding to the URI segments.
     *
     * @return bool true if a controller class is found.
     */
    private function searchLastDefaultController(): bool
    {
        $segments = $this->segments;

        $segmentCount = count($this->segments);
        $paramPos     = null;
        $params       = [];

        while ($segments !== []) {
            if ($segmentCount > count($segments)) {
                $paramPos = count($segments);
            }

            $namespaces = array_map(
                fn ($segment) => $this->translateURIDashes(ucfirst($segment)),
                $segments
            );

            $controller = '\\' . $this->namespace
                . '\\' . implode('\\', $namespaces)
                . '\\' . $this->defaultController;

            if (class_exists($controller)) {
                $this->controller = $controller;
                $this->params     = $params;

                if ($params !== []) {
                    $this->paramPos = $paramPos;
                }

                return true;
            }

            // Prepend the last element in $segments to the beginning of $params.
            array_unshift($params, array_pop($segments));
        }

        // Check for the default controller in Controllers directory.
        $controller = '\\' . $this->namespace
            . '\\' . $this->defaultController;

        if (class_exists($controller)) {
            $this->controller = $controller;
            $this->params     = $params;

            if ($params !== []) {
                $this->paramPos = 0;
            }

            return true;
        }

        return false;
    }

    /**
     * Finds controller, method and params from the URI.
     *
     * @return array [directory_name, controller_name, controller_method, params]
     */
    public function getRoute(string $uri, string $httpVerb): array
    {
        $httpVerb = strtolower($httpVerb);

        // Reset Controller method params.
        $this->params = [];

        $defaultMethod = $httpVerb . ucfirst($this->defaultMethod);
        $this->method  = $defaultMethod;

        $this->segments = $this->createSegments($uri);

        // Check for Module Routes.
        if (
            $this->segments !== []
            && ($routingConfig = config(Routing::class))
            && array_key_exists($this->segments[0], $routingConfig->moduleRoutes)
        ) {
            $uriSegment      = array_shift($this->segments);
            $this->namespace = rtrim($routingConfig->moduleRoutes[$uriSegment], '\\');
        }

        if ($this->searchFirstController()) {
            // Controller is found.
            $baseControllerName = class_basename($this->controller);

            // Prevent access to default controller path
            if (
                strtolower($baseControllerName) === strtolower($this->defaultController)
            ) {
                throw new PageNotFoundException(
                    'Cannot access the default controller "' . $this->controller . '" with the controller name URI path.'
                );
            }
        } elseif ($this->searchLastDefaultController()) {
            // The default Controller is found.
            $baseControllerName = class_basename($this->controller);
        } else {
            // No Controller is found.
            throw new PageNotFoundException('No controller is found for: ' . $uri);
        }

        // The first item may be a method name.
        /** @phpstan-var list<string> $params */
        $params = $this->params;

        $methodParam = array_shift($params);

        $method = '';
        if ($methodParam !== null) {
            $method = $httpVerb . ucfirst($this->translateURIDashes($methodParam));
        }

        if ($methodParam !== null && method_exists($this->controller, $method)) {
            // Method is found.
            $this->method = $method;
            $this->params = $params;

            // Update the positions.
            $this->methodPos = $this->paramPos;
            if ($params === []) {
                $this->paramPos = null;
            }
            if ($this->paramPos !== null) {
                $this->paramPos++;
            }

            // Prevent access to default controller's method
            if (strtolower($baseControllerName) === strtolower($this->defaultController)) {
                throw new PageNotFoundException(
                    'Cannot access the default controller "' . $this->controller . '::' . $this->method . '"'
                );
            }

            // Prevent access to default method path
            if (strtolower($this->method) === strtolower($defaultMethod)) {
                throw new PageNotFoundException(
                    'Cannot access the default method "' . $this->method . '" with the method name URI path.'
                );
            }
        } elseif (method_exists($this->controller, $defaultMethod)) {
            // The default method is found.
            $this->method = $defaultMethod;
        } else {
            // No method is found.
            throw PageNotFoundException::forControllerNotFound($this->controller, $method);
        }

        // Ensure the controller is not defined in routes.
        $this->protectDefinedRoutes();

        // Ensure the controller does not have _remap() method.
        $this->checkRemap();

        // Ensure the URI segments for the controller and method do not contain
        // underscores when $translateURIDashes is true.
        $this->checkUnderscore($uri);

        // Check parameter count
        try {
            $this->checkParameters($uri);
        } catch (MethodNotFoundException $e) {
            throw PageNotFoundException::forControllerNotFound($this->controller, $this->method);
        }

        $this->setDirectory();

        return [$this->directory, $this->controller, $this->method, $this->params];
    }

    /**
     * @internal For test purpose only.
     *
     * @return array<string, int|null>
     */
    public function getPos(): array
    {
        return [
            'controller' => $this->controllerPos,
            'method'     => $this->methodPos,
            'params'     => $this->paramPos,
        ];
    }

    /**
     * Get the directory path from the controller and set it to the property.
     *
     * @return void
     */
    private function setDirectory()
    {
        $segments = explode('\\', trim($this->controller, '\\'));

        // Remove short classname.
        array_pop($segments);

        $namespaces = implode('\\', $segments);

        $dir = str_replace(
            '\\',
            '/',
            ltrim(substr($namespaces, strlen($this->namespace)), '\\')
        );

        if ($dir !== '') {
            $this->directory = $dir . '/';
        }
    }

    private function protectDefinedRoutes(): void
    {
        $controller = strtolower($this->controller);

        foreach ($this->protectedControllers as $controllerInRoutes) {
            $routeLowerCase = strtolower($controllerInRoutes);

            if ($routeLowerCase === $controller) {
                throw new PageNotFoundException(
                    'Cannot access the controller in Defined Routes. Controller: ' . $controllerInRoutes
                );
            }
        }
    }

    private function checkParameters(string $uri): void
    {
        try {
            $refClass = new ReflectionClass($this->controller);
        } catch (ReflectionException $e) {
            throw PageNotFoundException::forControllerNotFound($this->controller, $this->method);
        }

        try {
            $refMethod = $refClass->getMethod($this->method);
            $refParams = $refMethod->getParameters();
        } catch (ReflectionException $e) {
            throw new MethodNotFoundException();
        }

        if (! $refMethod->isPublic()) {
            throw new MethodNotFoundException();
        }

        if (count($refParams) < count($this->params)) {
            throw new PageNotFoundException(
                'The param count in the URI are greater than the controller method params.'
                . ' Handler:' . $this->controller . '::' . $this->method
                . ', URI:' . $uri
            );
        }
    }

    private function checkRemap(): void
    {
        try {
            $refClass = new ReflectionClass($this->controller);
            $refClass->getMethod('_remap');

            throw new PageNotFoundException(
                'AutoRouterImproved does not support `_remap()` method.'
                . ' Controller:' . $this->controller
            );
        } catch (ReflectionException $e) {
            // Do nothing.
        }
    }

    private function checkUnderscore(string $uri): void
    {
        if ($this->translateURIDashes === false) {
            return;
        }

        $paramPos = $this->paramPos ?? count($this->segments);

        for ($i = 0; $i < $paramPos; $i++) {
            if (strpos($this->segments[$i], '_') !== false) {
                throw new PageNotFoundException(
                    'AutoRouterImproved prohibits access to the URI'
                    . ' containing underscores ("' . $this->segments[$i] . '")'
                    . ' when $translateURIDashes is enabled.'
                    . ' Please use the dash.'
                    . ' Handler:' . $this->controller . '::' . $this->method
                    . ', URI:' . $uri
                );
            }
        }
    }

    /**
     * Returns true if the supplied $segment string represents a valid PSR-4 compliant namespace/directory segment
     *
     * regex comes from https://www.php.net/manual/en/language.variables.basics.php
     */
    private function isValidSegment(string $segment): bool
    {
        return (bool) preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $segment);
    }

    private function translateURIDashes(string $segment): string
    {
        return $this->translateURIDashes
            ? str_replace('-', '_', $segment)
            : $segment;
    }
}
