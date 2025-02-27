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

namespace CodeIgniter\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class SecureHeadersTest extends CIUnitTestCase
{
    public function testAfter(): void
    {
        $filter   = new SecureHeaders();
        $request  = service('request', null, false);
        $response = service('response', null, false);

        $filter->after($request, $response);

        $responseHeaders = $response->headers();
        $headers         = $this->getPrivateProperty($filter, 'headers');

        foreach ($headers as $header => $value) {
            $this->assertSame($value, $responseHeaders[$header]->getValue());
        }
    }
}
