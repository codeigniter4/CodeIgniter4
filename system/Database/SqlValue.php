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
 * @interal
 */
class SqlValue
{
    /**
     * @var string Escaped column value.
     */
    private string $value;

    /**
     * @var string|null Column type.
     */
    private ?string $type;

    /**
     * @param string      $value Escaped column value.
     * @param string|null $type  Column type.
     */
    public function __construct(string $value, ?string $type = null)
    {
        $this->value = $value;
        $this->type  = $type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
