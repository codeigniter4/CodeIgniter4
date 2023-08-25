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
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Config\Services;
use Exception;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\TestCase;

/**
 * Test Response Class
 *
 * Consolidated response processing
 * for test results.
 *
 * @no-final
 *
 * @internal
 *
 * @mixin DOMParser
 */
class TestResponse extends TestCase
{
    /**
     * The request.
     *
     * @var RequestInterface|null
     */
    protected $request;

    /**
     * The response.
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * DOM for the body.
     *
     * @var DOMParser
     */
    protected $domParser;

    /**
     * Stores or the Response and parses the body in the DOM.
     */
    public function __construct(ResponseInterface $response)
    {
        $this->setResponse($response);
    }

    // --------------------------------------------------------------------
    // Getters / Setters
    // --------------------------------------------------------------------

    /**
     * Sets the request.
     *
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Sets the Response and updates the DOM.
     *
     * @return $this
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response  = $response;
        $this->domParser = new DOMParser();

        $body = $response->getBody();
        if (is_string($body) && $body !== '') {
            $this->domParser->withString($body);
        }

        return $this;
    }

    /**
     * Request accessor.
     *
     * @return RequestInterface|null
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Response accessor.
     *
     * @return ResponseInterface
     */
    public function response()
    {
        return $this->response;
    }

    // --------------------------------------------------------------------
    // Status Checks
    // --------------------------------------------------------------------

    /**
     * Boils down the possible responses into a boolean valid/not-valid
     * response type.
     */
    public function isOK(): bool
    {
        $status = $this->response->getStatusCode();

        // Only 200 and 300 range status codes
        // are considered valid.
        if ($status >= 400 || $status < 200) {
            return false;
        }

        // Empty bodies are not considered valid, unless in redirects
        return ! ($status < 300 && empty($this->response->getBody()));
    }

    /**
     * Asserts that the status is a specific value.
     *
     * @throws Exception
     */
    public function assertStatus(int $code)
    {
        $this->assertSame($code, $this->response->getStatusCode());
    }

    /**
     * Asserts that the Response is considered OK.
     *
     * @throws Exception
     */
    public function assertOK()
    {
        $this->assertTrue($this->isOK(), "{$this->response->getStatusCode()} is not a successful status code, or the Response has an empty body.");
    }

    /**
     * Asserts that the Response is considered OK.
     *
     * @throws Exception
     */
    public function assertNotOK()
    {
        $this->assertFalse($this->isOK(), "{$this->response->getStatusCode()} is an unexpected successful status code, or the Response has body content.");
    }

    // --------------------------------------------------------------------
    // Redirection
    // --------------------------------------------------------------------

    /**
     * Returns whether or not the Response was a redirect or RedirectResponse
     */
    public function isRedirect(): bool
    {
        return $this->response instanceof RedirectResponse
            || $this->response->hasHeader('Location')
            || $this->response->hasHeader('Refresh');
    }

    /**
     * Assert that the given response was a redirect.
     *
     * @throws Exception
     */
    public function assertRedirect()
    {
        $this->assertTrue($this->isRedirect(), 'Response is not a redirect or RedirectResponse.');
    }

    /**
     * Assert that a given response was a redirect
     * and it was redirect to a specific URI.
     *
     * @throws Exception
     */
    public function assertRedirectTo(string $uri)
    {
        $this->assertRedirect();

        $uri         = trim(strtolower($uri));
        $redirectUri = strtolower($this->getRedirectUrl());

        $matches = $uri === $redirectUri
                   || strtolower(site_url($uri)) === $redirectUri
                   || $uri === site_url($redirectUri);

        $this->assertTrue($matches, "Redirect URL `{$uri}` does not match `{$redirectUri}`");
    }

    /**
     * Assert that the given response was not a redirect.
     *
     * @throws Exception
     */
    public function assertNotRedirect()
    {
        $this->assertFalse($this->isRedirect(), 'Response is an unexpected redirect or RedirectResponse.');
    }

    /**
     * Returns the URL set for redirection.
     */
    public function getRedirectUrl(): ?string
    {
        if (! $this->isRedirect()) {
            return null;
        }

        if ($this->response->hasHeader('Location')) {
            return $this->response->getHeaderLine('Location');
        }

        if ($this->response->hasHeader('Refresh')) {
            return str_replace('0;url=', '', $this->response->getHeaderLine('Refresh'));
        }

        return null;
    }

    // --------------------------------------------------------------------
    // Session
    // --------------------------------------------------------------------

    /**
     * Asserts that an SESSION key has been set and, optionally, test it's value.
     *
     * @param mixed $value
     *
     * @throws Exception
     */
    public function assertSessionHas(string $key, $value = null)
    {
        $this->assertArrayHasKey($key, $_SESSION, "'{$key}' is not in the current \$_SESSION");

        if ($value === null) {
            return;
        }

        if (is_scalar($value)) {
            $this->assertSame($value, $_SESSION[$key], "The value of '{$key}' ({$value}) does not match expected value.");
        } else {
            $this->assertSame($value, $_SESSION[$key], "The value of '{$key}' does not match expected value.");
        }
    }

    /**
     * Asserts the session is missing $key.
     *
     * @throws Exception
     */
    public function assertSessionMissing(string $key)
    {
        $this->assertArrayNotHasKey($key, $_SESSION, "'{$key}' should not be present in \$_SESSION.");
    }

    // --------------------------------------------------------------------
    // Headers
    // --------------------------------------------------------------------

