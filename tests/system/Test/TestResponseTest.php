<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use Config\App;
use Config\Services;
use PHPUnit\Framework\AssertionFailedError;

/**
 * @internal
 *
 * @group Others
 */
final class TestResponseTest extends CIUnitTestCase
{
    private ?TestResponse $testResponse = null;
    private Response $response;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider provideHttpStatusCodes
     */
    public function testIsOK(int $code, bool $isOk): void
    {
        $this->getTestResponse('Hello World');
        $this->response->setStatusCode($code);

        $this->assertSame($isOk, $this->testResponse->isOK());
    }

    /**
     * Provides status codes and their expected "OK"
     */
    public static function provideHttpStatusCodes(): iterable
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

    public function testIsOKEmpty(): void
    {
        $this->getTestResponse('Hi there');
        $this->response->setStatusCode(200);
        $this->response->setBody('');

        $this->assertFalse($this->testResponse->isOK());
    }

    public function testAssertSee(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->testResponse->assertSee('Hello');
        $this->testResponse->assertSee('World', 'h1');
    }

    public function testAssertDontSee(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->testResponse->assertDontSee('Worlds');
        $this->testResponse->assertDontSee('World', 'h2');
    }

    public function testAssertSeeElement(): void
    {
        $this->getTestResponse('<h1 class="header">Hello <span>World</span></h1>');

        $this->testResponse->assertSeeElement('h1');
        $this->testResponse->assertSeeElement('span');
        $this->testResponse->assertSeeElement('h1.header');
    }

    public function testAssertDontSeeElement(): void
    {
        $this->getTestResponse('<h1 class="header">Hello <span>World</span></h1>');

        $this->testResponse->assertDontSeeElement('h2');
        $this->testResponse->assertDontSeeElement('.span');
        $this->testResponse->assertDontSeeElement('h1.para');
    }

    public function testAssertSeeLink(): void
    {
        $this->getTestResponse('<h1 class="header"><a href="http://example.com/hello">Hello</a> <span>World</span></h1>');

        $this->testResponse->assertSeeElement('h1');
        $this->testResponse->assertSeeLink('Hello');
    }

    public function testAssertSeeInField(): void
    {
        $this->getTestResponse('<html><body><input type="text" name="user[name]" value="Foobar"></body></html>');

        $this->testResponse->assertSeeInField('user[name]', 'Foobar');
    }

    public function testAssertRedirectFail(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->assertNotInstanceOf(RedirectResponse::class, $this->testResponse->response());
        $this->assertFalse($this->testResponse->isRedirect());
        $this->testResponse->assertNotRedirect();
    }

