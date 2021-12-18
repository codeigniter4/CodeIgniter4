<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Format;

/**
 * Formatter interface
 */
interface FormatterInterface
{
    /**
     * Takes the given data and formats it.
     *
     * @param array|string $data
     *
     * @return mixed
     */
    public function format($data);
}
