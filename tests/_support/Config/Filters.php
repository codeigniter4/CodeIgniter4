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

namespace Tests\Support\Config\Filters;

use Tests\Support\Filters\Customfilter;
use Tests\Support\Filters\RedirectFilter;

/**
 * @psalm-suppress UndefinedGlobalVariable
 */
$filters->aliases['test-customfilter']   = Customfilter::class;
$filters->aliases['test-redirectfilter'] = RedirectFilter::class;
