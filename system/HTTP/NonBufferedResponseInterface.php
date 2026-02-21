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

namespace CodeIgniter\HTTP;

/**
 * Marker interface for responses that bypass output buffering
 * and send their body directly to the client (e.g. downloads, SSE streams).
 */
interface NonBufferedResponseInterface
{
}