    /**
     * Asserts that the Response contains a specific header.
     *
     * @param string|null $value
     *
     * @throws Exception
     */
    public function assertHeader(string $key, $value = null)
    {
        $this->assertTrue($this->response->hasHeader($key), "'{$key}' is not a valid Response header.");

        if ($value !== null) {
            $this->assertSame($value, $this->response->getHeaderLine($key), "The value of '{$key}' header ({$this->response->getHeaderLine($key)}) does not match expected value.");
        }
    }

    /**
     * Asserts the Response headers does not contain the specified header.
     *
     * @throws Exception
     */
    public function assertHeaderMissing(string $key)
    {
        $this->assertFalse($this->response->hasHeader($key), "'{$key}' should not be in the Response headers.");
    }

    // --------------------------------------------------------------------
    // Cookies
    // --------------------------------------------------------------------

    /**
     * Asserts that the response has the specified cookie.
     *
     * @param string|null $value
     *
     * @throws Exception
     */
    public function assertCookie(string $key, $value = null, string $prefix = '')
    {
        $this->assertTrue($this->response->hasCookie($key, $value, $prefix), "No cookie found named '{$key}'.");
    }

    /**
     * Assert the Response does not have the specified cookie set.
     */
    public function assertCookieMissing(string $key)
    {
        $this->assertFalse($this->response->hasCookie($key), "Cookie named '{$key}' should not be set.");
    }

    /**
     * Asserts that a cookie exists and has an expired time.
     *
     * @throws Exception
     */
    public function assertCookieExpired(string $key, string $prefix = '')
    {
        $this->assertTrue($this->response->hasCookie($key, null, $prefix));
        $this->assertGreaterThan(Time::now()->getTimestamp(), $this->response->getCookie($key, $prefix)->getExpiresTimestamp());
    }

    // --------------------------------------------------------------------
    // JSON
    // --------------------------------------------------------------------

    /**
     * Returns the response's body as JSON
     *
     * @return false|string
     */
    public function getJSON()
    {
        $response = $this->response->getJSON();

        if ($response === null) {
            return false;
        }

        return $response;
    }

    /**
     * Test that the response contains a matching JSON fragment.
     *
     * @throws Exception
     */
    public function assertJSONFragment(array $fragment, bool $strict = false)
    {
        $json = json_decode($this->getJSON(), true);
        $this->assertIsArray($json, 'Response does not have valid json');
        $patched = array_replace_recursive($json, $fragment);

        if ($strict) {
            $this->assertSame($json, $patched, 'Response does not contain a matching JSON fragment.');
        } else {
            $this->assertThat($patched, new IsEqual($json), 'Response does not contain a matching JSON fragment.');
        }
    }

    /**
     * Asserts that the JSON exactly matches the passed in data.
     * If the value being passed in is a string, it must be a json_encoded string.
     *
     * @param array|object|string $test
     *
     * @throws Exception
     */
    public function assertJSONExact($test)
    {
        $json = $this->getJSON();

        if (is_object($test)) {
            $test = method_exists($test, 'toArray') ? $test->toArray() : (array) $test;
        }

        if (is_array($test)) {
            $test = Services::format()->getFormatter('application/json')->format($test);
        }

        $this->assertJsonStringEqualsJsonString($test, $json, 'Response does not contain matching JSON.');
    }

    // --------------------------------------------------------------------
    // XML Methods
    // --------------------------------------------------------------------

    /**
     * Returns the response' body as XML
     *
     * @return mixed|string
     */
    public function getXML()
    {
        return $this->response->getXML();
    }

    // --------------------------------------------------------------------
    // DomParser
    // --------------------------------------------------------------------

    /**
     * Assert that the desired text can be found in the result body.
     *
     * @throws Exception
     */
    public function assertSee(?string $search = null, ?string $element = null)
    {
        $this->assertTrue($this->domParser->see($search, $element), "Do not see '{$search}' in response.");
    }

    /**
     * Asserts that we do not see the specified text.
     *
     * @throws Exception
     */
    public function assertDontSee(?string $search = null, ?string $element = null)
    {
        $this->assertTrue($this->domParser->dontSee($search, $element), "I should not see '{$search}' in response.");
    }

    /**
     * Assert that we see an element selected via a CSS selector.
     *
     * @throws Exception
     */
    public function assertSeeElement(string $search)
    {
        $this->assertTrue($this->domParser->seeElement($search), "Do not see element with selector '{$search} in response.'");
    }

    /**
     * Assert that we do not see an element selected via a CSS selector.
     *
     * @throws Exception
     */
    public function assertDontSeeElement(string $search)
    {
        $this->assertTrue($this->domParser->dontSeeElement($search), "I should not see an element with selector '{$search}' in response.'");
    }

    /**
     * Assert that we see a link with the matching text and/or class.
     *
     * @throws Exception
     */
    public function assertSeeLink(string $text, ?string $details = null)
    {
        $this->assertTrue($this->domParser->seeLink($text, $details), "Do no see anchor tag with the text {$text} in response.");
    }

    /**
     * Assert that we see an input with name/value.
     *
     * @throws Exception
     */
    public function assertSeeInField(string $field, ?string $value = null)
    {
        $this->assertTrue($this->domParser->seeInField($field, $value), "Do no see input named {$field} with value {$value} in response.");
    }

    /**
     * Forward any unrecognized method calls to our DOMParser instance.
     *
     * @param string $function Method name
     * @param mixed  $params   Any method parameters
     *
     * @return mixed
     */
    public function __call($function, $params)
    {
        if (method_exists($this->domParser, $function)) {
            return $this->domParser->{$function}(...$params);
        }
    }
}
