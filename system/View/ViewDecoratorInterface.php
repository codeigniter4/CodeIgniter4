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

/**
 * View Decorators are simple classes that are given the
 * chance to modify the output from the view() calls
 * prior to it being cached.
 */
interface ViewDecoratorInterface
{
    /**
     * Takes $html and has a chance to alter it.
     * MUST return the modified HTML.
     */
    public static function decorate(string $html): string;
}
