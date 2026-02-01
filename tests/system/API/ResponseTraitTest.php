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

namespace CodeIgniter\API;

use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Format\FormatterInterface;
use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Format\XMLFormatter;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Model;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\Test\Mock\MockResponse;
use Config\App;
use Config\Cookie;
use Config\Services;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\API\InvalidTransformer;
use Tests\Support\API\TestTransformer;

/**
 * @internal
 */
#[Group('Others')]
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

    private function createAppConfig(): App
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

        return $config;
    }

    private function createCookieConfig(): Cookie
    {
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

        return $cookie;
    }

    /**
     * @param array<string, string> $userHeaders
     *
     * @phpstan-assert RequestInterface  $this->request
     * @phpstan-assert ResponseInterface $this->response
     */
    private function createRequestAndResponse(string $routePath = '', array $userHeaders = []): void
    {
        $config = $this->createAppConfig();
        $this->createCookieConfig();

        if (! $this->request instanceof MockIncomingRequest) {
            $this->request = new MockIncomingRequest(
                $config,
                new SiteURI($config, $routePath),
                null,
                new UserAgent(),
            );
            $this->response = new MockResponse($config);
        }

        $headers = array_merge(['Accept' => 'text/html'], $userHeaders);

        foreach ($headers as $key => $value) {
            $this->request->setHeader($key, $value);
        }
    }

    /**
     * @param array<string, string> $userHeaders
     */
    protected function makeController(string $routePath = '', array $userHeaders = []): object
    {
        $this->createRequestAndResponse($routePath, $userHeaders);

        return new class ($this->request, $this->response, $this->formatter) {
            use ResponseTrait;

            public function __construct(
                protected RequestInterface $request,
                protected ResponseInterface $response,
                ?FormatterInterface $formatter,
            ) {
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
        $controller      = $this->makeController(
            '',
            ['Accept' => 'application/json'],
        );

        $this->invoke($controller, 'respondCreated', [['id' => 3], 'A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
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
        $controller      = $this->makeController(
            '',
            ['Accept' => 'application/json'],
        );

        $this->invoke($controller, 'respondCreated', ['A Custom Reason']);

        $this->assertSame('"A Custom Reason"', $this->response->getBody());
    }

    public function testNoFormatterWithStringAsHtmlTrue(): void
    {
        $this->formatter = null;

        $this->createRequestAndResponse('', ['Accept' => 'application/json']);

        $controller = new class ($this->request, $this->response, $this->formatter) {
            use ResponseTrait;

            protected bool $stringAsHtml = true;

            public function __construct(
                protected RequestInterface $request,
                protected ResponseInterface $response,
                ?FormatterInterface $formatter,
            ) {
                $this->formatter = $formatter;
            }
        };

        $this->invoke($controller, 'respondCreated', ['A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getBody());
        $this->assertStringStartsWith(
            'text/html',
            $this->response->getHeaderLine('Content-Type'),
        );
    }

    public function testAssociativeArrayPayload(): void
    {
        $this->formatter = null;
        $controller      = $this->makeController();

        $payload = ['answer' => 42];
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

        $payload = [
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

        $payload       = new stdClass();
        $payload->name = 'Tom';
        $payload->id   = 1;

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
        $this->assertSame('"something"', $this->response->getBody());
        $this->assertStringStartsWith(
            'application/json',
            $this->response->getHeaderLine('Content-Type'),
        );
        $this->assertSame('Created', $this->response->getReasonPhrase());
    }

    public function testRespondSetsCorrectBodyAndStatusWithStringAsHtmlTrue(): void
    {
        $this->createRequestAndResponse();

        $controller = new class ($this->request, $this->response, $this->formatter) {
            use ResponseTrait;

            protected bool $stringAsHtml = true;

            public function __construct(
                protected RequestInterface $request,
                protected ResponseInterface $response,
                ?FormatterInterface $formatter,
            ) {
                $this->formatter = $formatter;
            }
        };

        $this->invoke($controller, 'respond', ['something', 201]);

        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame('something', $this->response->getBody());
        $this->assertStringStartsWith('text/html', $this->response->getHeaderLine('Content-Type'));
        $this->assertSame('Created', $this->response->getReasonPhrase());
    }

    public function testRespondWithCustomReason(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respond', ['something', 201, 'A Custom Reason']);

        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
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
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
    }

    public function testCreated(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respondCreated', [['id' => 3], 'A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format(['id' => 3]), $this->response->getBody());
    }

    public function testDeleted(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respondDeleted', [['id' => 3], 'A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
        $this->assertSame(200, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format(['id' => 3]), $this->response->getBody());
    }

    public function testUpdated(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respondUpdated', [['id' => 3], 'A Custom Reason']);

        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
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
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
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
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
        $this->assertSame(403, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testNoContent(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'respondNoContent', ['']);

        $this->assertStringStartsWith('application/json', $this->response->getHeaderLine('Content-Type'));
        $this->assertSame('No Content', $this->response->getReasonPhrase());
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
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
        $this->assertSame(404, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testValidationErrors(): void
    {
        $controller = $this->makeController();

        $this->invoke(
            $controller,
            'failValidationErrors',
            [['foo' => 'Nope', 'bar' => 'No way'], 'FAT CHANCE', 'A Custom Reason'],
        );

        $expected = [
            'status'   => 400,
            'error'    => 'FAT CHANCE',
            'messages' => [
                'foo' => 'Nope',
                'bar' => 'No way',
            ],
        ];
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
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
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
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
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
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
        $this->assertSame('A Custom Reason', $this->response->getReasonPhrase());
        $this->assertSame(429, $this->response->getStatusCode());
        $this->assertSame($this->formatter->format($expected), $this->response->getBody());
    }

    public function testServerError(): void
    {
        $controller = $this->makeController();

        $this->invoke($controller, 'failServerError', ['Nope.', 'FAT-CHANCE', 'A custom reason.']);

        $this->assertSame('A custom reason.', $this->response->getReasonPhrase());
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

    private function tryValidContentType(string $mimeType, string $contentType): void
    {
        $originalContentType = Services::superglobals()->server('CONTENT_TYPE') ?? '';
        Services::superglobals()->setServer('CONTENT_TYPE', $mimeType);

        $this->makeController('', ['Accept' => $mimeType]);
        $this->assertSame(
            $mimeType,
            $this->request->getHeaderLine('Accept'),
            'Request header...',
        );

        $this->response->setContentType($contentType);
        $this->assertSame(
            $contentType,
            $this->response->getHeaderLine('Content-Type'),
            'Response header pre-response...',
        );

        Services::superglobals()->setServer('CONTENT_TYPE', $originalContentType);
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
        $config = $this->createAppConfig();
        $this->createCookieConfig();

        $request  = new MockIncomingRequest($config, new SiteURI($config), null, new UserAgent());
        $response = new MockResponse($config);

        $controller = new class ($request, $response) {
            use ResponseTrait;

            public function __construct(
                protected RequestInterface $request,
                protected ResponseInterface $response,
            ) {
                $this->format = 'txt'; // @phpstan-ignore assign.propertyType (needed for testing)
            }
        };

        $this->invoke($controller, 'respondCreated', [['id' => 3], 'A Custom Reason']);

        $this->assertStringStartsWith(
            config('Format')->supportedResponseFormats[0],
            $response->getHeaderLine('Content-Type'),
        );
    }

    public function testResponseFormat(): void
    {
        $data       = ['foo' => 'something'];
        $controller = $this->makeController();

        $this->invoke($controller, 'setResponseFormat', ['json']);
        $this->invoke($controller, 'respond', [$data, 201]);

        $this->assertStringStartsWith(
            'application/json',
            $this->response->getHeaderLine('Content-Type'),
        );
        $this->assertSame($this->formatter->format($data), $this->response->getJSON());

        $this->invoke($controller, 'setResponseFormat', ['xml']);
        $this->invoke($controller, 'respond', [$data, 201]);

        $this->assertStringStartsWith(
            'application/xml',
            $this->response->getHeaderLine('Content-Type'),
        );
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

    /**
     * @param list<mixed> $args
     */
    private function invoke(object $controller, string $method, array $args = []): object
    {
        $method = self::getPrivateMethodInvoker($controller, $method);

        return $method(...$args);
    }

    /**
     * Helper method to create a mock Model with a mock Pager
     *
     * @param array<int, array<string, mixed>> $data
     */
    private function createMockModelWithPager(array $data, int $page, int $perPage, int $total, int $totalPages): Model
    {
        // Create a mock Pager
        $pager = $this->createMock(Pager::class);
        $pager->method('getCurrentPage')->willReturn($page);
        $pager->method('getPerPage')->willReturn($perPage);
        $pager->method('getTotal')->willReturn($total);
        $pager->method('getPageCount')->willReturn($totalPages);

        // Create a mock Model with a public pager property
        $model = $this->createMock(Model::class);

        $model->method('paginate')->willReturn($data);
        $model->pager = $pager;

        return $model;
    }

    public function testPaginateWithModel(): void
    {
        // Mock data
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        $model = $this->createMockModelWithPager($data, 1, 20, 50, 3);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 20]);

        // Check response structure
        $responseBody = json_decode($this->response->getBody(), true);

        $this->assertArrayHasKey('data', $responseBody);
        $this->assertArrayHasKey('meta', $responseBody);
        $this->assertArrayHasKey('links', $responseBody);

        // Check meta
        $this->assertSame(1, $responseBody['meta']['page']);
        $this->assertSame(20, $responseBody['meta']['perPage']);
        $this->assertSame(50, $responseBody['meta']['total']);
        $this->assertSame(3, $responseBody['meta']['totalPages']);

        // Check data
        $this->assertSame($data, $responseBody['data']);

        // Check headers
        $this->assertSame('50', $this->response->getHeaderLine('X-Total-Count'));
        $this->assertNotEmpty($this->response->getHeaderLine('Link'));
    }

    public function testPaginateWithQueryBuilder(): void
    {
        // Mock the database and builder
        $db = $this->createMock(BaseConnection::class);

        $builder = $this->getMockBuilder(BaseBuilder::class)
            ->setConstructorArgs(['test_table', $db])
            ->onlyMethods(['countAllResults', 'limit', 'get'])
            ->getMock();

        $result = $this->createMock(BaseResult::class);

        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        // Mock the query builder chain
        $builder->method('countAllResults')->willReturn(50);
        $builder->method('limit')->willReturnSelf();
        $builder->method('get')->willReturn($result);
        $result->method('getResultArray')->willReturn($data);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$builder, 20]);

        // Check response structure
        $responseBody = json_decode($this->response->getBody(), true);

        $this->assertArrayHasKey('data', $responseBody);
        $this->assertArrayHasKey('meta', $responseBody);
        $this->assertArrayHasKey('links', $responseBody);

        // Check meta
        $this->assertSame(1, $responseBody['meta']['page']);
        $this->assertSame(20, $responseBody['meta']['perPage']);
        $this->assertSame(50, $responseBody['meta']['total']);
        $this->assertSame(3, $responseBody['meta']['totalPages']);
    }

    public function testPaginateWithCustomPerPage(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
            ['id' => 4, 'name' => 'Item 4'],
            ['id' => 5, 'name' => 'Item 5'],
        ];

        $model = $this->createMockModelWithPager($data, 1, 5, 25, 5);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 5]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check meta with custom perPage
        $this->assertSame(5, $responseBody['meta']['perPage']);
        $this->assertSame(25, $responseBody['meta']['total']);
        $this->assertSame(5, $responseBody['meta']['totalPages']);
    }

    public function testPaginateWithPageParameter(): void
    {
        $data = [
            ['id' => 21, 'name' => 'Item 21'],
            ['id' => 22, 'name' => 'Item 22'],
        ];

        $model = $this->createMockModelWithPager($data, 2, 20, 50, 3);

        // Create controller with page=2 in query string
        $controller = $this->makeController('/api/items?page=2');
        Services::superglobals()->setGet('page', '2');

        $this->invoke($controller, 'paginate', [$model, 20]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check that we're on page 2
        $this->assertSame(2, $responseBody['meta']['page']);

        // Check links
        $this->assertStringContainsString('page=2', (string) $responseBody['links']['self']);
        $this->assertStringContainsString('page=1', (string) $responseBody['links']['prev']);
        $this->assertStringContainsString('page=3', (string) $responseBody['links']['next']);
    }

    public function testPaginateLinksStructure(): void
    {
        $data = [['id' => 1, 'name' => 'Item 1']];

        $model = $this->createMockModelWithPager($data, 2, 20, 100, 5);

        Services::superglobals()->setGet('page', '2');
        $controller = $this->makeController('/api/items?page=2');

        $this->invoke($controller, 'paginate', [$model, 20]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check all link types exist
        $this->assertArrayHasKey('self', $responseBody['links']);
        $this->assertArrayHasKey('first', $responseBody['links']);
        $this->assertArrayHasKey('last', $responseBody['links']);
        $this->assertArrayHasKey('prev', $responseBody['links']);
        $this->assertArrayHasKey('next', $responseBody['links']);

        // Check link values
        $this->assertStringContainsString('page=2', (string) $responseBody['links']['self']);
        $this->assertStringContainsString('page=1', (string) $responseBody['links']['first']);
        $this->assertStringContainsString('page=5', (string) $responseBody['links']['last']);
        $this->assertStringContainsString('page=1', (string) $responseBody['links']['prev']);
        $this->assertStringContainsString('page=3', (string) $responseBody['links']['next']);
    }

    public function testPaginateFirstPageNoPrevLink(): void
    {
        $data = [['id' => 1, 'name' => 'Item 1']];

        $model = $this->createMockModelWithPager($data, 1, 20, 50, 3);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 20]);

        $responseBody = json_decode($this->response->getBody(), true);

        // First page should not have a prev link
        $this->assertNull($responseBody['links']['prev']);
        // But should have a next link
        $this->assertNotNull($responseBody['links']['next']);
    }

    public function testPaginateLastPageNoNextLink(): void
    {
        $data = [['id' => 41, 'name' => 'Item 41']];

        $model = $this->createMockModelWithPager($data, 3, 20, 50, 3);

        Services::superglobals()->setGet('page', '3');
        $controller = $this->makeController('/api/items?page=3');

        $this->invoke($controller, 'paginate', [$model, 20]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Last page should not have a next link
        $this->assertNull($responseBody['links']['next']);
        // But should have a prev link
        $this->assertNotNull($responseBody['links']['prev']);
    }

    public function testPaginateLinkHeader(): void
    {
        $data = [['id' => 1, 'name' => 'Item 1']];

        $model = $this->createMockModelWithPager($data, 2, 20, 100, 5);

        Services::superglobals()->setGet('page', '2');
        $controller = $this->makeController('/api/items?page=2');

        $this->invoke($controller, 'paginate', [$model, 20]);

        $linkHeader = $this->response->getHeaderLine('Link');

        // Check that Link header is properly formatted
        $this->assertStringContainsString('rel="self"', $linkHeader);
        $this->assertStringContainsString('rel="first"', $linkHeader);
        $this->assertStringContainsString('rel="last"', $linkHeader);
        $this->assertStringContainsString('rel="prev"', $linkHeader);
        $this->assertStringContainsString('rel="next"', $linkHeader);

        // Check format <url>; rel="relation"
        $this->assertMatchesRegularExpression('/<[^>]+>;\s*rel="self"/', $linkHeader);
        $this->assertMatchesRegularExpression('/<[^>]+>;\s*rel="first"/', $linkHeader);
    }

    public function testPaginateXTotalCountHeader(): void
    {
        $data = [['id' => 1, 'name' => 'Item 1']];

        $model = $this->createMockModelWithPager($data, 1, 20, 150, 8);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 20]);

        // Check X-Total-Count header
        $this->assertSame('150', $this->response->getHeaderLine('X-Total-Count'));
    }

    public function testPaginateWithDatabaseException(): void
    {
        $model = $this->createMock(Model::class);

        // Make the model throw a DatabaseException
        $model->method('paginate')->willThrowException(
            new DatabaseException('Database error'),
        );

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 20]);

        // Should return a 500 error
        $this->assertSame(500, $this->response->getStatusCode());

        $responseBody = json_decode($this->response->getBody(), true);

        // Check error response structure
        $this->assertArrayHasKey('status', $responseBody);
        $this->assertArrayHasKey('error', $responseBody);
        $this->assertArrayHasKey('messages', $responseBody);
        $this->assertSame(500, $responseBody['status']);
    }

    public function testPaginateWithGenericException(): void
    {
        $model = $this->createMock(Model::class);

        // Make the model throw a generic exception
        $model->method('paginate')->willThrowException(
            new Exception('Generic error'),
        );

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 20]);

        // Should return a 500 error
        $this->assertSame(500, $this->response->getStatusCode());

        $responseBody = json_decode($this->response->getBody(), true);

        // Check error response structure
        $this->assertSame(500, $responseBody['status']);
        $this->assertArrayHasKey('error', $responseBody);
    }

    public function testPaginateWithNonDefaultPerPageInLinks(): void
    {
        $data = [['id' => 1, 'name' => 'Item 1']];

        $model = $this->createMockModelWithPager($data, 1, 10, 50, 5);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 10]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check that perPage is included in links when it's not the default (20)
        $this->assertStringContainsString('perPage=10', (string) $responseBody['links']['self']);
        $this->assertStringContainsString('perPage=10', (string) $responseBody['links']['first']);
        $this->assertStringContainsString('perPage=10', (string) $responseBody['links']['last']);
        $this->assertStringContainsString('perPage=10', (string) $responseBody['links']['next']);
    }

    public function testPaginatePreservesOtherQueryParameters(): void
    {
        $data = [['id' => 1, 'name' => 'Item 1']];

        $model = $this->createMockModelWithPager($data, 1, 20, 50, 3);

        Services::superglobals()->setGet('filter', 'active');
        Services::superglobals()->setGet('sort', 'name');
        $controller = $this->makeController('/api/items?filter=active&sort=name');

        $this->invoke($controller, 'paginate', [$model, 20]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check that other query parameters are preserved in links
        $this->assertStringContainsString('filter=active', (string) $responseBody['links']['self']);
        $this->assertStringContainsString('sort=name', (string) $responseBody['links']['self']);
        $this->assertStringContainsString('filter=active', (string) $responseBody['links']['next']);
        $this->assertStringContainsString('sort=name', (string) $responseBody['links']['next']);
    }

    public function testPaginateSinglePage(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        $model = $this->createMockModelWithPager($data, 1, 20, 2, 1);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 20]);

        $responseBody = json_decode($this->response->getBody(), true);

        // For a single page, prev and next should be null
        $this->assertNull($responseBody['links']['prev']);
        $this->assertNull($responseBody['links']['next']);
        // First and last should point to page 1
        $this->assertStringContainsString('page=1', (string) $responseBody['links']['first']);
        $this->assertStringContainsString('page=1', (string) $responseBody['links']['last']);
    }

    public function testPaginateWithTransformer(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        $model = $this->createMockModelWithPager($data, 1, 20, 2, 1);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 20, TestTransformer::class]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check that data is transformed
        $this->assertArrayHasKey('data', $responseBody);
        $this->assertCount(2, $responseBody['data']);

        // Check first item is transformed
        $this->assertArrayHasKey('transformed', $responseBody['data'][0]);
        $this->assertTrue($responseBody['data'][0]['transformed']);
        $this->assertArrayHasKey('name_upper', $responseBody['data'][0]);
        $this->assertSame('ITEM 1', $responseBody['data'][0]['name_upper']);

        // Check second item is transformed
        $this->assertArrayHasKey('transformed', $responseBody['data'][1]);
        $this->assertTrue($responseBody['data'][1]['transformed']);
        $this->assertArrayHasKey('name_upper', $responseBody['data'][1]);
        $this->assertSame('ITEM 2', $responseBody['data'][1]['name_upper']);

        // Meta and links should still be present
        $this->assertArrayHasKey('meta', $responseBody);
        $this->assertArrayHasKey('links', $responseBody);
    }

    public function testPaginateWithTransformerAndQueryBuilder(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        // Mock the database and builder
        $db = $this->createMock(BaseConnection::class);

        $builder = $this->getMockBuilder(BaseBuilder::class)
            ->setConstructorArgs(['test_table', $db])
            ->onlyMethods(['countAllResults', 'limit', 'get'])
            ->getMock();

        $result = $this->createMock(BaseResult::class);
        $result->method('getResultArray')->willReturn($data);

        $builder->method('countAllResults')->willReturn(2);
        $builder->method('limit')->willReturnSelf();
        $builder->method('get')->willReturn($result);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$builder, 20, TestTransformer::class]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check that data is transformed
        $this->assertArrayHasKey('data', $responseBody);
        $this->assertCount(2, $responseBody['data']);
        $this->assertTrue($responseBody['data'][0]['transformed']);
        $this->assertSame('ITEM 1', $responseBody['data'][0]['name_upper']);
    }

    public function testPaginateWithNonExistentTransformer(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
        ];

        $model = $this->createMockModelWithPager($data, 1, 20, 1, 1);

        $controller = $this->makeController('/api/items');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(lang('Api.transformerNotFound', ['NonExistent\\Transformer']));

        $this->invoke($controller, 'paginate', [$model, 20, 'NonExistent\\Transformer']);
    }

    public function testPaginateWithInvalidTransformer(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
        ];

        $model = $this->createMockModelWithPager($data, 1, 20, 1, 1);

        $controller = $this->makeController('/api/items');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(lang('Api.invalidTransformer', [InvalidTransformer::class]));

        $this->invoke($controller, 'paginate', [$model, 20, InvalidTransformer::class]);
    }

    public function testPaginateWithTransformerPreservesMetaAndLinks(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
        ];

        $model = $this->createMockModelWithPager($data, 1, 2, 10, 5);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 2, TestTransformer::class]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check meta is correct
        $this->assertSame(1, $responseBody['meta']['page']);
        $this->assertSame(2, $responseBody['meta']['perPage']);
        $this->assertSame(10, $responseBody['meta']['total']);
        $this->assertSame(5, $responseBody['meta']['totalPages']);

        // Check links are present
        $this->assertArrayHasKey('self', $responseBody['links']);
        $this->assertArrayHasKey('first', $responseBody['links']);
        $this->assertArrayHasKey('last', $responseBody['links']);
        $this->assertArrayHasKey('next', $responseBody['links']);
        $this->assertArrayHasKey('prev', $responseBody['links']);

        // Check headers
        $this->assertSame('10', $this->response->getHeaderLine('X-Total-Count'));
        $this->assertNotEmpty($this->response->getHeaderLine('Link'));
    }

    public function testPaginateWithTransformerEmptyData(): void
    {
        $data = [];

        $model = $this->createMockModelWithPager($data, 1, 20, 0, 0);

        $controller = $this->makeController('/api/items');

        $this->invoke($controller, 'paginate', [$model, 20, TestTransformer::class]);

        $responseBody = json_decode($this->response->getBody(), true);

        // Check that data is empty array
        $this->assertArrayHasKey('data', $responseBody);
        $this->assertSame([], $responseBody['data']);

        // Meta should show no results
        $this->assertSame(0, $responseBody['meta']['total']);
        $this->assertSame(0, $responseBody['meta']['totalPages']);
    }
}
