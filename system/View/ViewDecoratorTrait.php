<?php

namespace CodeIgniter\View;

use CodeIgniter\View\Exceptions\ViewException;

use function config;

trait ViewDecoratorTrait
{
    /**
     * Runs the generated output through and declared
     * view decorators.
     */
    private function decorateOutput(string $html): string
    {
        $decorators = config('View')->decorators;

        foreach ($decorators as $decorator) {
            if (! is_subclass_of($decorator, ViewDecoratorInterface::class)) {
                throw ViewException::forInvalidDecorator($decorator);
            }

            $html = $decorator::decorate($html);
        }

        return $html;
    }
}
