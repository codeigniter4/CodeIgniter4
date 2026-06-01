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

namespace CodeIgniter\Commands\Utilities\Routes;

use CodeIgniter\Exceptions\RuntimeException;

/**
 * Internal control-flow exception raised when `PlaceholderSampleGenerator`
 * encounters a regex fragment it cannot reverse.
 *
 * The caller turns this into a ``null`` return.
 *
 * @internal
 */
final class UnsupportedPatternException extends RuntimeException
{
}
