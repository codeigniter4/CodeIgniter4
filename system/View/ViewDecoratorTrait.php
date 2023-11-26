<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\View\Exceptions\ViewException;
use Config\View as ViewConfig;

trait ViewDecoratorTrait
{
    /**
     * Runs the generated output through any declared
     * view decorators.
     */
    protected function decorateOutput(string $html): string
    {
        $decorators = \config(ViewConfig::class)->decorators;

        foreach ($decorators as $decorator) {
            if (! is_subclass_of($decorator, ViewDecoratorInterface::class)) {
                throw ViewException::forInvalidDecorator($decorator);
            }

            $html = $decorator::decorate($html);
        }

        return $html;
    }
}
