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

namespace CodeIgniter\Filters;

use CodeIgniter\Config\Filters as BaseFiltersConfig;
use CodeIgniter\Filters\Exceptions\FilterException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Feature;
use Config\Filters as FiltersConfig;
use Config\Modules;

/**
 * Filters
 *
 * @see \CodeIgniter\Filters\FiltersTest
 */
class Filters
{
    /**
     * The Config\Filters instance
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
     * The Config\Modules instance
     *
     * @var Modules
     */
    protected $modules;

    /**
     * Whether we've done initial processing on the filter lists.
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * The filter list to execute for the current request (URI path).
     *
     * This property is for display. Use $filtersClass to execute filters.
     * This does not include "Required Filters".
     *
     * [
     *     'before' => [
     *         'alias',
     *         'alias:arg1',
     *         'alias:arg1,arg2',
     *     ],
     *     'after'  => [
     *         'alias',
     *         'alias:arg1',
     *         'alias:arg1,arg2',
     *     ],
     * ]
     *
     * @var array{
     *     before: list<string>,
     *     after: list<string>
     * }
     */
    protected $filters = [
        'before' => [],
        'after'  => [],
    ];

    /**
     * The collection of filter classnames and their arguments to execute for
     * the current request (URI path).
     *
     * This does not include "Required Filters".
     *
     * [
     *     'before' => [
     *         [classname, arguments],
     *     ],
     *     'after'  => [
     *         [classname, arguments],
     *     ],
     * ]
     *
     * @var array{
     *     before: list<array{0: class-string, 1: list<string>}>,
     *     after: list<array{0: class-string, 1: list<string>}>
     * }
     */
    protected $filtersClass = [
        'before' => [],
        'after'  => [],
    ];

    /**
     * List of filter class instances.
     *
     * @var array<class-string, FilterInterface> [classname => instance]
     */
    protected array $filterClassInstances = [];

    /**
     * Any arguments to be passed to filters.
     *
     * @var array<string, list<string>|null> [name => params]
     *
     * @deprecated 4.6.0 No longer used.
     */
    protected $arguments = [];

    /**
     * Any arguments to be passed to filtersClass.
     *
     * @var array<class-string, list<string>|null> [classname => arguments]
     *
     * @deprecated 4.6.0 No longer used.
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

        $this->modules = $modules instanceof Modules ? $modules : new Modules();

        if ($this->modules->shouldDiscover('filters')) {
            $this->discoverFilters();
        }
    }

    /**
     * If discoverFilters is enabled in Config then system will try to
     * auto-discover custom filters files in namespaces and allow access to
     * the config object via the variable $filters as with the routes file.
     *
     * Sample:
     * $filters->aliases['custom-auth'] = \Acme\Blob\Filters\BlobAuth::class;
     *
     * @deprecated 4.4.2 Use Registrar instead.
     */
    private function discoverFilters(): void
    {
        $locator = service('locator');

        // for access by custom filters
        $filters = $this->config;

        $files = $locator->search('Config/Filters.php');

        foreach ($files as $file) {
            // The $file may not be a class file.
            $className = $locator->getClassname($file);

            // Don't include our main Filter config again...
            if ($className === FiltersConfig::class || $className === BaseFiltersConfig::class) {
                continue;
            }

            include $file;
        }
    }

    /**
     * Set the response explicitly.
     *
     * @return void
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Runs through all the filters (except "Required Filters") for the specified
     * URI and position.
     *
     * @param         string           $uri      URI path relative to baseURL
     * @phpstan-param 'before'|'after' $position
     *
     * @return RequestInterface|ResponseInterface|string|null
     *
     * @throws FilterException
     */
    public function run(string $uri, string $position = 'before')
    {
        $this->initialize(strtolower($uri));

        if ($position === 'before') {
            return $this->runBefore($this->filtersClass[$position]);
        }

        // After
        return $this->runAfter($this->filtersClass[$position]);
    }

