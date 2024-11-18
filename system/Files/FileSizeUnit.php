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

namespace CodeIgniter\Files;

enum FileSizeUnit: int
{
    case B  = 0;
    case KB = 1;
    case MB = 2;
    case GB = 3;
    case TB = 4;
}
