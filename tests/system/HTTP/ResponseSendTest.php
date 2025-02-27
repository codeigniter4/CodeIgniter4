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

use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * This test suite has been created separately from
 * TestCaseTest because it messes with output
 * buffering from PHPUnit, and the individual
 * test cases need to be run as separate processes.
 *
 * @internal
 */
#[Group('SeparateProcess')]
final class ResponseSendTest extends CIUnitTestCase
{
    /**
     * These need to be run as a separate process, since phpunit
     * has already captured the "normal" output, and we will get
     * a "Cannot modify headers" message if we try to change
     * headers or cookies now.
     *
     * Furthermore, these tests needs to flush the output buffering
     * that might be in progress, and start our own output buffer
     * capture.
     *
     * The tests includes a basic sanity check, to make sure that
     * the body we thought would be sent actually was.
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testHeadersMissingDate(): void
    {
        $response = new Response(new App());
        $response->pretend(false);

        $body = 'Hello';
        $response->setBody($body);

        $response->setCookie('foo', 'bar');
        $this->assertTrue($response->hasCookie('foo'));
        $this->assertTrue($response->hasCookie('foo', 'bar'));

        // Drop the date header, to make sure it gets put back in
        $response->removeHeader('Date');

        // send it
        ob_start();
        $response->send();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // and what actually got sent?
        $this->assertHeaderEmitted('Date:');
    }

    /**
     * This test does not test that CSP is handled properly -
     * it makes sure that sending gives CSP a chance to do its thing.
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testHeadersWithCSP(): void
    {
        $this->resetFactories();
        $this->resetServices();

        $config             = config('App');
        $config->CSPEnabled = true;
        $response           = new Response($config);
        $response->pretend(false);

        $body = 'Hello';
        $response->setBody($body);

        $response->setCookie('foo', 'bar');
        $this->assertTrue($response->hasCookie('foo'));
        $this->assertTrue($response->hasCookie('foo', 'bar'));

        // send it
        ob_start();
        $response->send();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // and what actually got sent?; test both ways
        $this->assertHeaderEmitted('Content-Security-Policy:');
    }

    /**
     * Make sure cookies are set by RedirectResponse this way
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1393
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testRedirectResponseCookies(): void
    {
        $loginTime = time();

        $response = new Response(new App());
        $response->pretend(false);

        $routes = service('routes');
        $routes->add('user/login', 'Auth::verify', ['as' => 'login']);

        $answer1 = $response->redirect('/login')
            ->setCookie('foo', 'bar', YEAR)
            ->setCookie('login_time', (string) $loginTime, YEAR);

        $this->assertTrue($answer1->hasCookie('foo', 'bar'));
        $this->assertTrue($answer1->hasCookie('login_time'));

        $response->setBody('Hello');

        // send it
        ob_start();
        $response->send();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // and what actually got sent?
        $this->assertHeaderEmitted('Set-Cookie: foo=bar;');
        $this->assertHeaderEmitted('Set-Cookie: login_time');
    }

    /**
     * Make sure secure cookies are not sent with HTTP request
     */
    public function testDoNotSendUnSecureCookie(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Attempted to send a secure cookie over a non-secure connection.');

        $request = $this->createMock(IncomingRequest::class);
        $request->method('isSecure')->willReturn(false);
        Services::injectMock('request', $request);

        $response = new Response(new App());
        $response->pretend(false);
        $body = 'Hello';
        $response->setBody($body);

        $response->setCookie(
            'foo',
            'bar',
            '',
            '',
            '/',
            '',
            true,
        );

        // send it
        $response->send();
    }

    /**
     * Make sure that the headers set by the header() function
     * are overridden by the headers defined in the Response class.
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testHeaderOverride(): void
    {
        $response = new Response(new App());
        $response->pretend(false);

        $body = 'Hello';
        $response->setBody($body);

        // single header
        $response->setHeader('Vary', 'Accept-Encoding');
        $this->assertSame('Accept-Encoding', $response->header('Vary')->getValue());

        // multiple headers
        $response->setHeader('Access-Control-Expose-Headers', 'X-Custom-Header');
        $response->addHeader('Access-Control-Expose-Headers', 'Content-Length');
        $header = $response->header('Access-Control-Expose-Headers');
        $this->assertIsArray($header);
        $this->assertSame('X-Custom-Header', $header[0]->getValue());
        $this->assertSame('Content-Length', $header[1]->getValue());

        // send it
        ob_start();
        header('Vary: User-Agent');
        header('Access-Control-Expose-Headers: Content-Encoding');
        header('Allow: GET, POST');
        $response->send();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // single header
        $this->assertHeaderEmitted('Vary: Accept-Encoding');
        $this->assertHeaderNotEmitted('Vary: User-Agent');

        // multiple headers
        $this->assertHeaderEmitted('Access-Control-Expose-Headers: X-Custom-Header');
        $this->assertHeaderEmitted('Access-Control-Expose-Headers: Content-Length');
        $this->assertHeaderNotEmitted('Access-Control-Expose-Headers: Content-Encoding');

        // not overridden by the response class
        $this->assertHeaderEmitted('Allow: GET, POST');
    }
}
