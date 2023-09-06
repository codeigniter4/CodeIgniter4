<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

/**
 * @see \CodeIgniter\Database\RawSqlTest
 */
class RawSql
{
    /**
     * @var string Raw SQL string
     */
    private string $string;

    public function __construct(string $sqlString)
    {
        $this->string = $sqlString;
    }

    public function __toString(): string
    {
        return $this->string;
    }

    /**
     * Create new instance with new SQL string
     */
    public function with(string $newSqlString): self
    {
        $new         = clone $this;
        $new->string = $newSqlString;

        return $new;
    }

    /**
     * Returns unique id for binding key
     */
    public function getBindingKey(): string
    {
        return 'RawSql' . spl_object_id($this);
    }
}
