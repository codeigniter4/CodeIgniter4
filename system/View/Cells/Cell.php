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

namespace CodeIgniter\View\Cells;

use CodeIgniter\Traits\PropertiesTrait;
use LogicException;
use ReflectionClass;
use Stringable;

/**
 * Class Cell
 *
 * The base class that View Cells should extend.
 * Provides extended features for managing/rendering
 * a single cell's contents.
 *
 * @function mount()
 */
class Cell implements Stringable
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
     *
     * @return $this
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
     *
     * @throws LogicException
     */
    final protected function view(?string $view, array $data = []): string
    {
        $properties = $this->getPublicProperties();
        $properties = $this->includeComputedProperties($properties);
        $properties = array_merge($properties, $data);

        $view = (string) $view;

        if ($view === '') {
            $viewName  = decamelize(class_basename(static::class));
            $directory = dirname((new ReflectionClass($this))->getFileName()) . DIRECTORY_SEPARATOR;

            $possibleView1 = $directory . substr($viewName, 0, strrpos($viewName, '_cell')) . '.php';
            $possibleView2 = $directory . $viewName . '.php';
        }

        if ($view !== '' && ! is_file($view)) {
            $directory = dirname((new ReflectionClass($this))->getFileName()) . DIRECTORY_SEPARATOR;

            $view = $directory . $view . '.php';
        }

        $candidateViews = array_filter(
            [$view, $possibleView1 ?? '', $possibleView2 ?? ''],
            static fn (string $path): bool => $path !== '' && is_file($path),
        );

        if ($candidateViews === []) {
            throw new LogicException(sprintf(
                'Cannot locate the view file for the "%s" cell.',
                static::class,
            ));
        }

        $foundView = current($candidateViews);

        return (function () use ($properties, $foundView): string {
            extract($properties);
            ob_start();
            include $foundView;

            return ob_get_clean();
        })();
    }

    /**
     * Provides capability to render on string casting.
     */
    public function __toString(): string
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
