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

namespace Tests\Support\Router\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\Attributes\Filter;

class InvalidAttributeController extends Controller
{
    #[Filter(by: ['auth', 'csrf'])]
    public function invalidMultipleFilters(): ResponseInterface
    {
        return $this->response->setBody('Invalid attributes');
    }
}