    public function testAssertRedirectSuccess(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));

        $this->assertInstanceOf(RedirectResponse::class, $this->testResponse->response());
        $this->assertTrue($this->testResponse->isRedirect());
        $this->testResponse->assertRedirect();
    }

    public function testAssertRedirectSuccessWithoutRedirectResponse(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->redirect('foo/bar');

        $this->assertNotInstanceOf(RedirectResponse::class, $this->testResponse->response());
        $this->assertTrue($this->testResponse->isRedirect());
        $this->testResponse->assertRedirect();
        $this->assertSame('foo/bar', $this->testResponse->getRedirectUrl());
    }

    public function testGetRedirectUrlReturnsUrl(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));
        $this->testResponse->response()->redirect('foo/bar');

        $this->assertSame('foo/bar', $this->testResponse->getRedirectUrl());
    }

    public function testGetRedirectUrlReturnsNull(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->assertNull($this->testResponse->getRedirectUrl());
    }

    public function testRedirectToSuccess(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));
        $this->testResponse->response()->redirect('foo/bar');

        $this->testResponse->assertRedirectTo('foo/bar');
    }

    public function testRedirectToSuccessFullURL(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));
        $this->testResponse->response()->redirect('http://foo.com/bar');

        $this->testResponse->assertRedirectTo('http://foo.com/bar');
    }

    public function testRedirectToSuccessMixedURL(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->testResponse->setResponse(new RedirectResponse(new App()));
        $this->testResponse->response()->redirect('bar');

        $this->testResponse->assertRedirectTo('http://example.com/index.php/bar');
    }

    public function testAssertStatus(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>', ['statusCode' => 201]);

        $this->testResponse->assertStatus(201);
    }

    /**
     * @dataProvider provideHttpStatusCodes
     */
    public function testAssertIsOK(int $code, bool $isOk): void
    {
        $this->getTestResponse('<h1>Hello World</h1>', ['statusCode' => $code]);

        if ($isOk) {
            $this->testResponse->assertOK();
        } else {
            $this->testResponse->assertNotOK();
        }
    }

    public function testAssertSessionHas(): void
    {
        $_SESSION['foo'] = 'bar';
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->testResponse->assertSessionHas('foo');
        $this->testResponse->assertSessionHas('foo', 'bar');
    }

    public function testAssertSessionMissing(): void
    {
        $_SESSION = [];
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->testResponse->assertSessionMissing('foo');
    }

    public function testAssertHeader(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>', [], ['foo' => 'bar']);

        $this->testResponse->assertHeader('foo');
        $this->testResponse->assertHeader('foo', 'bar');
    }

    public function testAssertHeaderMissing(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>', [], ['foo' => 'bar']);

        $this->testResponse->assertHeader('foo');
        $this->testResponse->assertHeaderMissing('banana');
    }

    public function testAssertCookie(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->response = $this->response->setCookie('foo', 'bar');

        $this->testResponse->assertCookie('foo');
        $this->testResponse->assertCookie('foo', 'bar');
    }

    public function testAssertCookieMissing(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->response = $this->response->setCookie('foo', 'bar');

        $this->testResponse->assertCookieMissing('bar');
    }

    public function testAssertCookieExpired(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');

        $this->response = $this->response->setCookie('foo', 'bar', strtotime('-1 day'));

        $this->testResponse->assertCookieExpired('foo');
    }

    public function testGetJSON(): void
    {
        $data = ['foo' => 'bar'];
        $this->getTestResponse('');
        $this->response->setJSON($data, true);

        $formatter = Services::format()->getFormatter('application/json');
        $this->assertSame($formatter->format($data), $this->testResponse->getJSON());
    }

    public function testGetJSONEmptyJSON(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->setJSON('', true);

        // this should be "" - json_encode('');
        $this->assertSame('""', $this->testResponse->getJSON());
    }

    public function testGetJSONFalseJSON(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->setJSON(false, true);

        // this should be FALSE - json_encode(false)
        $this->assertSame('false', $this->testResponse->getJSON());
    }

    public function testGetJSONTrueJSON(): void
    {
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->setJSON(true, true);

        // this should be TRUE - json_encode(true)
        $this->assertSame('true', $this->testResponse->getJSON());
    }

    public function testGetJSONInvalidJSON(): void
    {
        $tmp = ' test " case ';
        $this->getTestResponse('<h1>Hello World</h1>');
        $this->response->setBody($tmp);

        // this should be FALSE - invalid JSON - will see if this is working that way ;-)
        $this->assertNotSame($this->testResponse->getJSON(), $this->response->getBody());
    }

    public function testGetXML(): void
    {
        $data = ['foo' => 'bar'];
        $this->getTestResponse('');
        $this->response->setXML($data);

        $formatter = Services::format()->getFormatter('application/xml');
        $this->assertSame($formatter->format($data), $this->testResponse->getXML());
    }

    public function testAssertJSONFragment(): void
    {
        $data = [
            'config' => [
                'key-a',
                'key-b',
            ],
        ];
        $this->getTestResponse('');
        $this->response->setJSON($data, true);

        $this->testResponse->assertJSONFragment(['config' => ['key-a']]);
        $this->testResponse->assertJSONFragment(['config' => ['key-a']], true);
    }

    public function testAssertJSONFragmentFollowingAssertArraySubset(): void
    {
        $data = [
            'config' => '124',
        ];
        $this->getTestResponse('');
        $this->response->setJSON($data, true);

        $this->testResponse->assertJSONFragment(['config' => 124]); // must fail on strict
        $this->testResponse->assertJSONFragment(['config' => '124'], true);
    }

    public function testAssertJSONFragmentFailsGracefullyWhenNotGivenJson(): void
    {
        $this->getTestResponse('<h1>Hello World!</h1>');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Response does not have valid json');

        $this->testResponse->assertJSONFragment(['foo' => 'bar']);
    }

    public function testAssertJsonExactArray(): void
    {
        $data = [
            'config' => [
                'key-a',
                'key-b',
            ],
        ];
        $this->getTestResponse('');
        $this->response->setJSON($data, true);

        $this->testResponse->assertJSONExact($data);
    }

    public function testAssertJsonExactObject(): void
    {
        $data = (object) [
            'config' => [
                'key-a',
                'key-b',
            ],
        ];
        $this->getTestResponse('');
        $this->response->setJSON($data, true);

        $this->testResponse->assertJSONExact($data);
    }

    public function testAssertJsonExactString(): void
    {
        $data = [
            'config' => [
                'key-a',
                'key-b',
            ],
        ];
        $this->getTestResponse('');
        $this->response->setJSON($data, true);

        $formatter = Services::format()->getFormatter('application/json');
        $this->testResponse->assertJSONExact($formatter->format($data));
    }

    protected function getTestResponse(?string $body = null, array $responseOptions = [], array $headers = []): void
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
