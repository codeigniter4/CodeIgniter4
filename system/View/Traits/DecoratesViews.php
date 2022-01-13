<?php

namespace CodeIgniter\View\Traits;

use CodeIgniter\View\Exceptions\ViewException;
use CodeIgniter\View\Interfaces\ViewDecorator;

use function config;

trait DecoratesViews
{
    /**
     * Runs the generated output through and declared
     * view decorators.
     *
     * @param string $html
     *
     * @return string
     */
    private function decorateOutput(string $html): string
    {
        $decorators = config('View')->decorators;

        if (empty($decorators)) {
            return $html;
        }

        foreach ($decorators as $className) {
            $decorator = new $className();

            if (! $decorator instanceof ViewDecorator) {
                throw ViewException::forInvalidDecorator($className);
            }

            $html = $decorator->decorate($html);
        }

        return $html;
    }
}
