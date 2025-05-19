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

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\Mock\MockCURLRequest;
use Config\App;
use Config\CURLRequest as ConfigCURLRequest;
use PHPUnit\Framework\Attributes\Group;

/**
 * This test case is for the case where shareOptions is true.
 * The shareOptions should be set to false.
 *
 * @internal
 */
#[Group('Others')]
final class CURLRequestShareOptionsTest extends CURLRequestTest
{
    protected function getRequest(array $options = [], bool $emptyShareConnection = false): MockCURLRequest
    {
        $uri = isset($options['baseURI']) ? new URI($options['baseURI']) : new URI();
        $app = new App();

        $config               = new ConfigCURLRequest();
        $config->shareOptions = true;

        if ($emptyShareConnection) {
            $config->shareConnection = [];
        }

        Factories::injectMock('config', 'CURLRequest', $config);

        return new MockCURLRequest(($app), $uri, new Response($app), $options);
    }

    public function testHeaderContentLengthNotSharedBetweenRequests(): void
    {
        $options = [
            'baseURI' => 'http://www.foo.com/api/v1/',
        ];
        $request = $this->getRequest($options);

        $request->post('example', [
            'form_params' => [
                'q' => 'keyword',
            ],
        ]);
        $request->get('example');

        // The Content-Length header is shared!
        $this->assertSame('9', $request->header('Content-Length')->getValue());
    }

    public function testBodyIsResetOnSecondRequest(): void
    {
        $request = $this->getRequest([
            'baseURI' => 'http://www.foo.com/api/v1/',
            'delay'   => 100,
        ]);
        $request->setBody('name=George');
        $request->setOutput('Hi there');

        $request->post('answer');
        $request->post('answer');

        // The body is not reset!
        $this->assertArrayHasKey(CURLOPT_POSTFIELDS, $request->curl_options);
    }
}
