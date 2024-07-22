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
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCURLRequest;
use Config\App;
use Config\CURLRequest as ConfigCURLRequest;
use CURLFile;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 *
 * @no-final
 */
#[Group('Others')]
class CURLRequestTest extends CIUnitTestCase
{
    protected MockCURLRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();
        $this->request = $this->getRequest();
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function getRequest(array $options = []): MockCURLRequest
    {
        $uri = isset($options['base_uri']) ? new URI($options['base_uri']) : new URI();
        $app = new App();

        $config               = new ConfigCURLRequest();
        $config->shareOptions = false;
        Factories::injectMock('config', 'CURLRequest', $config);

        return new MockCURLRequest(($app), $uri, new Response($app), $options);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4707
     */
    public function testPrepareURLIgnoresAppConfig(): void
    {
        config('App')->baseURL = 'http://example.com/fruit/';

        $request = $this->getRequest(['base_uri' => 'http://example.com/v1/']);

        $method = $this->getPrivateMethodInvoker($request, 'prepareURL');

        $this->assertSame('http://example.com/v1/bananas', $method('bananas'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1029
     */
    public function testGetRemembersBaseURI(): void
    {
        $request = $this->getRequest(['base_uri' => 'http://www.foo.com/api/v1/']);

        $request->get('products');

        $options = $request->curl_options;

        $this->assertSame('http://www.foo.com/api/v1/products', $options[CURLOPT_URL]);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1029
     */
    public function testGetRemembersBaseURIWithHelperMethod(): void
    {
        $request = Services::curlrequest(['base_uri' => 'http://www.foo.com/api/v1/']);

        $uri = $this->getPrivateProperty($request, 'baseURI');
        $this->assertSame('www.foo.com', $uri->getHost());
        $this->assertSame('/api/v1/', $uri->getPath());
    }

    public function testSendReturnsResponse(): void
    {
        $output = 'Howdy Stranger.';

        $response = $this->request->setOutput($output)->send('get', 'http://example.com');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($output, $response->getBody());
    }

    public function testGetSetsCorrectMethod(): void
    {
        $this->request->get('http://example.com');

        $this->assertSame('GET', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('GET', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testDeleteSetsCorrectMethod(): void
    {
        $this->request->delete('http://example.com');

        $this->assertSame('DELETE', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('DELETE', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testHeadSetsCorrectMethod(): void
    {
        $this->request->head('http://example.com');

        $this->assertSame('HEAD', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('HEAD', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testOptionsSetsCorrectMethod(): void
    {
        $this->request->options('http://example.com');

        $this->assertSame('OPTIONS', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('OPTIONS', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testOptionsBaseURIOption(): void
    {
        $options = ['base_uri' => 'http://www.foo.com/api/v1/'];
        $request = $this->getRequest($options);

        $this->assertSame('http://www.foo.com/api/v1/', $request->getBaseURI()->__toString());
    }

    public function testOptionsBaseURIOverride(): void
    {
        $options = [
            'base_uri' => 'http://www.foo.com/api/v1/',
            'baseURI'  => 'http://bogus/com',
        ];
        $request = $this->getRequest($options);

        $this->assertSame('http://bogus/com', $request->getBaseURI()->__toString());
    }

    public function testOptionsHeaders(): void
    {
        $options = [
            'base_uri' => 'http://www.foo.com/api/v1/',
            'headers'  => ['fruit' => 'apple'],
        ];
        $request = $this->getRequest();
        $this->assertNull($request->header('fruit'));

        $request = $this->getRequest($options);
        $this->assertSame('apple', $request->header('fruit')->getValue());
    }

    #[BackupGlobals(true)]
    public function testOptionsHeadersNotUsingPopulate(): void
    {
        $_SERVER['HTTP_HOST']            = 'site1.com';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, br';

        $options = [
            'base_uri' => 'http://www.foo.com/api/v1/',
            'headers'  => [
                'Host'            => 'www.foo.com',
                'Accept-Encoding' => '',
            ],
        ];
        $request = $this->getRequest($options);
        $request->get('example');
        // if headers for the request are defined we use them
        $this->assertNull($request->header('Accept-Language'));
        $this->assertSame('www.foo.com', $request->header('Host')->getValue());
        $this->assertSame('', $request->header('Accept-Encoding')->getValue());
    }

    public function testDefaultOptionsAreSharedBetweenRequests(): void
    {
        $options = [
            'form_params' => ['studio' => 1],
            'user_agent'  => 'CodeIgniter Framework v4',
        ];
        $request = $this->getRequest($options);

        $request->request('POST', 'https://realestate1.example.com');

        $this->assertSame('https://realestate1.example.com', $request->curl_options[CURLOPT_URL]);
        $this->assertSame('studio=1', $request->curl_options[CURLOPT_POSTFIELDS]);
        $this->assertSame('CodeIgniter Framework v4', $request->curl_options[CURLOPT_USERAGENT]);

        $request->request('POST', 'https://realestate2.example.com');

        $this->assertSame('https://realestate2.example.com', $request->curl_options[CURLOPT_URL]);
        $this->assertSame('studio=1', $request->curl_options[CURLOPT_POSTFIELDS]);
        $this->assertSame('CodeIgniter Framework v4', $request->curl_options[CURLOPT_USERAGENT]);
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

        $this->assertNull($request->header('Content-Length'));
    }

    #[BackupGlobals(true)]
    public function testHeaderContentLengthNotSharedBetweenClients(): void
    {
        $_SERVER['HTTP_CONTENT_LENGTH'] = '10';

        $options = [
            'base_uri' => 'http://www.foo.com/api/v1/',
        ];
        $request = $this->getRequest($options);
        $request->post('example', [
            'form_params' => [
                'q' => 'keyword',
            ],
        ]);

        $request = $this->getRequest($options);
        $request->get('example');

        $this->assertNull($request->header('Content-Length'));
    }

    public function testOptionsDelay(): void
    {
        $request = $this->getRequest();
        $this->assertEqualsWithDelta(0.0, $request->getDelay(), PHP_FLOAT_EPSILON);

        $options = [
            'delay'   => 2000,
            'headers' => ['fruit' => 'apple'],
        ];
        $request = $this->getRequest($options);
        $this->assertEqualsWithDelta(2.0, $request->getDelay(), PHP_FLOAT_EPSILON);
    }

    public function testPatchSetsCorrectMethod(): void
    {
        $this->request->patch('http://example.com');

        $this->assertSame('PATCH', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('PATCH', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testPostSetsCorrectMethod(): void
    {
        $this->request->post('http://example.com');

        $this->assertSame('POST', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('POST', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testPutSetsCorrectMethod(): void
    {
        $this->request->put('http://example.com');

        $this->assertSame('PUT', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('PUT', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testCustomMethodSetsCorrectMethod(): void
    {
        $this->request->request('custom', 'http://example.com');

        $this->assertSame('custom', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('custom', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testRequestMethodGetsSanitized(): void
    {
        $this->request->request('<script>Custom</script>', 'http://example.com');

        $this->assertSame('Custom', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
        $this->assertSame('Custom', $options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testRequestSetsBasicCurlOptions(): void
    {
        $this->request->request('get', 'http://example.com');

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_URL, $options);
        $this->assertSame('http://example.com', $options[CURLOPT_URL]);

        $this->assertArrayHasKey(CURLOPT_RETURNTRANSFER, $options);
        $this->assertTrue($options[CURLOPT_RETURNTRANSFER]);

        $this->assertArrayHasKey(CURLOPT_HEADER, $options);
        $this->assertTrue($options[CURLOPT_HEADER]);

        $this->assertArrayHasKey(CURLOPT_FRESH_CONNECT, $options);
        $this->assertTrue($options[CURLOPT_FRESH_CONNECT]);

        $this->assertArrayHasKey(CURLOPT_TIMEOUT_MS, $options);
        $this->assertEqualsWithDelta(0.0, $options[CURLOPT_TIMEOUT_MS], PHP_FLOAT_EPSILON);

        $this->assertArrayHasKey(CURLOPT_CONNECTTIMEOUT_MS, $options);
        $this->assertEqualsWithDelta(150000.0, $options[CURLOPT_CONNECTTIMEOUT_MS], PHP_FLOAT_EPSILON);
    }

    public function testAuthBasicOption(): void
    {
        $this->request->request('get', 'http://example.com', [
            'auth' => [
                'username',
                'password',
            ],
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_USERPWD, $options);
        $this->assertSame('username:password', $options[CURLOPT_USERPWD]);

        $this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
        $this->assertSame(CURLAUTH_BASIC, $options[CURLOPT_HTTPAUTH]);
    }

    public function testAuthBasicOptionExplicit(): void
    {
        $this->request->request('get', 'http://example.com', [
            'auth' => [
                'username',
                'password',
                'basic',
            ],
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_USERPWD, $options);
        $this->assertSame('username:password', $options[CURLOPT_USERPWD]);

        $this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
        $this->assertSame(CURLAUTH_BASIC, $options[CURLOPT_HTTPAUTH]);
    }

    public function testAuthDigestOption(): void
    {
        $output = "HTTP/1.1 401 Unauthorized
Server: ddos-guard
Set-Cookie: __ddg1=z177j4mLtqzC07v0zviU; Domain=.site.ru; HttpOnly; Path=/; Expires=Wed, 07-Jul-2021 15:13:14 GMT
WWW-Authenticate: Digest\x0d\x0a\x0d\x0aHTTP/1.1 200 OK
Server: ddos-guard
Connection: keep-alive
Keep-Alive: timeout=60
Set-Cookie: __ddg1=z177j4mLtqzC07v0zviU; Domain=.site.ru; HttpOnly; Path=/; Expires=Wed, 07-Jul-2021 15:13:14 GMT
Date: Tue, 07 Jul 2020 15:13:14 GMT
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Set-Cookie: PHPSESSID=80pd3hlg38mvjnelpvokp9lad0; path=/
Content-Type: application/xml; charset=utf-8
Transfer-Encoding: chunked\x0d\x0a\x0d\x0a<title>Update success! config</title>";

        $this->request->setOutput($output);

        $response = $this->request->request('get', 'http://example.com', [
            'auth' => [
                'username',
                'password',
                'digest',
            ],
        ]);

        $options = $this->request->curl_options;

        $this->assertSame('<title>Update success! config</title>', $response->getBody());
        $this->assertSame(200, $response->getStatusCode());

        $this->assertArrayHasKey(CURLOPT_USERPWD, $options);
        $this->assertSame('username:password', $options[CURLOPT_USERPWD]);

        $this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
        $this->assertSame(CURLAUTH_DIGEST, $options[CURLOPT_HTTPAUTH]);
    }

    public function testSetAuthBasic(): void
    {
        $this->request->setAuth('username', 'password')->get('http://example.com');

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_USERPWD, $options);
        $this->assertSame('username:password', $options[CURLOPT_USERPWD]);

        $this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
        $this->assertSame(CURLAUTH_BASIC, $options[CURLOPT_HTTPAUTH]);
    }

    public function testSetAuthDigest(): void
    {
        $output = "HTTP/1.1 401 Unauthorized
Server: ddos-guard
Set-Cookie: __ddg1=z177j4mLtqzC07v0zviU; Domain=.site.ru; HttpOnly; Path=/; Expires=Wed, 07-Jul-2021 15:13:14 GMT
WWW-Authenticate: Digest\x0d\x0a\x0d\x0aHTTP/1.1 200 OK
Server: ddos-guard
Connection: keep-alive
Keep-Alive: timeout=60
Set-Cookie: __ddg1=z177j4mLtqzC07v0zviU; Domain=.site.ru; HttpOnly; Path=/; Expires=Wed, 07-Jul-2021 15:13:14 GMT
Date: Tue, 07 Jul 2020 15:13:14 GMT
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Set-Cookie: PHPSESSID=80pd3hlg38mvjnelpvokp9lad0; path=/
Content-Type: application/xml; charset=utf-8
Transfer-Encoding: chunked\x0d\x0a\x0d\x0a<title>Update success! config</title>";

        $this->request->setOutput($output);

        $response = $this->request->setAuth('username', 'password', 'digest')->get('http://example.com');

        $options = $this->request->curl_options;

        $this->assertSame('<title>Update success! config</title>', $response->getBody());
        $this->assertSame(200, $response->getStatusCode());

        $this->assertArrayHasKey(CURLOPT_USERPWD, $options);
        $this->assertSame('username:password', $options[CURLOPT_USERPWD]);

        $this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
        $this->assertSame(CURLAUTH_DIGEST, $options[CURLOPT_HTTPAUTH]);
    }

    public function testCertOption(): void
    {
        $file = __FILE__;

        $this->request->request('get', 'http://example.com', [
            'cert' => $file,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_SSLCERT, $options);
        $this->assertSame($file, $options[CURLOPT_SSLCERT]);
    }

    public function testCertOptionWithPassword(): void
    {
        $file = __FILE__;

        $this->request->request('get', 'http://example.com', [
            'cert' => [
                $file,
                'password',
            ],
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_SSLCERT, $options);
        $this->assertSame($file, $options[CURLOPT_SSLCERT]);

        $this->assertArrayHasKey(CURLOPT_SSLCERTPASSWD, $options);
        $this->assertSame('password', $options[CURLOPT_SSLCERTPASSWD]);
    }

    public function testMissingCertOption(): void
    {
        $file = 'something_obviously_bogus';
        $this->expectException(HTTPException::class);

        $this->request->request('get', 'http://example.com', [
            'cert' => $file,
        ]);
    }

    public function testSSLVerification(): void
    {
        $file = __FILE__;

        $this->request->request('get', 'http://example.com', [
            'verify' => $file,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_CAINFO, $options);
        $this->assertSame($file, $options[CURLOPT_CAINFO]);

        $this->assertArrayHasKey(CURLOPT_SSL_VERIFYPEER, $options);
        $this->assertTrue($options[CURLOPT_SSL_VERIFYPEER]);

        $this->assertArrayHasKey(CURLOPT_SSL_VERIFYHOST, $options);
        $this->assertSame(2, $options[CURLOPT_SSL_VERIFYHOST]);
    }

    public function testNoSSL(): void
    {
        $this->request->request('get', 'http://example.com', [
            'verify' => false,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_SSL_VERIFYPEER, $options);
        $this->assertFalse($options[CURLOPT_SSL_VERIFYPEER]);

        $this->assertArrayHasKey(CURLOPT_SSL_VERIFYHOST, $options);
        $this->assertSame(0, $options[CURLOPT_SSL_VERIFYHOST]);
    }

    public function testSSLWithBadKey(): void
    {
        $file = 'something_obviously_bogus';
        $this->expectException(HTTPException::class);

        $this->request->request('get', 'http://example.com', [
            'verify' => $file,
        ]);
    }

    public function testProxyuOption(): void
    {
        $this->request->request('get', 'http://example.com', [
            'proxy' => 'http://localhost:3128',
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_PROXY, $options);
        $this->assertSame('http://localhost:3128', $options[CURLOPT_PROXY]);
        $this->assertArrayHasKey(CURLOPT_HTTPPROXYTUNNEL, $options);
        $this->assertTrue($options[CURLOPT_HTTPPROXYTUNNEL]);
    }

    public function testDebugOptionTrue(): void
    {
        $this->request->request('get', 'http://example.com', [
            'debug' => true,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_VERBOSE, $options);
        $this->assertSame(1, $options[CURLOPT_VERBOSE]);

        $this->assertArrayHasKey(CURLOPT_STDERR, $options);
        $this->assertIsResource($options[CURLOPT_STDERR]);
    }

    public function testDebugOptionFalse(): void
    {
        $this->request->request('get', 'http://example.com', [
            'debug' => false,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayNotHasKey(CURLOPT_VERBOSE, $options);
        $this->assertArrayNotHasKey(CURLOPT_STDERR, $options);
    }

    public function testDebugOptionFile(): void
    {
        $file = SUPPORTPATH . 'Files/baker/banana.php';

        $this->request->request('get', 'http://example.com', [
            'debug' => $file,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_VERBOSE, $options);
        $this->assertSame(1, $options[CURLOPT_VERBOSE]);

        $this->assertArrayHasKey(CURLOPT_STDERR, $options);
        $this->assertIsResource($options[CURLOPT_STDERR]);
    }

    public function testDecodeContent(): void
    {
        $this->request->setHeader('Accept-Encoding', 'cobol');
        $this->request->request('get', 'http://example.com', [
            'decode_content' => true,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_ENCODING, $options);
        $this->assertSame('cobol', $options[CURLOPT_ENCODING]);
    }

    public function testDecodeContentWithoutAccept(): void
    {
        //      $this->request->setHeader('Accept-Encoding', 'cobol');
        $this->request->request('get', 'http://example.com', [
            'decode_content' => true,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_ENCODING, $options);
        $this->assertSame('', $options[CURLOPT_ENCODING]);
        $this->assertArrayHasKey(CURLOPT_HTTPHEADER, $options);
        $this->assertSame('Accept-Encoding', $options[CURLOPT_HTTPHEADER]);
    }

    public function testAllowRedirectsOptionFalse(): void
    {
        $this->request->request('get', 'http://example.com', [
            'allow_redirects' => false,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
        $this->assertSame(0, $options[CURLOPT_FOLLOWLOCATION]);

        $this->assertArrayNotHasKey(CURLOPT_MAXREDIRS, $options);
        $this->assertArrayNotHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
    }

    public function testAllowRedirectsOptionTrue(): void
    {
        $this->request->request('get', 'http://example.com', [
            'allow_redirects' => true,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
        $this->assertSame(1, $options[CURLOPT_FOLLOWLOCATION]);

        $this->assertArrayHasKey(CURLOPT_MAXREDIRS, $options);
        $this->assertSame(5, $options[CURLOPT_MAXREDIRS]);
        $this->assertArrayHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
        $this->assertSame(CURLPROTO_HTTP | CURLPROTO_HTTPS, $options[CURLOPT_REDIR_PROTOCOLS]);
    }

    public function testAllowRedirectsOptionDefaults(): void
    {
        $this->request->request('get', 'http://example.com', [
            'allow_redirects' => true,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
        $this->assertSame(1, $options[CURLOPT_FOLLOWLOCATION]);

        $this->assertArrayHasKey(CURLOPT_MAXREDIRS, $options);
        $this->assertArrayHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
    }

    public function testAllowRedirectsArray(): void
    {
        $this->request->request('get', 'http://example.com', [
            'allow_redirects' => ['max' => 2],
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
        $this->assertSame(1, $options[CURLOPT_FOLLOWLOCATION]);

        $this->assertArrayHasKey(CURLOPT_MAXREDIRS, $options);
        $this->assertSame(2, $options[CURLOPT_MAXREDIRS]);
    }

    public function testSendWithQuery(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'query'    => [
                'name' => 'Henry',
                'd.t'  => 'value',
            ],
        ]);

        $request->get('products');

        $options = $request->curl_options;

        $this->assertSame('http://www.foo.com/api/v1/products?name=Henry&d.t=value', $options[CURLOPT_URL]);
    }

    public function testSendWithDelay(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $request->get('products');

        // we still need to check the code coverage to make sure this was done
        $this->assertEqualsWithDelta(0.1, $request->getDelay(), PHP_FLOAT_EPSILON);
    }

    public function testSendContinued(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $request->setOutput("HTTP/1.1 100 Continue\x0d\x0a\x0d\x0aHi there");
        $response = $request->get('answer');
        $this->assertSame('Hi there', $response->getBody());
    }

    /**
     * See: https://github.com/codeigniter4/CodeIgniter4/issues/3261
     */
    public function testSendContinuedWithManyHeaders(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $output = "HTTP/1.1 100 Continue
Server: ddos-guard
Set-Cookie: __ddg1=z177j4mLtqzC07v0zviU; Domain=.site.ru; HttpOnly; Path=/; Expires=Wed, 07-Jul-2021 15:13:14 GMT\x0d\x0a\x0d\x0aHTTP/1.1 200 OK
Server: ddos-guard
Connection: keep-alive
Keep-Alive: timeout=60
Set-Cookie: __ddg1=z177j4mLtqzC07v0zviU; Domain=.site.ru; HttpOnly; Path=/; Expires=Wed, 07-Jul-2021 15:13:14 GMT
Date: Tue, 07 Jul 2020 15:13:14 GMT
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Set-Cookie: PHPSESSID=80pd3hlg38mvjnelpvokp9lad0; path=/
Content-Type: application/xml; charset=utf-8
Transfer-Encoding: chunked\x0d\x0a\x0d\x0a<title>Update success! config</title>";
        $request->setOutput($output);

        $response = $request->get('answer');

        $this->assertSame('<title>Update success! config</title>', $response->getBody());

        $responseHeaderKeys = [
            'Cache-Control',
            'Server',
            'Connection',
            'Keep-Alive',
            'Set-Cookie',
            'Date',
            'Expires',
            'Pragma',
            'Content-Type',
            'Transfer-Encoding',
        ];
        $this->assertSame($responseHeaderKeys, array_keys($response->headers()));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSendProxied(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $output = "HTTP/1.1 200 Connection established
Proxy-Agent: Fortinet-Proxy/1.0\x0d\x0a\x0d\x0aHTTP/1.1 200 OK\x0d\x0a\x0d\x0aHi there";
        $request->setOutput($output);

        $response = $request->get('answer');
        $this->assertSame('Hi there', $response->getBody());
    }

    /**
     * See: https://github.com/codeigniter4/CodeIgniter4/issues/7394
     */
    public function testResponseHeadersWithMultipleRequests(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
        ]);

        $output = "HTTP/2.0 200 OK
Server: ddos-guard
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Content-Type: application/xml; charset=utf-8
Transfer-Encoding: chunked\x0d\x0a\x0d\x0a<title>Hello1</title>";
        $request->setOutput($output);

        $response = $request->get('answer1');

        $this->assertSame('<title>Hello1</title>', $response->getBody());

        $responseHeaderKeys = [
            'Cache-Control',
            'Server',
            'Expires',
            'Pragma',
            'Content-Type',
            'Transfer-Encoding',
        ];
        $this->assertSame($responseHeaderKeys, array_keys($response->headers()));

        $this->assertSame(200, $response->getStatusCode());

        $output = "HTTP/2.0 200 OK
Expires: Thu, 19 Nov 1982 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Content-Type: application/xml; charset=utf-8
Transfer-Encoding: chunked\x0d\x0a\x0d\x0a<title>Hello2</title>";
        $request->setOutput($output);

        $response = $request->get('answer2');

        $this->assertSame('<title>Hello2</title>', $response->getBody());

        $responseHeaderKeys = [
            'Cache-Control',
            'Expires',
            'Content-Type',
            'Transfer-Encoding',
        ];
        $this->assertSame($responseHeaderKeys, array_keys($response->headers()));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testResponseHeadersWithMultipleSetCookies(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'https://github.com/',
        ]);

        $output = "HTTP/2 200
server: GitHub.com
date: Sat, 11 Nov 2023 02:26:55 GMT
content-type: text/html; charset=utf-8
set-cookie: _gh_sess=PlRlha1YumlLhLuo5MuNbIWJRO9RRuR%2FHfYsWRh5B0mkalFIZstlAbTmSstl8q%2FAC57IsWMVuFHWQc6L4qDHQJrwhuYVO5ZaigPCUjAStnhh%2FieZQVqIf92Al7vusuzx2o8XH%2Fv6nd9qzMTAWc2%2FkRsl8jxPQYGNaWeuUBY2w3%2FDORSikN4c0vHOyedhU7Xcv3Ryz5xD3DNxK9R8xKNZ6OSXLJ6bjX8iIT6LxvroVIf2HjvowW9cQsq0kN08mS6KtTnH0mD3ANWqsVVWeMzFNA%3D%3D--Jx830Q9Nmkfz9OGA--kEcPtNphvjNMopYqFDxUbw%3D%3D; Path=/; HttpOnly; Secure; SameSite=Lax
set-cookie: _octo=GH1.1.599292127.1699669625; Path=/; Domain=github.com; Expires=Mon, 11 Nov 2024 02:27:05 GMT; Secure; SameSite=Lax
set-cookie: logged_in=no; Path=/; Domain=github.com; Expires=Mon, 11 Nov 2024 02:27:05 GMT; HttpOnly; Secure; SameSite=Lax
accept-ranges: bytes\x0d\x0a\x0d\x0a";
        $request->setOutput($output);

        $response = $request->get('/');

        $setCookieHeaders = $response->header('set-cookie');

        $this->assertCount(3, $setCookieHeaders);
        $this->assertSame(
            'logged_in=no; Path=/; Domain=github.com; Expires=Mon, 11 Nov 2024 02:27:05 GMT; HttpOnly; Secure; SameSite=Lax',
            $setCookieHeaders[2]->getValue()
        );

        $this->assertSame(
            '_octo=GH1.1.599292127.1699669625; Path=/; Domain=github.com; Expires=Mon, 11 Nov 2024 02:27:05 GMT; Secure; SameSite=Lax',
            $setCookieHeaders[1]->getValueLine()
        );
    }

    public function testSplitResponse(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $request->setOutput("Accept: text/html\x0d\x0a\x0d\x0aHi there");
        $response = $request->get('answer');
        $this->assertSame('Hi there', $response->getBody());
    }

    public function testApplyBody(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $request->setBody('name=George');
        $request->setOutput('Hi there');
        $response = $request->post('answer');

        $this->assertSame('Hi there', $response->getBody());
        $this->assertSame('name=George', $request->curl_options[CURLOPT_POSTFIELDS]);
    }

    public function testApplyBodyByOptions(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $request->setOutput('Hi there');
        $response = $request->post('answer', [
            'body' => 'name=George',
        ]);

        $this->assertSame('Hi there', $response->getBody());
        $this->assertSame('name=George', $request->curl_options[CURLOPT_POSTFIELDS]);
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

        $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $request->curl_options);
    }

    public function testResponseHeaders(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $request->setOutput("HTTP/2.0 234 Ohoh\x0d\x0aAccept: text/html\x0d\x0a\x0d\x0aHi there");
        $response = $request->get('bogus');

        $this->assertSame('2.0', $response->getProtocolVersion());
        $this->assertSame(234, $response->getStatusCode());
    }

    public function testResponseHeadersShortProtocol(): void
    {
        $request = $this->getRequest([
            'base_uri' => 'http://www.foo.com/api/v1/',
            'delay'    => 100,
        ]);

        $request->setOutput("HTTP/2 235 Ohoh\x0d\x0aAccept: text/html\x0d\x0a\x0d\x0aHi there shortie");
        $response = $request->get('bogus');

        $this->assertSame('2.0', $response->getProtocolVersion());
        $this->assertSame(235, $response->getStatusCode());
    }

    public function testPostFormEncoded(): void
    {
        $params = [
            'foo' => 'bar',
            'baz' => [
                'hi',
                'there',
            ],
        ];
        $this->request->request('POST', '/post', [
            'form_params' => $params,
        ]);

        $this->assertSame('POST', $this->request->getMethod());

        $options = $this->request->curl_options;

        $expected = http_build_query($params);
        $this->assertArrayHasKey(CURLOPT_POSTFIELDS, $options);
        $this->assertSame($expected, $options[CURLOPT_POSTFIELDS]);
    }

    public function testPostFormMultipart(): void
    {
        $params = [
            'foo' => 'bar',
            'baz' => [
                'hi',
                'there',
            ],
            'afile' => new CURLFile(__FILE__),
        ];
        $this->request->request('POST', '/post', [
            'multipart' => $params,
        ]);

        $this->assertSame('POST', $this->request->getMethod());

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_POSTFIELDS, $options);
        $this->assertSame($params, $options[CURLOPT_POSTFIELDS]);
    }

    public function testSetForm(): void
    {
        $params = [
            'foo' => 'bar',
            'baz' => [
                'hi',
                'there',
            ],
        ];

        $this->request->setForm($params)->post('/post');

        $this->assertSame(
            http_build_query($params),
            $this->request->curl_options[CURLOPT_POSTFIELDS]
        );

        $params['afile'] = new CURLFile(__FILE__);

        $this->request->setForm($params, true)->post('/post');

        $this->assertSame(
            $params,
            $this->request->curl_options[CURLOPT_POSTFIELDS]
        );
    }

    public function testJSONData(): void
    {
        $params = [
            'foo' => 'bar',
            'baz' => [
                'hi',
                'there',
            ],
        ];
        $this->request->request('POST', '/post', [
            'json' => $params,
        ]);

        $this->assertSame('POST', $this->request->getMethod());

        $expected = json_encode($params);
        $this->assertSame(
            $expected,
            $this->request->curl_options[CURLOPT_POSTFIELDS]
        );
    }

    public function testSetJSON(): void
    {
        $params = [
            'foo' => 'bar',
            'baz' => [
                'hi',
                'there',
            ],
        ];
        $this->request->setJSON($params)->post('/post');

        $expected = json_encode($params);
        $this->assertSame(
            $expected,
            $this->request->curl_options[CURLOPT_POSTFIELDS]
        );
        $this->assertSame(
            'Content-Type: application/json',
            $this->request->curl_options[CURLOPT_HTTPHEADER][0]
        );
    }

    public function testHTTPv1(): void
    {
        $this->request->request('POST', '/post', [
            'version' => 1.0,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_HTTP_VERSION, $options);
        $this->assertSame(CURL_HTTP_VERSION_1_0, $options[CURLOPT_HTTP_VERSION]);
    }

    public function testHTTPv11(): void
    {
        $this->request->request('POST', '/post', [
            'version' => 1.1,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_HTTP_VERSION, $options);
        $this->assertSame(CURL_HTTP_VERSION_1_1, $options[CURLOPT_HTTP_VERSION]);
    }

    public function testHTTPv2(): void
    {
        $this->request->request('POST', '/post', [
            'version' => 2.0,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_HTTP_VERSION, $options);
        $this->assertSame(CURL_HTTP_VERSION_2_0, $options[CURLOPT_HTTP_VERSION]);
    }

    public function testCookieOption(): void
    {
        $holder = SUPPORTPATH . 'HTTP/Files/CookiesHolder.txt';
        $this->request->request('POST', '/post', [
            'cookie' => $holder,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_COOKIEJAR, $options);
        $this->assertSame($holder, $options[CURLOPT_COOKIEJAR]);
        $this->assertArrayHasKey(CURLOPT_COOKIEFILE, $options);
        $this->assertSame($holder, $options[CURLOPT_COOKIEFILE]);
    }

    public function testUserAgentOption(): void
    {
        $agent = 'CodeIgniter Framework';

        $this->request->request('POST', '/post', [
            'user_agent' => $agent,
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_USERAGENT, $options);
        $this->assertSame($agent, $options[CURLOPT_USERAGENT]);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/8347
     */
    public function testMultipleHTTP100(): void
    {
        $jsonBody = '{"name":"John Doe","age":30}';

        $output = "HTTP/1.1 100 Continue
Mark bundle as not supporting multiuse
HTTP/1.1 100 Continue
Mark bundle as not supporting multiuse
HTTP/1.1 200 OK
Server: Werkzeug/2.2.2 Python/3.7.17
Date: Sun, 28 Jan 2024 06:05:36 GMT
Content-Type: application/json
Content-Length: 33\r\n\r\n" . $jsonBody;

        $this->request->setOutput($output);

        $response = $this->request->request('GET', 'http://example.com');

        $this->assertSame($jsonBody, $response->getBody());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetHeaderLineContentType(): void
    {
        $output = 'HTTP/2 200
date: Thu, 11 Apr 2024 07:26:00 GMT
content-type: text/html; charset=UTF-8
cache-control: no-store, max-age=0, no-cache
server: cloudflare
content-encoding: br
alt-svc: h3=":443"; ma=86400' . "\x0d\x0a\x0d\x0aResponse Body";

        $this->request->setOutput($output);

        $response = $this->request->request('get', 'http://example.com');

        $this->assertSame('text/html; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testHTTPversionAsString(): void
    {
        $this->request->request('POST', '/post', [
            'version' => '1.0',
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_HTTP_VERSION, $options);
        $this->assertSame(CURL_HTTP_VERSION_1_0, $options[CURLOPT_HTTP_VERSION]);

        $this->request->request('POST', '/post', [
            'version' => '1.1',
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_HTTP_VERSION, $options);
        $this->assertSame(CURL_HTTP_VERSION_1_1, $options[CURLOPT_HTTP_VERSION]);

        $this->request->request('POST', '/post', [
            'version' => '2.0',
        ]);

        $options = $this->request->curl_options;

        $this->assertArrayHasKey(CURLOPT_HTTP_VERSION, $options);
        $this->assertSame(CURL_HTTP_VERSION_2_0, $options[CURLOPT_HTTP_VERSION]);
    }
}
