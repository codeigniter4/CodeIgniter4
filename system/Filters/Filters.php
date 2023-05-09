<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\Filters\Exceptions\FilterException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Filters as FiltersConfig;
use Config\Modules;
use Config\Services;

/**
 * Filters
 */
class Filters
{
    /**
     * The original config file
     *
     * @var FiltersConfig
     */
    protected $config;

    /**
     * The active IncomingRequest or CLIRequest
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * The active Response instance
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Handle to the modules config.
     *
     * @var Modules
     */
    protected $modules;

    /**
     * Whether we've done initial processing
     * on the filter lists.
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * The processed filters that will
     * be used to check against.
     *
     * @var array
     */
    protected $filters = [
        'before' => [],
        'after'  => [],
    ];

    /**
     * The collection of filters' class names that will
     * be used to execute in each position.
     *
     * @var array
     */
    protected $filtersClass = [
        'before' => [],
        'after'  => [],
    ];

    /**
     * Any arguments to be passed to filters.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Any arguments to be passed to filtersClass.
     *
     * @var array
     */
    protected $argumentsClass = [];

    /**
     * Constructor.
     *
     * @param FiltersConfig $config
     */
    public function __construct($config, RequestInterface $request, ResponseInterface $response, ?Modules $modules = null)
    {
        $this->config  = $config;
        $this->request = &$request;
        $this->setResponse($response);

        $this->modules = $modules ?? config('Modules');

        if ($this->modules->shouldDiscover('filters')) {
            $this->discoverFilters();
        }
    }

    /**
     * If discoverFilters is enabled in Config then system will try to
     * auto-discover custom filters files in Namespaces and allow access to
     * the config object via the variable $filters as with the routes file
     *
     * Sample :
     * $filters->aliases['custom-auth'] = \Acme\Blob\Filters\BlobAuth::class;
     */
    private function discoverFilters()
    {
        $locator = Services::locator();

        // for access by custom filters
        $filters = $this->config;

        $files = $locator->search('Config/Filters.php');

        foreach ($files as $file) {
            $className = $locator->getClassname($file);

            // Don't include our main Filter config again...
            if ($className === FiltersConfig::class) {
                continue;
            }

            include $file;
        }
    }

    /**
     * Set the response explicitly.
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Runs through all of the filters for the specified
     * uri and position.
     *
     * @return mixed|RequestInterface|ResponseInterface
     *
     * @throws FilterException
     */
    public function run(string $uri, string $position = 'before')
    {
        $this->initialize(strtolower($uri));

        foreach ($this->filtersClass[$position] as $className) {
            $class = new $className();

            if (! $class instanceof FilterInterface) {
                throw FilterException::forIncorrectInterface(get_class($class));
            }

            if ($position === 'before') {
                $result = $class->before($this->request, $this->argumentsClass[$className] ?? null);

                if ($result instanceof RequestInterface) {
                    $this->request = $result;

                    continue;
                }

                // If the response object was sent back,
                // then send it and quit.
                if ($result instanceof ResponseInterface) {
                    // short circuit - bypass any other filters
                    return $result;
                }
                // Ignore an empty result
                if (empty($result)) {
                    continue;
                }

                return $result;
            }

            if ($position === 'after') {
                $result = $class->after($this->request, $this->response, $this->argumentsClass[$className] ?? null);

                if ($result instanceof ResponseInterface) {
                    $this->response = $result;

                    continue;
                }
            }
        }

        return $position === 'before' ? $this->request : $this->response;
    }

