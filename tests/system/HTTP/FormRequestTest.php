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

use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Support\Controllers\FormRequestController;
use Tests\Support\HTTP\Requests\ValidPostFormRequest;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class FormRequestTest extends CIUnitTestCase
{
    private MockCodeIgniter $codeigniter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();
        Services::injectMock('superglobals', new Superglobals());
        service('superglobals')->setServer('REQUEST_METHOD', 'POST');
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        service('superglobals')->setServer('SERVER_NAME', 'example.com');
        service('superglobals')->setServer('HTTP_HOST', 'example.com');
        /** @var Response $response */
        $response = service('response');
        $response->pretend(true);
        $this->codeigniter = new MockCodeIgniter(new App());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetServices();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Creates an IncomingRequest.
     * POST data must be set on the superglobals BEFORE calling this.
     */
    private function makeRequest(?string $body = null): IncomingRequest
    {
        $config = new App();

        return new IncomingRequest($config, new SiteURI($config), $body, new UserAgent());
    }

    /**
     * Returns a concrete FormRequest subclass instance that requires
     * 'title' (required|min_length[3]) and 'body' (required).
     */
    private function makeFormRequest(IncomingRequest $request): FormRequest
    {
        return new class ($request) extends FormRequest {
            public function rules(): array
            {
                return [
                    'title' => 'required|min_length[3]',
                    'body'  => 'required',
                ];
            }
        };
    }

    /**
     * Injects a router pointing at FormRequestController::$method
     * for the given URI, then runs the app and returns the response.
     */
    private function runRequest(string $uri, string $method, string $httpMethod = 'GET'): ResponseInterface
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_METHOD', $httpMethod);
        $superglobals->setServer('REQUEST_URI', $uri);
        $superglobals->setServer('SCRIPT_NAME', '/index.php');
        $superglobals->setServer('argv', ['index.php', ltrim($uri, '/')]);
        $superglobals->setServer('argc', 2);

        $routes = service('routes');
        $routes->setAutoRoute(false);
        $routes->add(ltrim($uri, '/'), '\\' . FormRequestController::class . '::' . $method);

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        return $this->codeigniter->run(null, true);
    }

    // -------------------------------------------------------------------------
    // Default behaviours
    // -------------------------------------------------------------------------

    public function testDefaultMessagesReturnsEmptyArray(): void
    {
        $formRequest = $this->makeFormRequest($this->makeRequest());

        $this->assertSame([], $formRequest->messages());
    }

    public function testDefaultAuthorizeReturnsTrue(): void
    {
        $formRequest = $this->makeFormRequest($this->makeRequest());

        $this->assertTrue($formRequest->isAuthorized());
    }

    public function testConstructorThrowsWhenFallbackRequestIsNotIncomingRequest(): void
    {
        Services::injectMock('request', new CLIRequest(new App()));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires an IncomingRequest instance, got CodeIgniter\HTTP\CLIRequest.');

        new class () extends FormRequest {
            public function rules(): array
            {
                return [];
            }
        };
    }

    public function testValidatedReturnsEmptyArrayBeforeResolution(): void
    {
        $formRequest = $this->makeFormRequest($this->makeRequest());

        $this->assertSame([], $formRequest->validated());
    }

    // -------------------------------------------------------------------------
    // Successful resolution
    // -------------------------------------------------------------------------

    public function testResolveRequestPassesWithValidData(): void
    {
        service('superglobals')->setPost('title', 'Hello World');
        service('superglobals')->setPost('body', 'Some body text');

        $formRequest = $this->makeFormRequest($this->makeRequest());
        $this->assertNotInstanceOf(ResponseInterface::class, $formRequest->resolveRequest());

        $this->assertSame(
            ['title' => 'Hello World', 'body' => 'Some body text'],
            $formRequest->validated(),
        );
    }

    public function testValidatedReturnsOnlyFieldsCoveredByRules(): void
    {
        service('superglobals')->setPost('title', 'Hello World');
        service('superglobals')->setPost('body', 'Some body text');
        service('superglobals')->setPost('extra_field', 'should be excluded');

        $formRequest = $this->makeFormRequest($this->makeRequest());
        $formRequest->resolveRequest();

        $validated = $formRequest->validated();

        $this->assertArrayHasKey('title', $validated);
        $this->assertArrayHasKey('body', $validated);
        $this->assertArrayNotHasKey('extra_field', $validated);
    }

    // -------------------------------------------------------------------------
    // Explicit access to validated fields
    // -------------------------------------------------------------------------

    public function testGetValidatedReturnsValidatedFieldValue(): void
    {
        service('superglobals')->setPost('title', 'Hello World');
        service('superglobals')->setPost('body', 'Some body text');

        $formRequest = new ValidPostFormRequest($this->makeRequest());
        $formRequest->resolveRequest();

        $this->assertSame('Hello World', $formRequest->getValidated('title'));
        $this->assertSame('Some body text', $formRequest->getValidated('body'));
    }

    public function testGetValidatedReturnsNullForMissingField(): void
    {
        service('superglobals')->setPost('title', 'Hello World');
        service('superglobals')->setPost('body', 'Some body text');

        $formRequest = new ValidPostFormRequest($this->makeRequest());
        $formRequest->resolveRequest();

        $this->assertNull($formRequest->getValidated('nonexistent'));
    }

    public function testGetValidatedReturnsDefaultForMissingField(): void
    {
        service('superglobals')->setPost('title', 'Hello World');
        service('superglobals')->setPost('body', 'Some body text');

        $formRequest = new ValidPostFormRequest($this->makeRequest());
        $formRequest->resolveRequest();

        $this->assertSame('fallback', $formRequest->getValidated('nonexistent', 'fallback'));
    }

    public function testGetValidatedReturnsNullBeforeValidationRuns(): void
    {
        $formRequest = new ValidPostFormRequest($this->makeRequest());

        $this->assertNull($formRequest->getValidated('title'));
    }

    public function testHasValidatedReturnsTrueForValidatedField(): void
    {
        service('superglobals')->setPost('title', 'Hello World');
        service('superglobals')->setPost('body', 'Some body text');

        $formRequest = new ValidPostFormRequest($this->makeRequest());
        $formRequest->resolveRequest();

        $this->assertTrue($formRequest->hasValidated('title'));
    }

    public function testHasValidatedReturnsFalseForMissingField(): void
    {
        service('superglobals')->setPost('title', 'Hello World');
        service('superglobals')->setPost('body', 'Some body text');

        $formRequest = new ValidPostFormRequest($this->makeRequest());
        $formRequest->resolveRequest();

        $this->assertFalse($formRequest->hasValidated('nonexistent'));
    }

    public function testGetValidatedAndHasValidatedSupportDotSyntax(): void
    {
        service('superglobals')->setPost('post', [
            'title' => 'Hello World',
            'meta'  => [
                'slug' => 'hello-world',
            ],
        ]);

        $formRequest = new class ($this->makeRequest()) extends FormRequest {
            public function rules(): array
            {
                return [
                    'post.title'     => 'required',
                    'post.meta.slug' => 'required',
                ];
            }
        };

        $formRequest->resolveRequest();

        $this->assertSame('Hello World', $formRequest->getValidated('post.title'));
        $this->assertSame('hello-world', $formRequest->getValidated('post.meta.slug'));
        $this->assertTrue($formRequest->hasValidated('post.meta.slug'));
    }

    public function testHasValidatedReturnsTrueForNullValidatedField(): void
    {
        service('superglobals')->setServer('CONTENT_TYPE', 'application/json');

        $formRequest = new class ($this->makeRequest('{"note":null}')) extends FormRequest {
            public function rules(): array
            {
                return ['note' => 'permit_empty'];
            }
        };

        $formRequest->resolveRequest();

        $this->assertSame(['note' => null], $formRequest->validated());
        $this->assertNull($formRequest->getValidated('note'));
        $this->assertNull($formRequest->getValidated('note', 'fallback'));
        $this->assertTrue($formRequest->hasValidated('note'));
    }

    // -------------------------------------------------------------------------
    // prepareForValidation hook
    // -------------------------------------------------------------------------

    public function testPrepareForValidationIsCalledBeforeValidation(): void
    {
        service('superglobals')->setPost('title', 'Hi'); // Too short - min_length[3] would normally fail.

        // This FormRequest normalises the title in prepareForValidation so it passes.
        $formRequest = new class ($this->makeRequest()) extends FormRequest {
            public static bool $prepareCalled = false;

            public function rules(): array
            {
                return ['title' => 'required|min_length[3]'];
            }

            protected function prepareForValidation(array $data): array
            {
                self::$prepareCalled = true;
                // Extend the title so it meets the min_length rule.
                $data['title'] = 'Hi extended';

                return $data;
            }
        };

        $this->assertNotInstanceOf(ResponseInterface::class, $formRequest->resolveRequest());

        $this->assertTrue($formRequest::$prepareCalled);
        $this->assertSame('Hi extended', $formRequest->validated()['title']);
    }

    // -------------------------------------------------------------------------
    // Validation failure
    // -------------------------------------------------------------------------

    #[RunInSeparateProcess]
    public function testFailedValidationFlashesErrorsToSession(): void
    {
        /** @var array<string, mixed> $_SESSION */
        $_SESSION = [];

        // No POST data - both required rules will fail and the default
        // failedValidation() should flash errors to _ci_validation_errors.
        $formRequest = $this->makeFormRequest($this->makeRequest());
        $formRequest->resolveRequest();

        $this->assertArrayHasKey('_ci_validation_errors', $_SESSION);
        $this->assertArrayHasKey('title', $_SESSION['_ci_validation_errors']);
        $this->assertArrayHasKey('body', $_SESSION['_ci_validation_errors']);
    }

    public function testResolveRequestReturnsResponseOnInvalidData(): void
    {
        // No POST data - both required rules will fail.
        $formRequest = $this->makeFormRequest($this->makeRequest());

        $response = $formRequest->resolveRequest();

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testFailedValidationResponseContainsErrors(): void
    {
        service('superglobals')->setServer('CONTENT_TYPE', 'application/json');

        $formRequest = new class ($this->makeRequest('{}')) extends FormRequest {
            public function rules(): array
            {
                return ['title' => 'required'];
            }
        };

        $response = $formRequest->resolveRequest();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('title', $body['errors']);
    }

    public function testResolveRequestReturns422ForJsonRequest(): void
    {
        service('superglobals')->setServer('CONTENT_TYPE', 'application/json');
        // Body is an empty JSON object - no fields provided.
        $formRequest = $this->makeFormRequest($this->makeRequest('{}'));

        $response = $formRequest->resolveRequest();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testResolveRequestReturns422ForAjaxRequest(): void
    {
        service('superglobals')->setServer('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');
        $formRequest = $this->makeFormRequest($this->makeRequest());

        $response = $formRequest->resolveRequest();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(422, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Authorization failure
    // -------------------------------------------------------------------------

    public function testResolveRequestReturns403WhenUnauthorized(): void
    {
        $formRequest = new class ($this->makeRequest()) extends FormRequest {
            public function rules(): array
            {
                return [];
            }

            public function isAuthorized(): bool
            {
                return false;
            }
        };

        $response = $formRequest->resolveRequest();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testAuthorizationIsCheckedBeforeValidation(): void
    {
        // Use a static property to record call order without needing a custom constructor.
        $formRequest = new class ($this->makeRequest()) extends FormRequest {
            /**
             * @var list<string>
             */
            public static array $order = [];

            public function rules(): array
            {
                return ['title' => 'required'];
            }

            public function isAuthorized(): bool
            {
                self::$order[] = 'authorize';

                return false;
            }

            protected function prepareForValidation(array $data): array
            {
                self::$order[] = 'prepare';

                return $data;
            }
        };

        $formRequest->resolveRequest();

        // isAuthorized() must fire before prepareForValidation(); validation never runs.
        $this->assertSame(['authorize'], $formRequest::$order);
    }

    // -------------------------------------------------------------------------
    // Custom overrides
    // -------------------------------------------------------------------------

    public function testValidationDataOverrideIsUsed(): void
    {
        // No POST data at all - but validationData() supplies its own.
        $formRequest = new class ($this->makeRequest()) extends FormRequest {
            public function rules(): array
            {
                return ['title' => 'required|min_length[3]'];
            }

            protected function validationData(): array
            {
                return ['title' => 'Injected Title'];
            }
        };

        $this->assertNotInstanceOf(ResponseInterface::class, $formRequest->resolveRequest());

        $this->assertSame('Injected Title', $formRequest->validated()['title']);
    }

    public function testCustomFailedValidationIsRespected(): void
    {
        $formRequest = new class ($this->makeRequest()) extends FormRequest {
            public static bool $called = false;

            public function rules(): array
            {
                return ['title' => 'required'];
            }

            protected function failedValidation(array $errors): ResponseInterface
            {
                self::$called = true;

                return service('response')->setStatusCode(422)->setJSON(['errors' => $errors]);
            }
        };

        $response = $formRequest->resolveRequest();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertTrue($formRequest::$called);
    }

    public function testCustomFailedAuthorizationIsRespected(): void
    {
        $formRequest = new class ($this->makeRequest()) extends FormRequest {
            public static bool $called = false;

            public function rules(): array
            {
                return [];
            }

            public function isAuthorized(): bool
            {
                return false;
            }

            protected function failedAuthorization(): ResponseInterface
            {
                self::$called = true;

                return service('response')->setStatusCode(403);
            }
        };

        $response = $formRequest->resolveRequest();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertTrue($formRequest::$called);
    }

    // -------------------------------------------------------------------------
    // Integration: BC - methods without FormRequest are unaffected
    // -------------------------------------------------------------------------

    #[RunInSeparateProcess]
    public function testControllerMethodWithoutFormRequestReceivesRouteParam(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_METHOD', 'GET');
        $superglobals->setServer('REQUEST_URI', '/items/42');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');
        $superglobals->setServer('argv', ['index.php', 'items/42']);
        $superglobals->setServer('argc', 2);

        $routes = service('routes');
        $routes->setAutoRoute(false);
        $routes->add('items/(:segment)', '\\' . FormRequestController::class . '::show/$1');

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run(null, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('item-42', (string) $response->getBody());
    }

    // -------------------------------------------------------------------------
    // Integration: valid FormRequest
    // -------------------------------------------------------------------------

    #[RunInSeparateProcess]
    public function testValidFormRequestInjectedAndControllerReceivesValidatedData(): void
    {
        service('superglobals')->setPost('title', 'My Post');
        service('superglobals')->setPost('body', 'Post content here');

        $response = $this->runRequest('/posts', 'store', 'POST');

        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('My Post', $body['title']);
        $this->assertSame('Post content here', $body['body']);
    }

    #[RunInSeparateProcess]
    public function testRouteParamAndFormRequestBothReachController(): void
    {
        service('superglobals')->setPost('title', 'Updated Title');
        service('superglobals')->setPost('body', 'Updated body content');

        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_METHOD', 'POST');
        $superglobals->setServer('REQUEST_URI', '/posts/99');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');
        $superglobals->setServer('argv', ['index.php', 'posts/99']);
        $superglobals->setServer('argc', 2);

        $routes = service('routes');
        $routes->setAutoRoute(false);
        $routes->add('posts/(:segment)', '\\' . FormRequestController::class . '::update/$1');

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run(null, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('99', $body['id']);
        $this->assertSame('Updated Title', $body['data']['title']);
        $this->assertSame('Updated body content', $body['data']['body']);
    }

    #[RunInSeparateProcess]
    public function testOptionalTrailingParamReceivesDefaultWhenSegmentAbsent(): void
    {
        service('superglobals')->setPost('title', 'Hello');
        service('superglobals')->setPost('body', 'Body text');

        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_METHOD', 'POST');
        $superglobals->setServer('REQUEST_URI', '/posts/42');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');
        $superglobals->setServer('argv', ['index.php', 'posts/42']);
        $superglobals->setServer('argc', 2);

        $routes = service('routes');
        $routes->setAutoRoute(false);
        // Route provides only $id; $format is absent so its default 'json' applies.
        $routes->add('posts/(:segment)', '\\' . FormRequestController::class . '::index/$1');

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run(null, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('42', $body['id']);
        $this->assertSame('json', $body['format']);
        $this->assertSame('Hello', $body['data']['title']);
    }

    #[RunInSeparateProcess]
    public function testVariadicRouteParamsAlongsideFormRequestAreAllCollected(): void
    {
        service('superglobals')->setPost('title', 'Tagged Post');
        service('superglobals')->setPost('body', 'Post body');

        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_METHOD', 'POST');
        $superglobals->setServer('REQUEST_URI', '/search/php/ci4');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');
        $superglobals->setServer('argv', ['index.php', 'search/php/ci4']);
        $superglobals->setServer('argc', 2);

        $routes = service('routes');
        $routes->setAutoRoute(false);
        $routes->add('search/(:segment)/(:segment)', '\\' . FormRequestController::class . '::search/$1/$2');

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run(null, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame(['php', 'ci4'], $body['tags']);
        $this->assertSame('Tagged Post', $body['data']['title']);
    }

    #[RunInSeparateProcess]
    public function testClosureRouteWithFormRequestIsInjected(): void
    {
        service('superglobals')->setPost('title', 'Closure Title');
        service('superglobals')->setPost('body', 'Closure body text');

        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_METHOD', 'POST');
        $superglobals->setServer('REQUEST_URI', '/closure/55');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');
        $superglobals->setServer('argv', ['index.php', 'closure/55']);
        $superglobals->setServer('argc', 2);

        $routes = service('routes');
        $routes->setAutoRoute(false);
        $routes->add('closure/(:segment)', static fn (string $id, ValidPostFormRequest $request): string => json_encode(['id' => $id, 'data' => $request->validated()]));

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run(null, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('55', $body['id']);
        $this->assertSame('Closure Title', $body['data']['title']);
        $this->assertSame('Closure body text', $body['data']['body']);
    }

    // -------------------------------------------------------------------------
    // Integration: validation failure - web redirect
    // -------------------------------------------------------------------------

    #[RunInSeparateProcess]
    public function testInvalidFormRequestRedirectsWebRequest(): void
    {
        service('superglobals')->setServer('HTTP_REFERER', 'http://example.com/posts/create');

        // No POST data → required rules fail.
        $response = $this->runRequest('/posts', 'store', 'POST');

        // For POST requests under HTTP/1.1, CI4 correctly issues 303 See Other (Post/Redirect/Get).
        $this->assertSame(303, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Integration: validation failure - JSON / AJAX
    // -------------------------------------------------------------------------

    #[RunInSeparateProcess]
    public function testInvalidFormRequestReturns422ForJsonRequest(): void
    {
        service('superglobals')->setServer('CONTENT_TYPE', 'application/json');

        // No valid POST/JSON data → required rules fail.
        $response = $this->runRequest('/posts', 'store', 'POST');

        $this->assertSame(422, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('title', $body['errors']);
    }

    #[RunInSeparateProcess]
    public function testInvalidFormRequestReturns422ForAjaxRequest(): void
    {
        service('superglobals')->setServer('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');

        $response = $this->runRequest('/posts', 'store', 'POST');

        $this->assertSame(422, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Integration: authorization failure
    // -------------------------------------------------------------------------

    #[RunInSeparateProcess]
    public function testUnauthorizedFormRequestReturns403(): void
    {
        $response = $this->runRequest('/admin/resource', 'restricted', 'POST');

        $this->assertSame(403, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Integration: validated() only returns fields declared in rules()
    // -------------------------------------------------------------------------

    #[RunInSeparateProcess]
    public function testValidatedExcludesFieldsNotInRules(): void
    {
        service('superglobals')->setPost('title', 'A title that is long enough');
        service('superglobals')->setPost('body', 'The body');
        service('superglobals')->setPost('__csrf_token', 'secret');
        service('superglobals')->setPost('extra_noise', 'ignored');

        $response = $this->runRequest('/posts', 'store', 'POST');

        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayNotHasKey('__csrf_token', $body);
        $this->assertArrayNotHasKey('extra_noise', $body);
    }
}
