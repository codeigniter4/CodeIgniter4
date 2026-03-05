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

use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * @internal
 */
final class FormRequestException extends RuntimeException implements ResponsableInterface
{
    public function __construct(private readonly ResponseInterface $response)
    {
        parent::__construct('FormRequest authorization or validation failed.');
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
