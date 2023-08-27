<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Config\Filters;

/**
 * @psalm-suppress UndefinedGlobalVariable
 */
$filters->aliases['test-customfilter']   = \Tests\Support\Filters\Customfilter::class;
$filters->aliases['test-redirectfilter'] = \Tests\Support\Filters\RedirectFilter::class;