    /**
     * Runs through our list of filters provided by the configuration
     * object to get them ready for use, including getting uri masks
     * to proper regex, removing those we can from the possibilities
     * based on HTTP method, etc.
     *
     * The resulting $this->filters is an array of only filters
     * that should be applied to this request.
     *
     * We go ahead and process the entire tree because we'll need to
     * run through both a before and after and don't want to double
     * process the rows.
     *
     * @return Filters
     */
    public function initialize(?string $uri = null)
    {
        if ($this->initialized === true) {
            return $this;
        }

        $this->processGlobals($uri);
        $this->processMethods();
        $this->processFilters($uri);

        // Set the toolbar filter to the last position to be executed
        if (in_array('toolbar', $this->filters['after'], true)
            && ($count = count($this->filters['after'])) > 1
            && $this->filters['after'][$count - 1] !== 'toolbar'
        ) {
            array_splice($this->filters['after'], array_search('toolbar', $this->filters['after'], true), 1);
            $this->filters['after'][] = 'toolbar';
        }

        $this->processAliasesToClass('before');
        $this->processAliasesToClass('after');

        $this->initialized = true;

        return $this;
    }

    /**
     * Restores instance to its pre-initialized state.
     * Most useful for testing so the service can be
     * re-initialized to a different path.
     */
    public function reset(): self
    {
        $this->initialized = false;

        $this->arguments = $this->argumentsClass = [];

        $this->filters = $this->filtersClass = [
            'before' => [],
            'after'  => [],
        ];

        return $this;
    }

    /**
     * Returns the processed filters array.
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns the filtersClass array.
     */
    public function getFiltersClass(): array
    {
        return $this->filtersClass;
    }

    /**
     * Adds a new alias to the config file.
     * MUST be called prior to initialize();
     * Intended for use within routes files.
     *
     * @return $this
     */
    public function addFilter(string $class, ?string $alias = null, string $when = 'before', string $section = 'globals')
    {
        $alias ??= md5($class);

        if (! isset($this->config->{$section})) {
            $this->config->{$section} = [];
        }

        if (! isset($this->config->{$section}[$when])) {
            $this->config->{$section}[$when] = [];
        }

        $this->config->aliases[$alias] = $class;

        $this->config->{$section}[$when][] = $alias;

        return $this;
    }

    /**
     * Ensures that a specific filter is on and enabled for the current request.
     *
     * Filters can have "arguments". This is done by placing a colon immediately
     * after the filter name, followed by a comma-separated list of arguments that
     * are passed to the filter when executed.
     *
     * @return Filters
     *
     * @deprecated Use enableFilters(). This method will be private.
     */
    public function enableFilter(string $name, string $when = 'before')
    {
        // Get parameters and clean name
        if (strpos($name, ':') !== false) {
            [$name, $params] = explode(':', $name);

            $params = explode(',', $params);
            array_walk($params, static function (&$item) {
                $item = trim($item);
            });

            $this->arguments[$name] = $params;
        }

        if (class_exists($name)) {
            $this->config->aliases[$name] = $name;
        } elseif (! array_key_exists($name, $this->config->aliases)) {
            throw FilterException::forNoAlias($name);
        }

        $classNames = (array) $this->config->aliases[$name];

        foreach ($classNames as $className) {
            $this->argumentsClass[$className] = $this->arguments[$name] ?? null;
        }

        if (! isset($this->filters[$when][$name])) {
            $this->filters[$when][]    = $name;
            $this->filtersClass[$when] = array_merge($this->filtersClass[$when], $classNames);
        }

        return $this;
    }

    /**
     * Ensures that specific filters are on and enabled for the current request.
     *
     * Filters can have "arguments". This is done by placing a colon immediately
     * after the filter name, followed by a comma-separated list of arguments that
     * are passed to the filter when executed.
     *
     * @return Filters
     */
    public function enableFilters(array $names, string $when = 'before')
    {
        foreach ($names as $filter) {
            $this->enableFilter($filter, $when);
        }

        return $this;
    }

    /**
     * Returns the arguments for a specified key, or all.
     *
     * @return array<string, string>|string
     */
    public function getArguments(?string $key = null)
    {
        return $key === null ? $this->arguments : $this->arguments[$key];
    }

    // --------------------------------------------------------------------
    // Processors
    // --------------------------------------------------------------------

