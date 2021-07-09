<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\View\Exceptions\ViewException;
use Config\Services;
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
 */
class Cell
{
    /**
     * Instance of the current Cache Instance
     *
     * @var CacheInterface
     */
    protected $cache;

    //--------------------------------------------------------------------

    /**
     * Cell constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    //--------------------------------------------------------------------

    /**
     * Render a cell, returning its body as a string.
     *
     * @param string      $library
     * @param null        $params
     * @param int         $ttl
     * @param string|null $cacheName
     *
     * @throws ReflectionException
     *
     * @return string
     */
    public function render(string $library, $params = null, int $ttl = 0, ?string $cacheName = null): string
    {
        [$class, $method] = $this->determineClass($library);

        // Is it cached?
        $cacheName = ! empty($cacheName)
            ? $cacheName
            : str_replace(['\\', '/'], '', $class) . $method . md5(serialize($params));

        if (! empty($this->cache) && $output = $this->cache->get($cacheName)) {
            return $output;
        }

        // Not cached - so grab it...
        $instance = new $class();

        if (method_exists($instance, 'initController')) {
            $instance->initController(Services::request(), Services::response(), Services::logger());
        }

        if (! method_exists($instance, $method)) {
            throw ViewException::forInvalidCellMethod($class, $method);
        }

        // Try to match up the parameter list we were provided
        // with the parameter name in the callback method.
        $paramArray = $this->prepareParams($params);
        $refMethod  = new ReflectionMethod($instance, $method);
        $paramCount = $refMethod->getNumberOfParameters();
        $refParams  = $refMethod->getParameters();

        if ($paramCount === 0) {
            if (! empty($paramArray)) {
                throw ViewException::forMissingCellParameters($class, $method);
            }

            $output = $instance->{$method}();
        } elseif (($paramCount === 1)
            && ((! array_key_exists($refParams[0]->name, $paramArray))
            || (array_key_exists($refParams[0]->name, $paramArray)
            && count($paramArray) !== 1))
        ) {
            $output = $instance->{$method}($paramArray);
        } else {
            $fireArgs     = [];
            $methodParams = [];

            foreach ($refParams as $arg) {
                $methodParams[$arg->name] = true;
                if (array_key_exists($arg->name, $paramArray)) {
                    $fireArgs[$arg->name] = $paramArray[$arg->name];
                }
            }

            foreach (array_keys($paramArray) as $key) {
                if (! isset($methodParams[$key])) {
                    throw ViewException::forInvalidCellParameter($key);
                }
            }

            $output = $instance->{$method}(...array_values($fireArgs));
        }
        // Can we cache it?
        if (! empty($this->cache) && $ttl !== 0) {
            $this->cache->save($cacheName, $output, $ttl);
        }

        return $output;
    }

    //--------------------------------------------------------------------

    /**
     * Parses the params attribute. If an array, returns untouched.
     * If a string, it should be in the format "key1=value key2=value".
     * It will be split and returned as an array.
     *
     * @param mixed $params
     *
     * @return array|null
     */
    public function prepareParams($params)
    {
        if (empty($params) || (! is_string($params) && ! is_array($params))) {
            return [];
        }

        if (is_string($params)) {
            $newParams = [];
            $separator = ' ';

            if (strpos($params, ',') !== false) {
                $separator = ',';
            }

            $params = explode($separator, $params);
            unset($separator);

            foreach ($params as $p) {
                if (! empty($p)) {
                    [$key, $val] = explode('=', $p);

                    $newParams[trim($key)] = trim($val, ', ');
                }
            }

            $params = $newParams;
            unset($newParams);
        }

        if (is_array($params) && empty($params)) {
            return [];
        }

        return $params;
    }

    //--------------------------------------------------------------------

    /**
     * Given the library string, attempts to determine the class and method
     * to call.
     *
     * @param string $library
     *
     * @return array
     */
    protected function determineClass(string $library): array
    {
        // We don't want to actually call static methods
        // by default, so convert any double colons.
        $library = str_replace('::', ':', $library);

        [$class, $method] = explode(':', $library);

        if (empty($class)) {
            throw ViewException::forNoCellClass();
        }

        if (! class_exists($class, true)) {
            throw ViewException::forInvalidCellClass($class);
        }

        if (empty($method)) {
            $method = 'index';
        }

        return [
            $class,
            $method,
        ];
    }

    //--------------------------------------------------------------------
}
