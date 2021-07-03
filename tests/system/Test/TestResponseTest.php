<?php

namespace CodeIgniter\Test;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Config\App;
use Config\Services;
use PHPUnit\Framework\AssertionFailedError;

/**
 * @internal
 */
final class TestResponseTest extends CIUnitTestCase
{
    /**
     * @var TestResponse
     */
    protected $testResponse;

    /**
     * @var ResponseInterface
     */
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider statusCodeProvider
     */
    public function testIsOK(int $code, bool $isOk)
    {
        $this->getTestResponse('Hello World');
        $this->response->setStatusCode($code);

        $this->assertSame($isOk, $this->testResponse->isOK());
    }

    /**
     * Provides status codes and their expected "OK"
     */
    public function statusCodeProvider(): array
    {
        return [
            [
                100,
                false,
            ],
            [
                200,
                true,
            ],
            [
                201,
                true,
            ],
            [
                300,
                true,
            ], // Redirects are acceptable if the body is empty
            [
                301,
                true,
            ],
            [
                400,
                false,
            ],
            [
                401,
                false,
            ],
            [
                599,
                false,
            ],
        ];
    }

    public function testIsOKEmpty()
    {
        $this->getTestResponse('Hi there');
        $this->response->setStatusCode(200);
        $this->response->setBody('');

        $this->assertFalse($this->testResponse->isOK());
    }

