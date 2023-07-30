<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @group Others
 */
final class SecureHeadersTest extends CIUnitTestCase
{
    public function testAfter(): void
    {
        $filter   = new SecureHeaders();
        $request  = Services::request(null, false);
        $response = Services::response(null, false);

        $filter->after($request, $response);

        $responseHeaders = $response->headers();
        $headers         = $this->getPrivateProperty($filter, 'headers');

        foreach ($headers as $header => $value) {
            $this->assertSame($value, $responseHeaders[$header]->getValue());
        }
    }
}
