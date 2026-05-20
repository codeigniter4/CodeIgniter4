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

namespace Tests\Support\Entity;

use ArrayObject;

/**
 * @extends ArrayObject<string, string>
 */
final class ArrayObjectWithToArray extends ArrayObject
{
    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['array' => 'same'];
    }
}