    public function testAssertSee()
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->testResponse->assertSee('Hello');
        $this->testResponse->assertSee('World', 'h1');
    }

    public function testAssertDontSee()
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->testResponse->assertDontSee('Worlds');
        $this->testResponse->assertDontSee('World', 'h2');
    }

    public function testAssertSeeElement()
    {
        $this->getTestResponse('<h1 class="header">Hello <span>World</span></h1>');

        $this->testResponse->assertSeeElement('h1');
        $this->testResponse->assertSeeElement('span');
        $this->testResponse->assertSeeElement('h1.header');
    }

    public function testAssertDontSeeElement()
    {
        $this->getTestResponse('<h1 class="header">Hello <span>World</span></h1>');

        $this->testResponse->assertDontSeeElement('h2');
        $this->testResponse->assertDontSeeElement('.span');
        $this->testResponse->assertDontSeeElement('h1.para');
    }

    public function testAssertSeeLink()
    {
        $this->getTestResponse('<h1 class="header"><a href="http://example.com/hello">Hello</a> <span>World</span></h1>');

        $this->testResponse->assertSeeElement('h1');
        $this->testResponse->assertSeeLink('Hello');
    }

    public function testAssertSeeInField()
    {
        $this->getTestResponse('<html><body><input type="text" name="user[name]" value="Foobar"></body></html>');

        $this->testResponse->assertSeeInField('user[name]', 'Foobar');
    }

    public function testAssertRedirectFail()
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->assertFalse($this->testResponse->response() instanceof RedirectResponse);
        $this->assertFalse($this->testResponse->isRedirect());
        $this->testResponse->assertNotRedirect();
    }

    public function testAssertRedirectSuccess()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));

        $this->assertTrue($this->testResponse->response() instanceof RedirectResponse);
        $this->assertTrue($this->testResponse->isRedirect());
        $this->testResponse->assertRedirect();
    }

    public function testAssertRedirectSuccessWithoutRedirectResponse()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->redirect('foo/bar');

        $this->assertFalse($this->testResponse->response() instanceof RedirectResponse);
        $this->assertTrue($this->testResponse->isRedirect());
        $this->testResponse->assertRedirect();
        $this->assertSame('foo/bar', $this->testResponse->getRedirectUrl());
    }

    public function testGetRedirectUrlReturnsUrl()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));
        $this->testResponse->response()->redirect('foo/bar');

        $this->assertSame('foo/bar', $this->testResponse->getRedirectUrl());
    }

    public function testGetRedirectUrlReturnsNull()
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->assertNull($this->testResponse->getRedirectUrl());
    }

    public function testRedirectToSuccess()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));
        $this->testResponse->response()->redirect('foo/bar');

        $this->testResponse->assertRedirectTo('foo/bar');
    }

    public function testRedirectToSuccessFullURL()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));
        $this->testResponse->response()->redirect('http://foo.com/bar');

        $this->testResponse->assertRedirectTo('http://foo.com/bar');
    }

    public function testRedirectToSuccessMixedURL()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));
        $this->testResponse->response()->redirect('bar');

        $this->testResponse->assertRedirectTo('http://example.com/index.php/bar');
    }

    public function testAssertStatus()
    {
        $this->getTestResponse('<h1>Hello World</h1>', ['statusCode' => 201]);

        $this->testResponse->assertStatus(201);
    }

    /**
     * @dataProvider statusCodeProvider
     */
    public function testAssertIsOK(int $code, bool $isOk)
    {
        $this->getTestResponse('<h1>Hello World</h1>', ['statusCode' => $code]);

        if ($isOk) {
            $this->testResponse->assertOK();
        } else {
            $this->testResponse->assertNotOK();
        }
    }

    public function testAssertSessionHas()
    {
        $_SESSION['foo'] = 'bar';
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->testResponse->assertSessionHas('foo');
        $this->testResponse->assertSessionHas('foo', 'bar');
    }

    public function testAssertSessionMissing()
    {
        $_SESSION = [];
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->testResponse->assertSessionMissing('foo');
    }

    public function testAssertHeader()
    {
        $this->getTestResponse('<h1>Hello World</h1>', [], ['foo' => 'bar']);

        $this->testResponse->assertHeader('foo');
        $this->testResponse->assertHeader('foo', 'bar');
    }

    public function testAssertHeaderMissing()
    {
        $this->getTestResponse('<h1>Hello World</h1>', [], ['foo' => 'bar']);

        $this->testResponse->assertHeader('foo');
        $this->testResponse->assertHeaderMissing('banana');
    }

    public function testAssertCookie()
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->response = $this->response->setCookie('foo', 'bar');

        $this->testResponse->assertCookie('foo');
        $this->testResponse->assertCookie('foo', 'bar');
    }

    public function testAssertCookieMissing()
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->response = $this->response->setCookie('foo', 'bar');

        $this->testResponse->assertCookieMissing('bar');
    }

    public function testAssertCookieExpired()
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->response = $this->response->setCookie('foo', 'bar', strtotime('-1 day'));

        $this->testResponse->assertCookieExpired('foo');
    }

    public function testGetJSON()
    {
        $this->getTestResponse(['foo' => 'bar']);
        $formatter = Services::format()->getFormatter('application/json');

        $this->assertSame($formatter->format(['foo' => 'bar']), $this->testResponse->getJSON());
    }

    public function testEmptyJSON()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->setJSON('', true);

        // this should be "" - json_encode('');
        $this->assertSame('""', $this->testResponse->getJSON());
    }

    public function testFalseJSON()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->setJSON(false, true);

        // this should be FALSE - json_encode(false)
        $this->assertSame('false', $this->testResponse->getJSON());
    }

    public function testTrueJSON()
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->setJSON(true, true);

        // this should be TRUE - json_encode(true)
        $this->assertSame('true', $this->testResponse->getJSON());
    }

    public function testInvalidJSON()
    {
        $tmp = ' test " case ';
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->setBody($tmp);

        // this should be FALSE - invalid JSON - will see if this is working that way ;-)
        $this->assertFalse($this->response->getBody() === $this->testResponse->getJSON());
    }

    public function testGetXML()
    {
        $this->getTestResponse(['foo' => 'bar']);
        $formatter = Services::format()->getFormatter('application/xml');

        $this->assertSame($formatter->format(['foo' => 'bar']), $this->testResponse->getXML());
    }

    public function testJsonFragment()
    {
        $this->getTestResponse([
            'config' => [
                'key-a',
                'key-b',
            ],
        ]);

        $this->testResponse->assertJSONFragment(['config' => ['key-a']]);
        $this->testResponse->assertJSONFragment(['config' => ['key-a']], true);
    }

    public function testAssertJSONFragmentFollowingAssertArraySubset()
    {
        $this->getTestResponse([
            'config' => '124',
        ]);

        $this->testResponse->assertJSONFragment(['config' => 124]); // must fail on strict
        $this->testResponse->assertJSONFragment(['config' => '124'], true);
    }

    public function testAssertJSONFragmentFailsGracefullyWhenNotGivenJson()
    {
        $this->getTestResponse('<h1>Hello World!</h1>');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Response does not have valid json');

        $this->testResponse->assertJSONFragment(['foo' => 'bar']);
    }

    public function testJsonExact()
    {
        $data = [
            'config' => [
                'key-a',
                'key-b',
            ],
        ];

        $this->getTestResponse($data);

        $this->testResponse->assertJSONExact($data);
    }

    public function testJsonExactString()
    {
        $data = [
            'config' => [
                'key-a',
                'key-b',
            ],
        ];

        $this->getTestResponse($data);
        $formatter = Services::format()->getFormatter('application/json');

        $this->testResponse->assertJSONExact($formatter->format($data));
    }

    protected function getTestResponse($body = null, array $responseOptions = [], array $headers = [])
    {
        $this->response = new Response(new App());
        $this->response->setBody($body);

        foreach ($responseOptions as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this->response, $method)) {
                $this->response = $this->response->{$method}($value);
            }
        }

        foreach ($headers as $key => $value) {
            $this->response = $this->response->setHeader($key, $value);
        }

        $this->testResponse = new TestResponse($this->response);
    }
}
