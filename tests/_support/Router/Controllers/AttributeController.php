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
use CodeIgniter\Router\Attributes\Cache;
use CodeIgniter\Router\Attributes\Filter;
use CodeIgniter\Router\Attributes\Restrict;

class AttributeController extends Controller
{
    /**
     * Test method with Cache attribute
     */
    #[Cache(for: 60)]
    public function cached(): ResponseInterface
    {
        return $this->response->setBody('Cached content at ' . time());
    }

    /**
     * Test method with Filter attribute
     */
    #[Filter(by: 'testAttributeFilter')]
    public function filtered(): ResponseInterface
    {
        $body = $this->request->getBody();

        return $this->response->setBody('Filtered: ' . $body);
    }

    /**
     * Test method with Filter attribute with parameters
     */
    #[Filter(by: 'testAttributeFilter', having: ['arg1', 'arg2'])]
    public function filteredWithParams(): ResponseInterface
    {
        $body = $this->request->getBody();

        return $this->response->setBody('Filtered: ' . $body);
    }

    /**
     * Test method with Restrict attribute (environment)
     */
    #[Restrict(environment: ENVIRONMENT)]
    public function restricted(): ResponseInterface
    {
        return $this->response->setBody('Access granted');
    }

    /**
     * Test method with multiple attributes
     */
    #[Filter(by: 'testAttributeFilter')]
    #[Restrict(environment: ENVIRONMENT)]
    public function multipleAttributes(): ResponseInterface
    {
        $body = $this->request->getBody();

        return $this->response->setBody('Multiple: ' . $body);
    }

    /**
     * Test method that should be restricted
     */
    #[Restrict(environment: 'production')]
    public function shouldBeRestricted(): ResponseInterface
    {
        return $this->response->setBody('Should not see this');
    }

    /**
     * Test method with custom cache key
     */
    #[Cache(for: 60, key: 'custom_cache_key')]
    public function customCacheKey(): ResponseInterface
    {
        return $this->response->setBody('Custom key content at ' . time());
    }

    /**
     * Simple method with no attributes
     */
    public function noAttributes(): ResponseInterface
    {
        return $this->response->setBody('No attributes');
    }
}
