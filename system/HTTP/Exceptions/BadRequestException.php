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

namespace CodeIgniter\HTTP\Exceptions;

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use RuntimeException;

/**
 * 400 Bad Request
 */
class BadRequestException extends RuntimeException implements HTTPExceptionInterface
{
    /**
     * HTTP status code for Bad Request
     *
     * @var int
     */
    protected $code = 400; // @phpstan-ignore-line
}
