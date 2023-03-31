<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View\Cells;

use CodeIgniter\Traits\PropertiesTrait;
use ReflectionClass;

/**
 * Class Cell
 *
 * The base class that View Cells should extend.
 * Provides extended features for managing/rendering
 * a single cell's contents.
 *
 * @function mount()
 */
class Cell
{
    use PropertiesTrait;

    /**
     * The name of the view to render.
     * If empty, will be determined based
     * on the cell class' name.
     */
    protected string $view = '';

    /**
     * Responsible for converting the view into HTML.
     * Expected to be overridden by the child class
     * in many occasions, but not all.
     */
    public function render(): string
    {
        if (! function_exists('decamelize')) {
            helper('inflector');
        }

        return $this->view($this->view);
    }

    /**
     * Sets the view to use when rendered.
     */
    public function setView(string $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Actually renders the view, and returns the HTML.
     * In order to provide access to public properties and methods
     * from within the view, this method extracts $data into the
     * current scope and captures the output buffer instead of
     * relying on the view service.
     */
    final protected function view(?string $view, array $data = []): string
    {
        $properties = $this->getPublicProperties();
        $properties = $this->includeComputedProperties($properties);
        $properties = array_merge($properties, $data);

        // If no view is specified, we'll try to guess it based on the class name.
        if (empty($view)) {
            // According to the docs, the name of the view file should be the
            // snake_cased version of the cell's class name, but for backward
            // compatibility, the name also accepts '_cell' being omitted.
            $ref      = new ReflectionClass($this);
            $view     = decamelize($ref->getShortName());
            $viewPath = dirname($ref->getFileName()) . DIRECTORY_SEPARATOR . $view . '.php';
            $view     = is_file($viewPath) ? $viewPath : str_replace('_cell', '', $view);
        }

        // Locate our view, preferring the directory of the class.
        if (! is_file($view)) {
            // Get the local pathname of the Cell
            $ref  = new ReflectionClass($this);
            $view = dirname($ref->getFileName()) . DIRECTORY_SEPARATOR . $view . '.php';
        }

        return (function () use ($properties, $view): string {
            extract($properties);
            ob_start();
            include $view;

            return ob_get_clean() ?: '';
        })();
    }

    /**
     * Provides capability to render on string casting.
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Allows the developer to define computed properties
     * as methods with `get` prefixed to the protected/private property name.
     */
    private function includeComputedProperties(array $properties): array
    {
        $reservedProperties = ['data', 'view'];
        $privateProperties  = $this->getNonPublicProperties();

        foreach ($privateProperties as $property) {
            $name = $property->getName();

            // don't include any methods in the base class
            if (in_array($name, $reservedProperties, true)) {
                continue;
            }

            $computedMethod = 'get' . ucfirst($name) . 'Property';

            if (method_exists($this, $computedMethod)) {
                $properties[$name] = $this->{$computedMethod}();
            }
        }

        return $properties;
    }
}
