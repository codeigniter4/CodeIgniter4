<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * This test suite has been created separately from
 * TestCaseTest because it messes with output
 * buffering from PHPUnit, and the individual
 * test cases need to be run as separate processes.
 *
 * @internal
 */
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testHeadersMissingDate()
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

    //--------------------------------------------------------------------

    /**
     * This test does not test that CSP is handled properly -
     * it makes sure that sending gives CSP a chance to do its thing.
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testHeadersWithCSP()
    {
        $config             = new App();
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

    //--------------------------------------------------------------------

    /**
     * Make sure cookies are set by RedirectResponse this way
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1393
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testRedirectResponseCookies()
    {
        $loginTime = time();

        $response = new Response(new App());
        $response->pretend(false);

        $routes = service('routes');
        $routes->add('user/login', 'Auth::verify', ['as' => 'login']);

        $answer1 = $response->redirect('/login')
            ->setCookie('foo', 'bar', YEAR)
            ->setCookie('login_time', $loginTime, YEAR);
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
}
