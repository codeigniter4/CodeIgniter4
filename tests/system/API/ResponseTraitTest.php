<?php

namespace CodeIgniter\API;

use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Format\XMLFormatter;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\Test\Mock\MockResponse;
use Config\App;
use stdClass;

/**
 * @internal
 */
final class ResponseTraitTest extends CIUnitTestCase
{
    protected $request;
    protected $response;

    /**
     * @var Response formatter
     */
    protected $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatter = new JSONFormatter();
    }

    protected function makeController(array $userConfig = [], string $uri = 'http://example.com', array $userHeaders = [])
    {
        $config = new App();

        foreach ([
            'baseURL' => 'http://example.com/',
            'uriProtocol' => 'REQUEST_URI',
            'defaultLocale' => 'en',
            'negotiateLocale' => false,
            'supportedLocales' => ['en'],
            'CSPEnabled' => false,
            'cookiePrefix' => '',
            'cookieDomain' => '',
            'cookiePath' => '/',
            'cookieSecure' => false,
            'cookieHTTPOnly' => false,
            'proxyIPs' => [],
            'cookieSameSite' => 'Lax',
        ] as $key => $value) {
            $config->{$key} = $value;
        }

        if ($this->request === null) {
            $this->request  = new MockIncomingRequest((object) $config, new URI($uri), null, new UserAgent());
            $this->response = new MockResponse((object) $config);
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
        $controller = new class($this->request, $this->response, $this->formatter) {
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

            public function resetFormatter()
            {
                $this->formatter = null;
            }
        };

        return $controller;
    }

    public function testNoFormatterJSON()
    {
        $this->formatter = null;
        $controller      = $this->makeController([], 'http://codeigniter.com', ['Accept' => 'application/json']);
        $controller->respondCreated(['id' => 3], 'A Custom Reason');

        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(201, $this->response->getStatusCode());

        $expected = <<<'EOH'
            {
                "id": 3
            }
            EOH;
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testNoFormatter()
    {
        $this->formatter = null;
        $controller      = $this->makeController([], 'http://codeigniter.com', ['Accept' => 'application/json']);
        $controller->respondCreated('A Custom Reason');

        $this->assertSame('A Custom Reason', $this->response->getBody());
    }

    public function testAssociativeArrayPayload()
    {
        $this->formatter = null;
        $controller      = $this->makeController();
        $payload         = ['answer' => 42];
        $expected        = <<<'EOH'
            {
                "answer": 42
            }
            EOH;
        $controller->respond($payload);
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testArrayPayload()
    {
        $this->formatter = null;
        $controller      = $this->makeController();
        $payload         = [
            1,
            2,
            3,
        ];
        $expected = <<<'EOH'
            [
                1,
                2,
                3
            ]
            EOH;
        $controller->respond($payload);
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testPHPtoArrayPayload()
    {
        $this->formatter = null;
        $controller      = $this->makeController();
        $payload         = new stdClass();
        $payload->name   = 'Tom';
        $payload->id     = 1;
        $expected        = <<<'EOH'
            {
                "name": "Tom",
                "id": 1
            }
            EOH;
        $controller->respond((array) $payload);
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testRespondSets404WithNoData()
    {
        $controller = $this->makeController();
        $controller->respond(null, null);

        $this->assertSame(404, $this->response->getStatusCode());
        $this->assertNull($this->response->getBody());
    }

    public function testRespondSetsStatusWithEmptyData()
    {
        $controller = $this->makeController();
        $controller->respond(null, 201);

        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertNull($this->response->getBody());
    }

    public function testRespondSetsCorrectBodyAndStatus()
    {
        $controller = $this->makeController();
        $controller->respond('something', 201);

        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame('something', $this->response->getBody());
        $this->assertStringStartsWith('text/html', $this->response->getHeaderLine('Content-Type'));
        $this->assertSame('Created', $this->response->getReason());
    }

    public function testRespondWithCustomReason()
    {
        $controller = $this->makeController();
        $controller->respond('something', 201, 'A Custom Reason');

        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame('A Custom Reason', $this->response->getReason());
    }

    public function testFailSingleMessage()
    {
        $controller = $this->makeController();

        $controller->fail('Failure to Launch', 500, 'WHAT!', 'A Custom Reason');

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

    public function testCreated()
    {
        $controller = $this->makeController();
        $controller->respondCreated(['id' => 3], 'A Custom Reason');

        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format(['id' => 3]), $this->response->getBody());
    }

    public function testDeleted()
    {
        $controller = $this->makeController();
        $controller->respondDeleted(['id' => 3], 'A Custom Reason');

        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(200, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format(['id' => 3]), $this->response->getBody());
    }

    public function testUpdated()
    {
        $controller = $this->makeController();
        $controller->respondUpdated(['id' => 3], 'A Custom Reason');

        $this->assertSame('A Custom Reason', $this->response->getReason());
        $this->assertSame(200, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format(['id' => 3]), $this->response->getBody());
    }

    public function testUnauthorized()
    {
        $controller = $this->makeController();
        $controller->failUnauthorized('Nope', 'FAT CHANCE', 'A Custom Reason');

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

    public function testForbidden()
    {
        $controller = $this->makeController();
        $controller->failForbidden('Nope', 'FAT CHANCE', 'A Custom Reason');

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

    public function testNoContent()
    {
        $controller = $this->makeController();
        $controller->respondNoContent('');

        $this->assertSame('No Content', $this->response->getReason());
        $this->assertSame(204, $this->response->getStatusCode());
    }

    public function testNotFound()
    {
        $controller = $this->makeController();
        $controller->failNotFound('Nope', 'FAT CHANCE', 'A Custom Reason');

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

    public function testValidationError()
    {
        $controller = $this->makeController();
        $controller->failValidationError('Nope', 'FAT CHANCE', 'A Custom Reason');

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

    public function testValidationErrors()
    {
        $controller = $this->makeController();
        $controller->failValidationErrors(['foo' => 'Nope', 'bar' => 'No way'], 'FAT CHANCE', 'A Custom Reason');

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

    public function testResourceExists()
    {
        $controller = $this->makeController();
        $controller->failResourceExists('Nope', 'FAT CHANCE', 'A Custom Reason');

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

    public function testResourceGone()
    {
        $controller = $this->makeController();
        $controller->failResourceGone('Nope', 'FAT CHANCE', 'A Custom Reason');

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

    public function testTooManyRequests()
    {
        $controller = $this->makeController();
        $controller->failTooManyRequests('Nope', 'FAT CHANCE', 'A Custom Reason');

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

    public function testServerError()
    {
        $controller = $this->makeController();
        $controller->failServerError('Nope.', 'FAT-CHANCE', 'A custom reason.');

        $this::assertEquals('A custom reason.', $this->response->getReason());
        $this::assertEquals(500, $this->response->getStatusCode());
        $this::assertEquals($this->formatter->format([
            'status'   => 500,
            'error'    => 'FAT-CHANCE',
            'messages' => [
                'error' => 'Nope.',
            ],
        ]), $this->response->getBody());
    }

    public function testValidContentTypes()
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

    private function tryValidContentType($mimeType, $contentType)
    {
        $original                = $_SERVER;
        $_SERVER['CONTENT_TYPE'] = $mimeType;

        $this->makeController([], 'http://codeigniter.com', ['Accept' => $mimeType]);
        $this->assertSame($mimeType, $this->request->getHeaderLine('Accept'), 'Request header...');
        $this->response->setContentType($contentType);
        $this->assertSame($contentType, $this->response->getHeaderLine('Content-Type'), 'Response header pre-response...');

        $_SERVER = $original;
    }

    public function testValidResponses()
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

    public function testXMLFormatter()
    {
        $this->formatter = new XMLFormatter();
        $controller      = $this->makeController();

        $this->assertSame('CodeIgniter\Format\XMLFormatter', get_class($this->formatter));

        $controller->respondCreated(['id' => 3], 'A Custom Reason');
        $expected = <<<'EOH'
            <?xml version="1.0"?>
            <response><id>3</id></response>

            EOH;
        $this->assertSame($expected, $this->response->getBody());
    }

    public function testFormatByRequestNegotiateIfFormatIsNotJsonOrXML()
    {
        $config = new App();

        foreach ([
            'baseURL' => 'http://example.com/',
            'uriProtocol' => 'REQUEST_URI',
            'defaultLocale' => 'en',
            'negotiateLocale' => false,
            'supportedLocales' => ['en'],
            'CSPEnabled' => false,
            'cookiePrefix' => '',
            'cookieDomain' => '',
            'cookiePath' => '/',
            'cookieSecure' => false,
            'cookieHTTPOnly' => false,
            'proxyIPs' => [],
            'cookieSameSite' => 'Lax',
        ] as $key => $value) {
            $config->{$key} = $value;
        }

        $request  = new MockIncomingRequest($config, new URI($config->baseURL), null, new UserAgent());
        $response = new MockResponse($config);

        $controller = new class($request, $response) {
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

        $controller->respondCreated(['id' => 3], 'A Custom Reason');
        $this->assertStringStartsWith(config('Format')->supportedResponseFormats[0], $response->getHeaderLine('Content-Type'));
    }

    public function testResponseFormat()
    {
        $data = ['foo' => 'something'];

        $controller = $this->makeController();
        $controller->setResponseFormat('json');
        $controller->respond($data, 201);

        $this->assertStringStartsWith('application/json', $this->response->getHeaderLine('Content-Type'));
        $this->assertSame($this->formatter->format($data), $this->response->getJSON());

        $controller->setResponseFormat('xml');
        $controller->respond($data, 201);

        $this->assertStringStartsWith('application/xml', $this->response->getHeaderLine('Content-Type'));
    }

    public function testXMLResponseFormat()
    {
        $data       = ['foo' => 'bar'];
        $controller = $this->makeController();
        $controller->resetFormatter();
        $controller->setResponseFormat('xml');
        $controller->respond($data, 201);

        $xmlFormatter = new XMLFormatter();
        $this->assertSame($xmlFormatter->format($data), $this->response->getXML());
    }
}
