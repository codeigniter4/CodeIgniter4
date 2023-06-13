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

/**
 * Router for Auto-Routing
 */
final class AutoRouter implements AutoRouterInterface
{
    /**
     * List of controllers registered for the CLI verb that should not be accessed in the web.
     *
     * @var class-string[]
     */
    private array $protectedControllers;

    /**
     * Sub-directory that contains the requested controller class.
     * Primarily used by 'autoRoute'.
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
     * Whether dashes in URI's should be converted
     * to underscores when determining method names.
     */
    private bool $translateURIDashes;

    /**
     * HTTP verb for the request.
     */
    private string $httpVerb;

    /**
     * Default namespace for controllers.
     */
    private string $defaultNamespace;

    public function __construct(
        array $protectedControllers,
        string $defaultNamespace,
        string $defaultController,
        string $defaultMethod,
        bool $translateURIDashes,
        string $httpVerb
    ) {
        $this->protectedControllers = $protectedControllers;
        $this->defaultNamespace     = $defaultNamespace;
        $this->translateURIDashes   = $translateURIDashes;
        $this->httpVerb             = $httpVerb;

        $this->controller = $defaultController;
        $this->method     = $defaultMethod;
    }

    /**
     * Attempts to match a URI path against Controllers and directories
     * found in APPPATH/Controllers, to find a matching route.
     *
     * @return array [directory_name, controller_name, controller_method, params]
     */
    public function getRoute(string $uri, string $httpVerb): array
    {
        $segments = explode('/', $uri);

        // WARNING: Directories get shifted out of the segments array.
        $segments = $this->scanControllers($segments);

        // If we don't have any segments left - use the default controller;
        // If not empty, then the first segment should be the controller
        if (! empty($segments)) {
            $this->controller = ucfirst(array_shift($segments));
        }

        $controllerName = $this->controllerName();

        if (! $this->isValidSegment($controllerName)) {
            throw new PageNotFoundException($this->controller . ' is not a valid controller name');
        }

        // Use the method name if it exists.
        // If it doesn't, no biggie - the default method name
        // has already been set.
        if (! empty($segments)) {
            $this->method = array_shift($segments) ?: $this->method;
        }

        // Prevent access to initController method
        if (strtolower($this->method) === 'initcontroller') {
            throw PageNotFoundException::forPageNotFound();
        }

        /** @var array $params An array of params to the controller method. */
        $params = [];

        if (! empty($segments)) {
            $params = $segments;
        }

        // Ensure routes registered via $routes->cli() are not accessible via web.
        if ($this->httpVerb !== 'cli') {
            $controller = '\\' . $this->defaultNamespace;

            $controller .= $this->directory ? str_replace('/', '\\', $this->directory) : '';
            $controller .= $controllerName;

            $controller = strtolower($controller);

            foreach ($this->protectedControllers as $controllerInRoute) {
                if (! is_string($controllerInRoute)) {
                    continue;
                }
                if (strtolower($controllerInRoute) !== $controller) {
                    continue;
                }

                throw new PageNotFoundException(
                    'Cannot access the controller in a CLI Route. Controller: ' . $controllerInRoute
                );
            }
        }

        // Load the file so that it's available for CodeIgniter.
        $file = APPPATH . 'Controllers/' . $this->directory . $controllerName . '.php';
        if (is_file($file)) {
            include_once $file;
        }

        // Ensure the controller stores the fully-qualified class name
        // We have to check for a length over 1, since by default it will be '\'
        if (strpos($this->controller, '\\') === false && strlen($this->defaultNamespace) > 1) {
            $this->controller = '\\' . ltrim(
                str_replace(
                    '/',
                    '\\',
                    $this->defaultNamespace . $this->directory . $controllerName
                ),
                '\\'
            );
        }

        return [$this->directory, $this->controllerName(), $this->methodName(), $params];
    }

    /**
     * Tells the system whether we should translate URI dashes or not
     * in the URI from a dash to an underscore.
     *
     * @deprecated This method should be removed.
     */
    public function setTranslateURIDashes(bool $val = false): self
    {
        $this->translateURIDashes = $val;

        return $this;
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

        // if a prior directory value has been set, just return segments and get out of here
        if (isset($this->directory)) {
            return $segments;
        }

        // Loop through our segments and return as soon as a controller
        // is found or when such a directory doesn't exist
        $c = count($segments);

        while ($c-- > 0) {
            $segmentConvert = ucfirst(
                $this->translateURIDashes ? str_replace('-', '_', $segments[0]) : $segments[0]
            );
            // as soon as we encounter any segment that is not PSR-4 compliant, stop searching
            if (! $this->isValidSegment($segmentConvert)) {
                return $segments;
            }

            $test = APPPATH . 'Controllers/' . $this->directory . $segmentConvert;

            // as long as each segment is *not* a controller file but does match a directory, add it to $this->directory
            if (! is_file($test . '.php') && is_dir($test)) {
                $this->setDirectory($segmentConvert, true, false);
                array_shift($segments);

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
     * Sets the sub-directory that the controller is in.
     *
     * @param bool $validate if true, checks to make sure $dir consists of only PSR4 compliant segments
     *
     * @deprecated This method should be removed.
     */
    public function setDirectory(?string $dir = null, bool $append = false, bool $validate = true)
    {
        if (empty($dir)) {
            $this->directory = null;

            return;
        }

        if ($validate) {
            $segments = explode('/', trim($dir, '/'));

            foreach ($segments as $segment) {
                if (! $this->isValidSegment($segment)) {
                    return;
                }
            }
        }

        if ($append !== true || empty($this->directory)) {
            $this->directory = trim($dir, '/') . '/';
        } else {
            $this->directory .= trim($dir, '/') . '/';
        }
    }

    /**
     * Returns the name of the sub-directory the controller is in,
     * if any. Relative to APPPATH.'Controllers'.
     *
     * @deprecated This method should be removed.
     */
    public function directory(): string
    {
        return ! empty($this->directory) ? $this->directory : '';
    }

    private function controllerName(): string
    {
        return $this->translateURIDashes
            ? str_replace('-', '_', $this->controller)
            : $this->controller;
    }

    private function methodName(): string
    {
        return $this->translateURIDashes
            ? str_replace('-', '_', $this->method)
            : $this->method;
    }
}
