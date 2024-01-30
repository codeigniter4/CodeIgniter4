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

namespace CodeIgniter\Entity;

/**
 * Entity encapsulation, for use with CodeIgniter\Model
 *
 * @see \CodeIgniter\Entity\EntityTest
 */
interface EntityInterface
{
    /**
     * Returns the raw values of the current attributes.
     *
     * @param bool $onlyChanged If true, only return values that have changed since object creation
     * @param bool $recursive   If true, inner entities will be cast as array as well.
     */
    public function toRawArray(bool $onlyChanged = false, bool $recursive = false): array;

    /**
     * Set raw data array without any mutations
     *
     * @return $this
     */
    public function injectRawData(array $data);
}
