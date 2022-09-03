<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Test\Filters\CITestStreamFilter;

trait StreamFilterTrait
{
    protected function setUpStreamFilterTrait(): void
    {
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();
        CITestStreamFilter::addErrorFilter();
    }

    protected function tearDownStreamFilterTrait(): void
    {
        CITestStreamFilter::removeOutputFilter();
        CITestStreamFilter::removeErrorFilter();
    }

    protected function getStreamFilterBuffer(): string
    {
        return CITestStreamFilter::$buffer;
    }

    protected function resetStreamFilterBuffer(): void
    {
        CITestStreamFilter::$buffer = '';
    }
}
