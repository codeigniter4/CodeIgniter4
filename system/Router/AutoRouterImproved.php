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
use ReflectionClass;
use ReflectionException;

/**
 * New Secure Router for Auto-Routing
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
     * Sub-namespace that contains the requested controller class.
     */
    private ?string $subNamespace = null;

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
        $this->namespace            = rtrim($namespace, '\\') . '\\';
        $this->translateURIDashes   = $translateURIDashes;
        $this->defaultController    = $defaultController;
        $this->defaultMethod        = $defaultMethod;

        // Set the default values
        $this->controller = $this->defaultController;
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

        $segments = explode('/', $uri);

        // WARNING: Directories get shifted out of the segments array.
        $nonDirSegments = $this->scanControllers($segments);

        $controllerSegment  = '';
        $baseControllerName = $this->defaultController;

        // If we don't have any segments left - use the default controller;
        // If not empty, then the first segment should be the controller
        if (! empty($nonDirSegments)) {
            $controllerSegment = array_shift($nonDirSegments);

            $baseControllerName = $this->translateURIDashes(ucfirst($controllerSegment));
        }

        if (! $this->isValidSegment($baseControllerName)) {
            throw new PageNotFoundException($baseControllerName . ' is not a valid controller name');
        }

        // Prevent access to default controller path
        if (
            strtolower($baseControllerName) === strtolower($this->defaultController)
            && strtolower($controllerSegment) === strtolower($this->defaultController)
        ) {
            throw new PageNotFoundException(
                'Cannot access the default controller "' . $baseControllerName . '" with the controller name URI path.'
            );
        }

        // Use the method name if it exists.
        if (! empty($nonDirSegments)) {
            $methodSegment = $this->translateURIDashes(array_shift($nonDirSegments));

            // Prefix HTTP verb
            $this->method = $httpVerb . ucfirst($methodSegment);

            // Prevent access to default method path
            if (strtolower($this->method) === strtolower($defaultMethod)) {
                throw new PageNotFoundException(
                    'Cannot access the default method "' . $this->method . '" with the method name URI path.'
                );
            }
        }

        if (! empty($nonDirSegments)) {
            $this->params = $nonDirSegments;
        }

        // Ensure the controller stores the fully-qualified class name
        $this->controller = '\\' . ltrim(
            str_replace(
                '/',
                '\\',
                $this->namespace . $this->subNamespace . $baseControllerName
            ),
            '\\'
        );

        // Ensure routes registered via $routes->cli() are not accessible via web.
        $this->protectDefinedRoutes();

        // Check _remap()
        $this->checkRemap();

        // Check parameters
        try {
            $this->checkParameters($uri);
        } catch (ReflectionException $e) {
            throw PageNotFoundException::forControllerNotFound($this->controller, $this->method);
        }

        return [$this->directory, $this->controller, $this->method, $this->params];
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
        $refClass  = new ReflectionClass($this->controller);
        $refMethod = $refClass->getMethod($this->method);
        $refParams = $refMethod->getParameters();

        if (! $refMethod->isPublic()) {
            throw PageNotFoundException::forMethodNotFound($this->method);
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

    /**
     * Scans the controller directory, attempting to locate a controller matching the supplied uri $segments
     *
     * @param array $segments URI segments
     *
     * @return array returns an array of remaining uri segments that don't map onto a directory
     */
    private function scanControllers(array $segments): array
    {
        $segments = array_filter($segments, static fn ($segment) => $segment !== '');
        // numerically reindex the array, removing gaps
        $segments = array_values($segments);

        // Loop through our segments and return as soon as a controller
        // is found or when such a directory doesn't exist
        $c = count($segments);

        while ($c-- > 0) {
            $segmentConvert = $this->translateURIDashes(ucfirst($segments[0]));

            // as soon as we encounter any segment that is not PSR-4 compliant, stop searching
            if (! $this->isValidSegment($segmentConvert)) {
                return $segments;
            }

            $test = $this->namespace . $this->subNamespace . $segmentConvert;

            // as long as each segment is *not* a controller file, add it to $this->subNamespace
            if (! class_exists($test)) {
                $this->setSubNamespace($segmentConvert, true, false);
                array_shift($segments);

                $this->directory .= $this->directory . $segmentConvert . '/';

                continue;
            }

            return $segments;
        }

        // This means that all segments were actually directories
        return $segments;
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

    /**
     * Sets the sub-namespace that the controller is in.
     *
     * @param bool $validate if true, checks to make sure $dir consists of only PSR4 compliant segments
     */
    private function setSubNamespace(?string $namespace = null, bool $append = false, bool $validate = true): void
    {
        if ($validate) {
            $segments = explode('/', trim($namespace, '/'));

            foreach ($segments as $segment) {
                if (! $this->isValidSegment($segment)) {
                    return;
                }
            }
        }

        if ($append !== true || empty($this->subNamespace)) {
            $this->subNamespace = trim($namespace, '/') . '\\';
        } else {
            $this->subNamespace .= trim($namespace, '/') . '\\';
        }
    }

    private function translateURIDashes(string $classname): string
    {
        return $this->translateURIDashes
            ? str_replace('-', '_', $classname)
            : $classname;
    }
}