    /**
     * @param list<array{0: class-string, 1: list<string>}> $filterClassList [[classname, arguments], ...]
     *
     * @return RequestInterface|ResponseInterface|string
     */
    private function runBefore(array $filterClassList)
    {
        foreach ($filterClassList as $filterClassInfo) {
            $className = $filterClassInfo[0];
            $arguments = ($filterClassInfo[1] === []) ? null : $filterClassInfo[1];

            $instance = $this->createFilter($className);

            $result = $instance->before($this->request, $arguments);

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

        return $this->request;
    }

    /**
     * @param list<array{0: class-string, 1: list<string>}> $filterClassList [[classname, arguments], ...]
     */
    private function runAfter(array $filterClassList): ResponseInterface
    {
        foreach ($filterClassList as $filterClassInfo) {
            $className = $filterClassInfo[0];
            $arguments = ($filterClassInfo[1] === []) ? null : $filterClassInfo[1];

            $instance = $this->createFilter($className);

            $result = $instance->after($this->request, $this->response, $arguments);

            if ($result instanceof ResponseInterface) {
                $this->response = $result;

                continue;
            }
        }

        return $this->response;
    }

    /**
     * @param class-string $className
     */
    private function createFilter(string $className): FilterInterface
    {
        if (isset($this->filterClassInstances[$className])) {
            return $this->filterClassInstances[$className];
        }

        $instance = new $className();

        if (! $instance instanceof FilterInterface) {
            throw FilterException::forIncorrectInterface($instance::class);
        }

        $this->filterClassInstances[$className] = $instance;

        return $instance;
    }

    /**
     * Returns the "Required Filters" class list.
     *
     * @phpstan-param 'before'|'after' $position
     *
     * @return list<array{0: class-string, 1: list<string>}> [[classname, arguments], ...]
     */
    public function getRequiredClasses(string $position): array
    {
        [$filters, $aliases] = $this->getRequiredFilters($position);

        if ($filters === []) {
            return [];
        }

        $filterClassList = [];

        foreach ($filters as $alias) {
            if (is_array($aliases[$alias])) {
                foreach ($this->config->aliases[$alias] as $class) {
                    $filterClassList[] = [$class, []];
                }
            } else {
                $filterClassList[] = [$aliases[$alias], []];
            }
        }

        return $filterClassList;
    }

    /**
     * Runs "Required Filters" for the specified position.
     *
     * @phpstan-param 'before'|'after' $position
     *
     * @return RequestInterface|ResponseInterface|string|null
     *
     * @throws FilterException
     *
     * @internal
     */
    public function runRequired(string $position = 'before')
    {
        $filterClassList = $this->getRequiredClasses($position);

        if ($filterClassList === []) {
            return $position === 'before' ? $this->request : $this->response;
        }

        if ($position === 'before') {
            return $this->runBefore($filterClassList);
        }

        // After
        return $this->runAfter($filterClassList);
    }

    /**
     * Returns "Required Filters" for the specified position.
     *
     * @phpstan-param 'before'|'after' $position
     *
     * @internal
     */
    public function getRequiredFilters(string $position = 'before'): array
    {
        // For backward compatibility. For users who do not update Config\Filters.
        if (! isset($this->config->required[$position])) {
            $baseConfig = config(BaseFiltersConfig::class); // @phpstan-ignore-line
            $filters    = $baseConfig->required[$position];
            $aliases    = $baseConfig->aliases;
        } else {
            $filters = $this->config->required[$position];
            $aliases = $this->config->aliases;
        }

        if ($filters === []) {
            return [[], $aliases];
        }

        if ($position === 'after') {
            if (in_array('toolbar', $this->filters['after'], true)) {
                // It was already run in globals filters. So remove it.
                $filters = $this->setToolbarToLast($filters, true);
            } else {
                // Set the toolbar filter to the last position to be executed
                $filters = $this->setToolbarToLast($filters);
            }
        }

        foreach ($filters as $alias) {
            if (! array_key_exists($alias, $aliases)) {
                throw FilterException::forNoAlias($alias);
            }
        }

        return [$filters, $aliases];
    }

    /**
     * Set the toolbar filter to the last position to be executed.
     *
     * @param list<string> $filters `after` filter array
     * @param bool         $remove  if true, remove `toolbar` filter
     */
    private function setToolbarToLast(array $filters, bool $remove = false): array
    {
        $afters = [];
        $found  = false;

        foreach ($filters as $alias) {
            if ($alias === 'toolbar') {
                $found = true;

                continue;
            }

            $afters[] = $alias;
        }

        if ($found && ! $remove) {
            $afters[] = 'toolbar';
        }

        return $afters;
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
     * @param string|null $uri URI path relative to baseURL (all lowercase)
     *
     * @TODO We don't need to accept null as $uri.
     *
     * @return Filters
     *
     * @testTag Only for test code. The run() calls this, so you don't need to
     *          call this in your app.
     */
    public function initialize(?string $uri = null)
    {
        if ($this->initialized === true) {
            return $this;
        }

        // Decode URL-encoded string
        $uri = urldecode($uri ?? '');

        $oldFilterOrder = config(Feature::class)->oldFilterOrder ?? false;
        if ($oldFilterOrder) {
            $this->processGlobals($uri);
            $this->processMethods();
            $this->processFilters($uri);
        } else {
            $this->processFilters($uri);
            $this->processMethods();
            $this->processGlobals($uri);
        }

        // Set the toolbar filter to the last position to be executed
        $this->filters['after'] = $this->setToolbarToLast($this->filters['after']);

        // Since some filters like rate limiters rely on being executed once a request,
        // we filter em here.
        $this->filters['before'] = array_unique($this->filters['before']);
        $this->filters['after']  = array_unique($this->filters['after']);

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
     * This does not include "Required Filters".
     *
     * @return array{
     *      before: list<string>,
     *      after: list<string>
     *  }
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns the filtersClass array.
     * This does not include "Required Filters".
     *
     * @return array{
     *      before: list<array{0: class-string, 1: list<string>}>,
     *      after: list<array{0: class-string, 1: list<string>}>
     *  }
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
     * @phpstan-param 'before'|'after' $position
     *
     * @return $this
     */
    public function addFilter(string $class, ?string $alias = null, string $position = 'before', string $section = 'globals')
    {
        $alias ??= md5($class);

        if (! isset($this->config->{$section})) {
            $this->config->{$section} = [];
        }

        if (! isset($this->config->{$section}[$position])) {
            $this->config->{$section}[$position] = [];
        }

        $this->config->aliases[$alias] = $class;

        $this->config->{$section}[$position][] = $alias;

        return $this;
    }

    /**
     * Ensures that a specific filter is on and enabled for the current request.
     *
     * Filters can have "arguments". This is done by placing a colon immediately
     * after the filter name, followed by a comma-separated list of arguments that
     * are passed to the filter when executed.
     *
     * @param         string           $filter   filter_name or filter_name:arguments like 'role:admin,manager'
     *                                           or filter classname.
     * @phpstan-param 'before'|'after' $position
     */
    private function enableFilter(string $filter, string $position = 'before'): void
    {
        // Normalize the arguments.
        [$alias, $arguments] = $this->getCleanName($filter);
        $filter              = ($arguments === []) ? $alias : $alias . ':' . implode(',', $arguments);

        if (class_exists($alias)) {
            $this->config->aliases[$alias] = $alias;
        } elseif (! array_key_exists($alias, $this->config->aliases)) {
            throw FilterException::forNoAlias($alias);
        }

        if (! isset($this->filters[$position][$filter])) {
            $this->filters[$position][] = $filter;
        }

        // Since some filters like rate limiters rely on being executed once a request,
        // we filter em here.
        $this->filters[$position] = array_unique($this->filters[$position]);
    }

    /**
     * Get clean name and arguments
     *
     * @param string $filter filter_name or filter_name:arguments like 'role:admin,manager'
     *
     * @return array{0: string, 1: list<string>} [name, arguments]
     */
    private function getCleanName(string $filter): array
    {
        $arguments = [];

        if (! str_contains($filter, ':')) {
            return [$filter, $arguments];
        }

        [$alias, $arguments] = explode(':', $filter);

        $arguments = explode(',', $arguments);
        array_walk($arguments, static function (&$item): void {
            $item = trim($item);
        });

        return [$alias, $arguments];
    }

    /**
     * Ensures that specific filters are on and enabled for the current request.
     *
     * Filters can have "arguments". This is done by placing a colon immediately
     * after the filter name, followed by a comma-separated list of arguments that
     * are passed to the filter when executed.
     *
     * @param list<string> $filters filter_name or filter_name:arguments like 'role:admin,manager'
     *
     * @return Filters
     */
    public function enableFilters(array $filters, string $when = 'before')
    {
        foreach ($filters as $filter) {
            $this->enableFilter($filter, $when);
        }

        return $this;
    }

    /**
     * Returns the arguments for a specified key, or all.
     *
     * @return array<string, string>|string
     *
     * @deprecated 4.6.0 Already does not work.
     */
    public function getArguments(?string $key = null)
    {
        return ((string) $key === '') ? $this->arguments : $this->arguments[$key];
    }

    // --------------------------------------------------------------------
    // Processors
    // --------------------------------------------------------------------

    /**
     * Add any applicable (not excluded) global filter settings to the mix.
     *
     * @param string|null $uri URI path relative to baseURL (all lowercase)
     *
     * @return void
     */
    protected function processGlobals(?string $uri = null)
    {
        if (! isset($this->config->globals) || ! is_array($this->config->globals)) {
            return;
        }

        $uri = strtolower(trim($uri ?? '', '/ '));

        // Add any global filters, unless they are excluded for this URI
        $sets = ['before', 'after'];

        $filters = [];

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
                            if ($this->checkExcept($uri, $check)) {
                                $keep = false;
                            }
                        }
                    } else {
                        $alias = $rules; // simple name of filter to apply
                    }

                    if ($keep) {
                        $filters[$set][] = $alias;
                    }
                }
            }
        }

        if (isset($filters['before'])) {
            $oldFilterOrder = config(Feature::class)->oldFilterOrder ?? false;
            if ($oldFilterOrder) {
                $this->filters['before'] = array_merge($this->filters['before'], $filters['before']);
            } else {
                $this->filters['before'] = array_merge($filters['before'], $this->filters['before']);
            }
        }

        if (isset($filters['after'])) {
            $this->filters['after'] = array_merge($this->filters['after'], $filters['after']);
        }
    }

    /**
     * Add any method-specific filters to the mix.
     *
     * @return void
     */
    protected function processMethods()
    {
        if (! isset($this->config->methods) || ! is_array($this->config->methods)) {
            return;
        }

        $method = $this->request->getMethod();

        $found = false;

        if (array_key_exists($method, $this->config->methods)) {
            $found = true;
        }
        // Checks lowercase HTTP method for backward compatibility.
        // @deprecated 4.5.0
        // @TODO remove this in the future.
        elseif (array_key_exists(strtolower($method), $this->config->methods)) {
            @trigger_error(
                'Setting lowercase HTTP method key "' . strtolower($method) . '" is deprecated.'
                . ' Use uppercase HTTP method like "' . strtoupper($method) . '".',
                E_USER_DEPRECATED,
            );

            $found  = true;
            $method = strtolower($method);
        }

        if ($found) {
            $oldFilterOrder = config(Feature::class)->oldFilterOrder ?? false;
            if ($oldFilterOrder) {
                $this->filters['before'] = array_merge($this->filters['before'], $this->config->methods[$method]);
            } else {
                $this->filters['before'] = array_merge($this->config->methods[$method], $this->filters['before']);
            }
        }
    }

    /**
     * Add any applicable configured filters to the mix.
     *
     * @param string|null $uri URI path relative to baseURL (all lowercase)
     *
     * @return void
     */
    protected function processFilters(?string $uri = null)
    {
        if (! isset($this->config->filters) || $this->config->filters === []) {
            return;
        }

        $uri = strtolower(trim($uri, '/ '));

        // Add any filters that apply to this URI
        $filters = [];

        foreach ($this->config->filters as $filter => $settings) {
            // Normalize the arguments.
            [$alias, $arguments] = $this->getCleanName($filter);
            $filter              = ($arguments === []) ? $alias : $alias . ':' . implode(',', $arguments);

            // Look for inclusion rules
            if (isset($settings['before'])) {
                $path = $settings['before'];

                if ($this->pathApplies($uri, $path)) {
                    $filters['before'][] = $filter;
                }
            }

            if (isset($settings['after'])) {
                $path = $settings['after'];

                if ($this->pathApplies($uri, $path)) {
                    $filters['after'][] = $filter;
                }
            }
        }

        $oldFilterOrder = config(Feature::class)->oldFilterOrder ?? false;

        if (isset($filters['before'])) {
            if ($oldFilterOrder) {
                $this->filters['before'] = array_merge($this->filters['before'], $filters['before']);
            } else {
                $this->filters['before'] = array_merge($filters['before'], $this->filters['before']);
            }
        }

        if (isset($filters['after'])) {
            if (! $oldFilterOrder) {
                $filters['after'] = array_reverse($filters['after']);
            }

            $this->filters['after'] = array_merge($this->filters['after'], $filters['after']);
        }
    }

    /**
     * Maps filter aliases to the equivalent filter classes
     *
     * @phpstan-param 'before'|'after' $position
     *
     * @return void
     *
     * @throws FilterException
     */
    protected function processAliasesToClass(string $position)
    {
        $filterClassList = [];

        foreach ($this->filters[$position] as $filter) {
            // Get arguments and clean alias
            [$alias, $arguments] = $this->getCleanName($filter);

            if (! array_key_exists($alias, $this->config->aliases)) {
                throw FilterException::forNoAlias($alias);
            }

            if (is_array($this->config->aliases[$alias])) {
                foreach ($this->config->aliases[$alias] as $class) {
                    $filterClassList[] = [$class, $arguments];
                }
            } else {
                $filterClassList[] = [$this->config->aliases[$alias], $arguments];
            }
        }

        if ($position === 'before') {
            $this->filtersClass[$position] = array_merge($filterClassList, $this->filtersClass[$position]);
        } else {
            $this->filtersClass[$position] = array_merge($this->filtersClass[$position], $filterClassList);
        }
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
        if ($paths === '' || $paths === []) {
            return true;
        }

        // make sure the paths are iterable
        if (is_string($paths)) {
            $paths = [$paths];
        }

        return $this->checkPseudoRegex($uri, $paths);
    }

    /**
     * Check except paths
     *
     * @param string       $uri   URI path relative to baseURL (all lowercase)
     * @param array|string $paths The except path patterns
     *
     * @return bool True if the URI matches except paths.
     */
    private function checkExcept(string $uri, $paths): bool
    {
        // empty array does not match anything
        if ($paths === []) {
            return false;
        }

        // make sure the paths are iterable
        if (is_string($paths)) {
            $paths = [$paths];
        }

        return $this->checkPseudoRegex($uri, $paths);
    }

    /**
     * Check the URI path as pseudo-regex
     *
     * @param string $uri   URI path relative to baseURL (all lowercase, URL-decoded)
     * @param array  $paths The except path patterns
     */
    private function checkPseudoRegex(string $uri, array $paths): bool
    {
        // treat each path as pseudo-regex
        foreach ($paths as $path) {
            // need to escape path separators
            $path = str_replace('/', '\/', trim($path, '/ '));
            // need to make pseudo wildcard real
            $path = strtolower(str_replace('*', '.*', $path));

            // Does this rule apply here?
            if (preg_match('#\A' . $path . '\z#u', $uri, $match) === 1) {
                return true;
            }
        }

        return false;
    }
}
