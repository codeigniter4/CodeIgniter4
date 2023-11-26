<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\View\OtherCells;

/**
 * Two classes with the same short name.
 *
 * - Tests\Support\View\SampleClass
 * - Tests\Support\View\OtherCells\SampleClass
 */
class SampleClass
{
    public function hello()
    {
        return 'Good-bye!';
    }
}
