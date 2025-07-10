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

namespace CodeIgniter\View;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\Factories;
use CodeIgniter\View\Cells\Cell as BaseCell;
use CodeIgniter\View\Exceptions\ViewException;
use ReflectionException;
use ReflectionMethod;

/**
 * Class Cell
 *
 * A simple class that can call any other class that can be loaded,
 * and echo out it's result. Intended for displaying small blocks of
 * content within views that can be managed by other libraries and
 * not require they are loaded within controller.
 *
 * Used with the helper function, it's use will look like:
 *
 *         viewCell('\Some\Class::method', 'limit=5 sort=asc', 60, 'cache-name');
 *
 * Parameters are matched up with the callback method's arguments of the same name:
 *
 *         class Class {
 *             function method($limit, $sort)
 *         }
 *
 * Alternatively, the params will be passed into the callback method as a simple array
 * if matching params are not found.
 *
 *         class Class {
 *             function method(array $params=null)
 *         }
 *
 * @see \CodeIgniter\View\CellTest
 */
class Cell
{
    /**
     * Instance of the current Cache Instance
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Cell constructor.
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Render a cell, returning its body as a string.
     *
     * @param string                            $library   Cell class and method name.
     * @param array<string, string>|string|null $params    Parameters to pass to the method.
     * @param int                               $ttl       Number of seconds to cache the cell.
     * @param string|null                       $cacheName Cache item name.
     *
     * @throws ReflectionException
     */
    public function render(string $library, $params = null, int $ttl = 0, ?string $cacheName = null): string
    {
        [$instance, $method] = $this->determineClass($library);

        $class = is_object($instance)
            ? $instance::class
            : null;

        $params = $this->prepareParams($params);

        // Is the output cached?
        $cacheName ??= str_replace(['\\', '/'], '', $class) . $method . md5(serialize($params));

        $output = $this->cache->get($cacheName);

        if (is_string($output) && $output !== '') {
            return $output;
        }

        if (method_exists($instance, 'initController')) {
            $instance->initController(service('request'), service('response'), service('logger'));
        }

        if (! method_exists($instance, $method)) {
            throw ViewException::forInvalidCellMethod($class, $method);
        }

        $output = $instance instanceof BaseCell
            ? $this->renderCell($instance, $method, $params)
            : $this->renderSimpleClass($instance, $method, $params, $class);

        // Can we cache it?
        if ($ttl !== 0) {
            $this->cache->save($cacheName, $output, $ttl);
        }

        return $output;
    }

    /**
     * Parses the params attribute. If an array, returns untouched.
     * If a string, it should be in the format "key1=value key2=value".
     * It will be split and returned as an array.
     *
     * @param array<string, string>|float|string|null $params
     *
     * @return array<string, string>
     */
    public function prepareParams($params)
    {
        if (
            ($params === null || $params === '' || $params === [])
            || (! is_string($params) && ! is_array($params))
        ) {
            return [];
        }

        if (is_string($params)) {
            $newParams = [];
            $separator = ' ';

            if (str_contains($params, ',')) {
                $separator = ',';
            }

            $params = explode($separator, $params);
            unset($separator);

            foreach ($params as $p) {
                if ($p !== '') {
                    [$key, $val] = explode('=', $p);

                    $newParams[trim($key)] = trim($val, ', ');
                }
            }

            $params = $newParams;
            unset($newParams);
        }

        if ($params === []) {
            return [];
        }

        return $params;
    }

    /**
     * Given the library string, attempts to determine the class and method
     * to call.
     */
    protected function determineClass(string $library): array
    {
        // We don't want to actually call static methods
        // by default, so convert any double colons.
        $library = str_replace('::', ':', $library);

        // controlled cells might be called with just
        // the class name, so add a default method
        if (! str_contains($library, ':')) {
            $library .= ':render';
        }

        [$class, $method] = explode(':', $library);

        if ($class === '') {
            throw ViewException::forNoCellClass();
        }

        // locate and return an instance of the cell
        $object = Factories::cells($class, ['getShared' => false]);

        if (! is_object($object)) {
            throw ViewException::forInvalidCellClass($class);
        }

        if ($method === '') {
            $method = 'index';
        }

        return [
            $object,
            $method,
        ];
    }

    /**
     * Renders a cell that extends the BaseCell class.
     */
    final protected function renderCell(BaseCell $instance, string $method, array $params): string
    {
        // Only allow public properties to be set, or protected/private
        // properties that have a method to get them (get<Foo>Property())
        $publicProperties  = $instance->getPublicProperties();
        $privateProperties = array_column($instance->getNonPublicProperties(), 'name');
        $publicParams      = array_intersect_key($params, $publicProperties);

        foreach ($params as $key => $value) {
            $getter = 'get' . ucfirst((string) $key) . 'Property';
            if (in_array($key, $privateProperties, true) && method_exists($instance, $getter)) {
                $publicParams[$key] = $value;
            }
        }

        // Fill in any public properties that were passed in
        // but only ones that are in the $pulibcProperties array.
        $instance = $instance->fill($publicParams);

        // If there are any protected/private properties, we need to
        // send them to the mount() method.
        if (method_exists($instance, 'mount')) {
            // if any $params have keys that match the name of an argument in the
            // mount method, pass those variables to the method.
            $mountParams = $this->getMethodParams($instance, 'mount', $params);
            $instance->mount(...$mountParams);
        }

        return $instance->{$method}();
    }

    /**
     * Returns the values from $params that match the parameters
     * for a method, in the order they are defined. This allows
     * them to be passed directly into the method.
     */
    private function getMethodParams(BaseCell $instance, string $method, array $params): array
    {
        $mountParams = [];

        try {
            $reflectionMethod = new ReflectionMethod($instance, $method);
            $reflectionParams = $reflectionMethod->getParameters();

            foreach ($reflectionParams as $reflectionParam) {
                $paramName = $reflectionParam->getName();

                if (array_key_exists($paramName, $params)) {
                    $mountParams[] = $params[$paramName];
                }
            }
        } catch (ReflectionException) {
            // do nothing
        }

        return $mountParams;
    }

    /**
     * Renders the non-Cell class, passing in the string/array params.
     *
     * @todo Determine if this can be refactored to use $this-getMethodParams().
     *
     * @param object $instance
     */
    final protected function renderSimpleClass($instance, string $method, array $params, string $class): string
    {
        // Try to match up the parameter list we were provided
        // with the parameter name in the callback method.
        $refMethod  = new ReflectionMethod($instance, $method);
        $paramCount = $refMethod->getNumberOfParameters();
        $refParams  = $refMethod->getParameters();

        if ($paramCount === 0) {
            if ($params !== []) {
                throw ViewException::forMissingCellParameters($class, $method);
            }

            $output = $instance->{$method}();
        } elseif (($paramCount === 1)
            && ((! array_key_exists($refParams[0]->name, $params))
            || (array_key_exists($refParams[0]->name, $params)
            && count($params) !== 1))
        ) {
            $output = $instance->{$method}($params);
        } else {
            $fireArgs     = [];
            $methodParams = [];

            foreach ($refParams as $arg) {
                $methodParams[$arg->name] = true;
                if (array_key_exists($arg->name, $params)) {
                    $fireArgs[$arg->name] = $params[$arg->name];
                }
            }

            foreach (array_keys($params) as $key) {
                if (! isset($methodParams[$key])) {
                    throw ViewException::forInvalidCellParameter($key);
                }
            }

            $output = $instance->{$method}(...array_values($fireArgs));
        }

        return $output;
    }
}