    /**
     * Add any applicable (not excluded) global filter settings to the mix.
     *
     * @param string $uri
     */
    protected function processGlobals(?string $uri = null)
    {
        if (! isset($this->config->globals) || ! is_array($this->config->globals)) {
            return;
        }

        $uri = strtolower(trim($uri ?? '', '/ '));

        // Add any global filters, unless they are excluded for this URI
        $sets = ['before', 'after'];

        foreach ($sets as $set) {
            if (isset($this->config->globals[$set])) {
                // look at each alias in the group
                foreach ($this->config->globals[$set] as $alias => $rules) {
                    $keep = true;
                    if (is_array($rules)) {
                        // see if it should be excluded
                        if (isset($rules['except'])) {
                            // grab the exclusion rules
                            $check = $rules['except'];
                            if ($this->pathApplies($uri, $check)) {
                                $keep = false;
                            }
                        }
                    } else {
                        $alias = $rules; // simple name of filter to apply
                    }

                    if ($keep) {
                        $this->filters[$set][] = $alias;
                    }
                }
            }
        }
    }

    /**
     * Add any method-specific filters to the mix.
     */
    protected function processMethods()
    {
        if (! isset($this->config->methods) || ! is_array($this->config->methods)) {
            return;
        }

        // Request method won't be set for CLI-based requests
        $method = strtolower($this->request->getMethod()) ?? 'cli';

        if (array_key_exists($method, $this->config->methods)) {
            $this->filters['before'] = array_merge($this->filters['before'], $this->config->methods[$method]);
        }
    }

    /**
     * Add any applicable configured filters to the mix.
     *
     * @param string $uri
     */
    protected function processFilters(?string $uri = null)
    {
        if (! isset($this->config->filters) || ! $this->config->filters) {
            return;
        }

        $uri = strtolower(trim($uri, '/ '));

        // Add any filters that apply to this URI
        foreach ($this->config->filters as $alias => $settings) {
            // Look for inclusion rules
            if (isset($settings['before'])) {
                $path = $settings['before'];
                if ($this->pathApplies($uri, $path)) {
                    $this->filters['before'][] = $alias;
                }
            }

            if (isset($settings['after'])) {
                $path = $settings['after'];
                if ($this->pathApplies($uri, $path)) {
                    $this->filters['after'][] = $alias;
                }
            }
        }
    }

    /**
     * Maps filter aliases to the equivalent filter classes
     *
     * @throws FilterException
     */
    protected function processAliasesToClass(string $position)
    {
        foreach ($this->filters[$position] as $alias => $rules) {
            if (is_numeric($alias) && is_string($rules)) {
                $alias = $rules;
            }

            if (! array_key_exists($alias, $this->config->aliases)) {
                throw FilterException::forNoAlias($alias);
            }

            if (is_array($this->config->aliases[$alias])) {
                $this->filtersClass[$position] = array_merge($this->filtersClass[$position], $this->config->aliases[$alias]);
            } else {
                $this->filtersClass[$position][] = $this->config->aliases[$alias];
            }
        }

        // when using enableFilter() we already write the class name in ->filtersClass as well as the
        // alias in ->filters. This leads to duplicates when using route filters.
        // Since some filters like rate limiters rely on being executed once a request we filter em here.
        $this->filtersClass[$position] = array_values(array_unique($this->filtersClass[$position]));
    }

    /**
     * Check paths for match for URI
     *
     * @param string       $uri   URI to test against
     * @param array|string $paths The path patterns to test
     *
     * @return bool True if any of the paths apply to the URI
     */
    private function pathApplies(string $uri, $paths)
    {
        // empty path matches all
        if (empty($paths)) {
            return true;
        }

        // make sure the paths are iterable
        if (is_string($paths)) {
            $paths = [$paths];
        }

        // treat each paths as pseudo-regex
        foreach ($paths as $path) {
            // need to escape path separators
            $path = str_replace('/', '\/', trim($path, '/ '));
            // need to make pseudo wildcard real
            $path = strtolower(str_replace('*', '.*', $path));
            // Does this rule apply here?
            if (preg_match('#^' . $path . '$#', $uri, $match) === 1) {
                return true;
            }
        }

        return false;
    }
}
