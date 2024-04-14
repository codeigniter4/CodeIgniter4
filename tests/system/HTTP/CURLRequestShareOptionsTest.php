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

/**
 * This test case is for the case where shareOptions is true.
 * The shareOptions should be set to false.
 *
 * @internal
 *
 * @group Others
 */
final class CURLRequestShareOptionsTest extends CURLRequestTest
{
    protected function getRequest(array $options = []): MockCURLRequest
    {
        $uri = isset($options['base_uri']) ? new URI($options['base_uri']) : new URI();
        $app = new App();

        $config               = new ConfigCURLRequest();
        $config->shareOptions = true;
        Factories::injectMock('config', 'CURLRequest', $config);

        return new MockCURLRequest(($app), $uri, new Response($app), $options);
    }

    public function testHeaderContentLengthNotSharedBetweenRequests(): void
    {
        $options = [
            'base_uri' => 'http://www.foo.com/api/v1/',
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
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);
        $request->setBody('name=George');
        $request->setOutput('Hi there');

        $request->post('answer');
        $request->post('answer');

        // The body is not reset!
        $this->assertArrayHasKey(CURLOPT_POSTFIELDS, $request->curl_options);
    }
}
