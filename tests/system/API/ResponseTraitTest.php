<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\API;

use CodeIgniter\Config\Factories;
use CodeIgniter\Format\FormatterInterface;
use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Format\XMLFormatter;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\Test\Mock\MockResponse;
use Config\App;
use Config\Cookie;
use stdClass;

/**
 * @internal
 *
 * @group Others
 */
final class ResponseTraitTest extends CIUnitTestCase
{
    private ?MockIncomingRequest $request  = null;
    private ?MockResponse $response        = null;
    private ?FormatterInterface $formatter = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatter = new JSONFormatter();
    }

    protected function makeController(array $userConfig = [], string $uri = 'http://example.com', array $userHeaders = [])
    {
        $config = new App();

        foreach ([
            'baseURL'          => 'http://example.com/',
            'uriProtocol'      => 'REQUEST_URI',
            'defaultLocale'    => 'en',
            'negotiateLocale'  => false,
            'supportedLocales' => ['en'],
            'CSPEnabled'       => false,
            'proxyIPs'         => [],
        ] as $key => $value) {
            $config->{$key} = $value;
        }

        $cookie = new Cookie();

        foreach ([
            'prefix'   => '',
            'domain'   => '',
            'path'     => '/',
            'secure'   => false,
            'httponly' => false,
            'samesite' => 'Lax',
        ] as $key => $value) {
            $cookie->{$key} = $value;
        }
        Factories::injectMock('config', 'Cookie', $cookie);

        if ($this->request === null) {
            $this->request  = new MockIncomingRequest($config, new URI($uri), null, new UserAgent());
            $this->response = new MockResponse($config);
        }

        // Insert headers into request.
        $headers = [
            'Accept' => 'text/html',
        ];
        $headers = array_merge($headers, $userHeaders);

        foreach ($headers as $key => $value) {
            $this->request->setHeader($key, $value);
            if (($key === 'Accept') && ! is_array($value)) {
                $this->response->setContentType($value);
            }
        }

        // Create the controller class finally.
        return new class ($this->request, $this->response, $this->formatter) {
            use ResponseTrait;

            protected $request;
            protected $response;
            protected $formatter;

            public function __construct(&$request, &$response, &$formatter)
            {
                $this->request   = $request;
                $this->response  = $response;
                $this->formatter = $formatter;
            }

            public function resetFormatter(): void
            {
                $this->formatter = null;
            }
        };
    }

    public function testNoFormatterJSON(): void
    {
        $this->formatter = null;
        $controller      = $this->makeController([], 'http://codeigniter.com', ['Accept' => 'application/json']);

        $this->invoke($controller, 'respondCreated', [['id' => 3], 'A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(201, $this->response->getStatusCode());

        $expected = <<<'EOH'
            {
                "id": 3
            }
            EOH;
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testNoFormatter(): void
    {
        $this->formatter = null;
        $controller      = $this->makeController([], 'http://codeigniter.com', ['Accept' => 'application/json']);

        $this->invoke($controller, 'respondCreated', ['A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getBody());
    }

    public function testAssociativeArrayPayload(): void
    {
        $this->formatter = null;
        $controller      = $this->makeController();
        $payload         = ['answer' => 42];

        $this->invoke($controller, 'respond', [$payload]);

        $expected = <<<'EOH'
            {
                "answer": 42
            }
            EOH;
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testArrayPayload(): void
    {
        $this->formatter = null;
        $controller      = $this->makeController();
        $payload         = [
            1,
            2,
            3,
        ];

        $this->invoke($controller, 'respond', [$payload]);

        $expected = <<<'EOH'
            [
                1,
                2,
                3
            ]
            EOH;
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testPHPtoArrayPayload(): void
    {
        $this->formatter = null;
        $controller      = $this->makeController();
        $payload         = new stdClass();
        $payload->name   = 'Tom';
        $payload->id     = 1;

        $this->invoke($controller, 'respond', [(array) $payload]);

        $expected = <<<'EOH'
            {
                "name": "Tom",
                "id": 1
            }
            EOH;
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testRespondSets404WithNoData(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respond', [null, null]);

        $this->assertSame(404, $this->response->getStatusCode());
        $this->assertNull($this->response->getBody());
    }

    public function testRespondSetsStatusWithEmptyData(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respond', [null, 201]);

        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertNull($this->response->getBody());
    }

    public function testRespondSetsCorrectBodyAndStatus(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respond', ['something', 201]);

        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame('something', $this->response->getBody());
        $this->assertStringStartsWith('text/html', $this->response->getHeaderLine('Content-Type'));
        $this->assertSame('Created', $this->response->getReason());
    }

    public function testRespondWithCustomReason(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respond', ['something', 201, 'A Custom Reason']);

        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame('A Custom Reason', $this->response->getReason());
    }

    public function testFailSingleMessage(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'fail', ['Failure to Launch', 500, 'WHAT!', 'A Custom Reason']);

        // Will use the JSON formatter by default
        $expected = [
            'status'   => 500,
            'error'    => 'WHAT!',
            'messages' => [
                'error' => 'Failure to Launch',
            ],
        ];
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
        $this->assertSame(500, $this->response->getStatusCode());
        $this->assertSame('A Custom Reason', $this->response->getReason());
    }

    public function testCreated(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respondCreated', [['id' => 3], 'A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format(['id' => 3]), $this->response->getBody());
    }

    public function testDeleted(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respondDeleted', [['id' => 3], 'A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(200, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format(['id' => 3]), $this->response->getBody());
    }

    public function testUpdated(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respondUpdated', [['id' => 3], 'A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(200, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format(['id' => 3]), $this->response->getBody());
    }

    public function testUnauthorized(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failUnauthorized', ['Nope', 'FAT CHANCE', 'A Custom Reason']);

        $expected = [
            'status'   => 401,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'error' => 'Nope',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(401, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testForbidden(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failForbidden', ['Nope', 'FAT CHANCE', 'A Custom Reason']);

        $expected = [
            'status'   => 403,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'error' => 'Nope',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(403, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testNoContent(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respondNoContent', ['']);

        $this->assertStringStartsWith('application/json', $this->response->getHeaderLine('Content-Type'));
        $this->assertSame('No Content', $this->response->getReason());
        $this->assertSame(204, $this->response->getStatusCode());
    }

    public function testNotFound(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failNotFound', ['Nope', 'FAT CHANCE', 'A Custom Reason']);

        $expected = [
            'status'   => 404,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'error' => 'Nope',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(404, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testValidationError(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failValidationError', ['Nope', 'FAT CHANCE', 'A Custom Reason']);

        $expected = [
            'status'   => 400,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'error' => 'Nope',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(400, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testValidationErrors(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failValidationErrors', [['foo' => 'Nope', 'bar' => 'No way'], 'FAT CHANCE', 'A Custom Reason']);

        $expected = [
            'status'   => 400,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'foo' => 'Nope',
                'bar' => 'No way',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(400, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testResourceExists(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failResourceExists', ['Nope', 'FAT CHANCE', 'A Custom Reason']);

        $expected = [
            'status'   => 409,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'error' => 'Nope',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(409, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testResourceGone(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failResourceGone', ['Nope', 'FAT CHANCE', 'A Custom Reason']);

        $expected = [
            'status'   => 410,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'error' => 'Nope',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(410, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testTooManyRequests(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failTooManyRequests', ['Nope', 'FAT CHANCE', 'A Custom Reason']);

        $expected = [
            'status'   => 429,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'error' => 'Nope',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(429, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testServerError(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failServerError', ['Nope.', 'FAT-CHANCE', 'A custom reason.']);

        $this->assertSame('A custom reason.', $this->response->getReason());
        $this->assertSame(500, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format([
            'status'   => 500,
            'error'    => 'FAT-CHANCE',
            'messages' => [
                'error' => 'Nope.',
            ],
        ]), $this->response->getBody());
    }

    public function testValidContentTypes(): void
    {
        $chars     = '; charset=UTF-8';
        $goodMimes = [
            'text/xml',
            'text/html',
            'application/json',
            'application/xml',
        ];

        foreach ($goodMimes as $goodMime) {
            $this->tryValidContentType($goodMime, $goodMime . $chars);
        }
    }

    private function tryValidContentType($mimeType, $contentType): void
    {
        $original                = $_SERVER;
        $_SERVER['CONTENT_TYPE'] = $mimeType;

        $this->makeController([], 'http://codeigniter.com', ['Accept' => $mimeType]);
        $this->assertSame($mimeType, $this->request->getHeaderLine('Accept'), 'Request header...');
        $this->response->setContentType($contentType);
        $this->assertSame($contentType, $this->response->getHeaderLine('Content-Type'), 'Response header pre-response...');

        $_SERVER = $original;
    }

    public function testValidResponses(): void
    {
        $chars     = '; charset=UTF-8';
        $goodMimes = [
            'text/xml',
            'text/html',
            'application/json',
            'application/xml',
        ];

        foreach ($goodMimes as $goodMime) {
            $this->tryValidContentType($goodMime, $goodMime . $chars);
        }
    }

    public function testXMLFormatter(): void
    {
        $this->formatter = new XMLFormatter();
        $controller      = $this->makeController();

        $this->assertInstanceOf(XMLFormatter::class, $this->formatter);

        $this->invoke($controller, 'respondCreated', [['id' => 3], 'A Custom Reason']);

        $expected = <<<'EOH'
            <?xml version="1.0"?>
            <response><id>3</id></response>

            EOH;
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testFormatByRequestNegotiateIfFormatIsNotJsonOrXML(): void
    {
        $config = new App();

        foreach ([
            'baseURL'          => 'http://example.com/',
            'uriProtocol'      => 'REQUEST_URI',
            'defaultLocale'    => 'en',
            'negotiateLocale'  => false,
            'supportedLocales' => ['en'],
            'CSPEnabled'       => false,
            'proxyIPs'         => [],
        ] as $key => $value) {
            $config->{$key} = $value;
        }

        $cookie = new Cookie();

        foreach ([
            'prefix'   => '',
            'domain'   => '',
            'path'     => '/',
            'secure'   => false,
            'httponly' => false,
            'samesite' => 'Lax',
        ] as $key => $value) {
            $cookie->{$key} = $value;
        }
        Factories::injectMock('config', 'Cookie', $cookie);

        $request  = new MockIncomingRequest($config, new URI($config->baseURL), null, new UserAgent());
        $response = new MockResponse($config);

        $controller = new class ($request, $response) {
            use ResponseTrait;

            protected $request;
            protected $response;

            public function __construct(&$request, &$response)
            {
                $this->request  = $request;
                $this->response = $response;

                $this->format = 'txt';
            }
        };

        $this->invoke($controller, 'respondCreated', [['id' => 3], 'A Custom Reason']);

        $this->assertStringStartsWith(config('Format')->supportedResponseFormats[0], $response->getHeaderLine('Content-Type'));
    }

    public function testResponseFormat(): void
    {
        $data       = ['foo' => 'something'];
        $controller = $this->makeController();

        $this->invoke($controller, 'setResponseFormat', ['json']);
        $this->invoke($controller, 'respond', [$data, 201]);

        $this->assertStringStartsWith('application/json', $this->response->getHeaderLine('Content-Type'));
        $this->assertSame($this->formatter->format($data), $this->response->getJSON());

        $this->invoke($controller, 'setResponseFormat', ['xml']);
        $this->invoke($controller, 'respond', [$data, 201]);

        $this->assertStringStartsWith('application/xml', $this->response->getHeaderLine('Content-Type'));
    }

    public function testXMLResponseFormat(): void
    {
        $data       = ['foo' => 'bar'];
        $controller = $this->makeController();
        $controller->resetFormatter();

        $this->invoke($controller, 'setResponseFormat', ['xml']);
        $this->invoke($controller, 'respond', [$data, 201]);

        $xmlFormatter = new XMLFormatter();
        $this->assertSame($xmlFormatter->format($data), $this->response->getXML());
    }

    private function invoke(object $controller, string $method, array $args = [])
    {
        $method = $this->getPrivateMethodInvoker($controller, $method);

        return $method(...$args);
    }
}
