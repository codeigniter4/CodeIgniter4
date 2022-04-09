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
class AutoRouterImproved implements AutoRouterInterface
{
    /**
     * A RouteCollection instance.
     */
    protected RouteCollectionInterface $collection;

    /**
     * Sub-directory that contains the requested controller class.
     */
    protected ?string $directory = null;

    /**
     * Sub-namespace that contains the requested controller class.
     */
    protected ?string $subNamespace = null;

    /**
     * The name of the controller class.
     */
    protected string $controller;

    /**
     * The name of the method to use.
     */
    protected string $method;

    /**
     * An array of params to the controller method.
     */
    protected array $params = [];

    /**
     * Whether dashes in URI's should be converted
     * to underscores when determining method names.
     */
    protected bool $translateURIDashes;

    /**
     * HTTP verb for the request.
     */
    protected string $httpVerb;

    /**
     * The namespace for controllers.
     */
    protected string $namespace;

    /**
     * The name of the default controller class.
     */
    protected string $defaultController;

    /**
     * The name of the default method
     */
    protected string $defaultMethod;

    public function __construct(
        RouteCollectionInterface $routes,
        string $namespace,
        bool $translateURIDashes,
        string $httpVerb
    ) {
        $this->collection         = $routes;
        $this->namespace          = rtrim($namespace, '\\') . '\\';
        $this->translateURIDashes = $translateURIDashes;
        $this->httpVerb           = $httpVerb;

        $this->defaultController = $this->collection->getDefaultController();
        $this->defaultMethod     = $httpVerb . ucfirst($this->collection->getDefaultMethod());

        // Set the default values
        $this->controller = $this->defaultController;
        $this->method     = $this->defaultMethod;
    }

    /**
     * Finds controller, method and params from the URI.
     *
     * @return array [directory_name, controller_name, controller_method, params]
     */
    public function getRoute(string $uri): array
    {
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
            throw PageNotFoundException::forPageNotFound();
        }

        // Use the method name if it exists.
        if (! empty($nonDirSegments)) {
            $methodSegment = $this->translateURIDashes(array_shift($nonDirSegments));

            // Prefix HTTP verb
            $this->method = $this->httpVerb . ucfirst($methodSegment);

            // Prevent access to default method path
            if (strtolower($this->method) === strtolower($this->defaultMethod)) {
                throw PageNotFoundException::forPageNotFound();
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
        if ($this->httpVerb !== 'cli') {
            $controller = strtolower($this->controller);
            $methodName = strtolower($this->method);

            foreach ($this->collection->getRoutes('cli') as $route) {
                if (is_string($route)) {
                    $route = strtolower($route);
                    if (strpos(
                            $route,
                            $controller . '::' . $methodName
                        ) === 0) {
                        throw new PageNotFoundException();
                    }

                    if ($route === $controller) {
                        throw new PageNotFoundException();
                    }
                }
            }
        }

        // Check parameters
        try {
            $this->checkParameters($uri);
        } catch (ReflectionException $e) {
            throw PageNotFoundException::forControllerNotFound($this->controller, $this->method);
        }

        return [$this->directory, $this->controller, $this->method, $this->params];
    }

    private function checkParameters(string $uri)
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
            $segmentConvert = ucfirst(
                $this->translateURIDashes === true
                    ? str_replace('-', '_', $segments[0])
                    : $segments[0]
            );

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
    private function setSubNamespace(?string $namespace = null, bool $append = false, bool $validate = true)
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
