<?php

namespace CodeIgniter\View\Cells;

use CodeIgniter\Traits\PropertiesTrait;
use ReflectionClass;

/**
 * Class Cell
 *
 * The base class that View Cells should extend.
 * Provides extended features for managing/rendering
 * a single cell's contents.
 */
class Cell
{
    use PropertiesTrait;

    /**
     * The name of the view to render.
     * If empty, will be determined based
     * on the cell class' name.
     */
    protected string $view;

    /**
     * Allows developer to do actions when the class is instantiated,
     * and before it is rendered out.
     */
    public function mount(?array $params): void {}

    /**
     * Responsible for converting the view into HTML.
     * Expected to be overridden by the child class
     * in many occassions, but not all.
     */
    public function render(): string
    {
        if (empty($this->view)) {
            $this->view = url_title((new \ReflectionClass($this))->getShortName());
        }

        return $this->view($this->view);
    }

    /**
     * Actually renders the view, and returns the HTML.
     * In order to provide access to public properties and methods
     * from within the view, this method extracts $data into the
     * current scope and captures the output buffer instead of
     * relying on the view service.
     */
    final protected function view(string $view, array $data = []): string
    {
        $properties = $this->getPublicProperties();
        $properties = $this->includeComputedProperties($properties);

        // Locate our view, prefering the directory of the class.
        $view = $this->view;
        if (! is_file($view)) {
            // Get the local pathname of the Cell
            $ref = new ReflectionClass($this);
            $view = dirname($ref->getFileName()) .'/'. $view;
        }
        dd($view, $this->view);

        $output = (function () use ($properties): string {
            extract($properties);
            ob_start();
            include $this->view;

            return ob_get_clean() ?: '';
        })();

        return $output;
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
        $privateProperties = $this->getNonPublicProperties();

        foreach ($privateProperties as $property) {
            $name = $property->getName();

            // don't include any methods in the base class
            if (in_array($name, $reservedProperties)) {
                continue;
            }

            $computedMethod = 'get'. ucfirst($name) .'Property';

            if (method_exists($this, $computedMethod)) {
                $properties[$name] = $this->$computedMethod();
            }
        }

        return $properties;
    }
}
