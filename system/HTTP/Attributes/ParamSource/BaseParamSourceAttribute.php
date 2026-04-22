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

namespace CodeIgniter\HTTP\Attributes\ParamSource;

abstract class BaseParamSourceAttribute
{
    public function __construct(private readonly ?string $key = null)
    {
    }

    public function getKey(string $paramName): string
    {
        return $this->key ?? $paramName;
    }
}
