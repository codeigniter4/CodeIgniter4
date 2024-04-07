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

namespace CodeIgniter\Test;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * Consolidated response processing
 * for test results.
 *
 * @mixin DOMParser
 *
 * @see \CodeIgniter\Test\TestResponseTest
 */
class TestResponse
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

        $body = (string) $this->response->getBody();

        // Empty bodies are not considered valid, unless in redirects
        return ! ($status < 300 && $body === '');
    }

    /**
     * Asserts that the status is a specific value.
     */
    public function assertStatus(int $code): void
    {
        Assert::assertSame($code, $this->response->getStatusCode());
    }

    /**
     * Asserts that the Response is considered OK.
     */
    public function assertOK(): void
    {
        Assert::assertTrue(
            $this->isOK(),
            "{$this->response->getStatusCode()} is not a successful status code, or Response has an empty body."
        );
    }

    /**
     * Asserts that the Response is considered not OK.
     */
    public function assertNotOK(): void
    {
        Assert::assertFalse(
            $this->isOK(),
            "{$this->response->getStatusCode()} is an unexpected successful status code, or Response body has content."
        );
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
     */
    public function assertRedirect(): void
    {
        Assert::assertTrue($this->isRedirect(), 'Response is not a redirect or instance of RedirectResponse.');
    }

    /**
     * Assert that a given response was a redirect
     * and it was redirect to a specific URI.
     */
    public function assertRedirectTo(string $uri): void
    {
        $this->assertRedirect();

        $uri         = trim(strtolower($uri));
        $redirectUri = strtolower($this->getRedirectUrl());

        $matches = $uri === $redirectUri
            || strtolower(site_url($uri)) === $redirectUri
            || $uri === site_url($redirectUri);

        Assert::assertTrue($matches, "Redirect URL '{$uri}' does not match '{$redirectUri}'.");
    }

    /**
     * Assert that the given response was not a redirect.
     */
    public function assertNotRedirect(): void
    {
        Assert::assertFalse($this->isRedirect(), 'Response is an unexpected redirect or instance of RedirectResponse.');
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
     * Asserts that an SESSION key has been set and, optionally, test its value.
     *
     * @param mixed $value
     */
    public function assertSessionHas(string $key, $value = null): void
    {
        Assert::assertArrayHasKey($key, $_SESSION, "Key '{$key}' is not in the current \$_SESSION");

        if ($value === null) {
            return;
        }

        if (is_scalar($value)) {
            Assert::assertSame($value, $_SESSION[$key], "The value of key '{$key}' ({$value}) does not match expected value.");

            return;
        }

        Assert::assertSame($value, $_SESSION[$key], "The value of key '{$key}' does not match expected value.");
    }

    /**
     * Asserts the session is missing $key.
     */
    public function assertSessionMissing(string $key): void
    {
        Assert::assertArrayNotHasKey($key, $_SESSION, "Key '{$key}' should not be present in \$_SESSION.");
    }

    // --------------------------------------------------------------------
    // Headers
    // --------------------------------------------------------------------

    /**
     * Asserts that the Response contains a specific header.
     *
     * @param string|null $value
     */
    public function assertHeader(string $key, $value = null): void
    {
        Assert::assertTrue($this->response->hasHeader($key), "Header '{$key}' is not a valid Response header.");

        if ($value !== null) {
            Assert::assertSame(
                $value,
                $this->response->getHeaderLine($key),
                "The value of '{$key}' header ({$this->response->getHeaderLine($key)}) does not match expected value."
            );
        }
    }

    /**
     * Asserts the Response headers does not contain the specified header.
     */
    public function assertHeaderMissing(string $key): void
    {
        Assert::assertFalse($this->response->hasHeader($key), "Header '{$key}' should not be in the Response headers.");
    }

    // --------------------------------------------------------------------
    // Cookies
    // --------------------------------------------------------------------

    /**
     * Asserts that the response has the specified cookie.
     *
     * @param string|null $value
     */
    public function assertCookie(string $key, $value = null, string $prefix = ''): void
    {
        Assert::assertTrue($this->response->hasCookie($key, $value, $prefix), "Cookie named '{$key}' is not found.");
    }

    /**
     * Assert the Response does not have the specified cookie set.
     */
    public function assertCookieMissing(string $key): void
    {
        Assert::assertFalse($this->response->hasCookie($key), "Cookie named '{$key}' should not be set.");
    }

    /**
     * Asserts that a cookie exists and has an expired time.
     */
    public function assertCookieExpired(string $key, string $prefix = ''): void
    {
        Assert::assertTrue($this->response->hasCookie($key, null, $prefix));

        Assert::assertGreaterThan(
            Time::now()->getTimestamp(),
            $this->response->getCookie($key, $prefix)->getExpiresTimestamp()
        );
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
     */
    public function assertJSONFragment(array $fragment, bool $strict = false): void
    {
        $json = json_decode($this->getJSON(), true);
        Assert::assertIsArray($json, 'Response is not a valid JSON.');

        $patched = array_replace_recursive($json, $fragment);

        if ($strict) {
            Assert::assertSame($json, $patched, 'Response does not contain a matching JSON fragment.');

            return;
        }

        Assert::assertThat($patched, new IsEqual($json), 'Response does not contain a matching JSON fragment.');
    }

    /**
     * Asserts that the JSON exactly matches the passed in data.
     * If the value being passed in is a string, it must be a json_encoded string.
     *
     * @param array|object|string $test
     */
    public function assertJSONExact($test): void
    {
        $json = $this->getJSON();

        if (is_object($test)) {
            $test = method_exists($test, 'toArray') ? $test->toArray() : (array) $test;
        }

        if (is_array($test)) {
            $test = service('format')->getFormatter('application/json')->format($test);
        }

        Assert::assertJsonStringEqualsJsonString($test, $json, 'Response does not contain matching JSON.');
    }

    // --------------------------------------------------------------------
    // XML Methods
    // --------------------------------------------------------------------

    /**
     * Returns the response' body as XML
     *
     * @return bool|string|null
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
     */
    public function assertSee(?string $search = null, ?string $element = null): void
    {
        Assert::assertTrue(
            $this->domParser->see($search, $element),
            "Text '{$search}' is not seen in response."
        );
    }

    /**
     * Asserts that we do not see the specified text.
     */
    public function assertDontSee(?string $search = null, ?string $element = null): void
    {
        Assert::assertTrue(
            $this->domParser->dontSee($search, $element),
            "Text '{$search}' is unexpectedly seen in response."
        );
    }

    /**
     * Assert that we see an element selected via a CSS selector.
     */
    public function assertSeeElement(string $search): void
    {
        Assert::assertTrue(
            $this->domParser->seeElement($search),
            "Element with selector '{$search}' is not seen in response."
        );
    }

    /**
     * Assert that we do not see an element selected via a CSS selector.
     */
    public function assertDontSeeElement(string $search): void
    {
        Assert::assertTrue(
            $this->domParser->dontSeeElement($search),
            "Element with selector '{$search}' is unexpectedly seen in response.'"
        );
    }

    /**
     * Assert that we see a link with the matching text and/or class.
     */
    public function assertSeeLink(string $text, ?string $details = null): void
    {
        Assert::assertTrue(
            $this->domParser->seeLink($text, $details),
            "Anchor tag with text '{$text}' is not seen in response."
        );
    }

    /**
     * Assert that we see an input with name/value.
     */
    public function assertSeeInField(string $field, ?string $value = null): void
    {
        Assert::assertTrue(
            $this->domParser->seeInField($field, $value),
            "Input named '{$field}' with value '{$value}' is not seen in response."
        );
    }

    /**
     * Forward any unrecognized method calls to our DOMParser instance.
     *
     * @param list<mixed> $params
     */
    public function __call(string $function, array $params): mixed
    {
        if (method_exists($this->domParser, $function)) {
            return $this->domParser->{$function}(...$params);
        }

        return null;
    }
}
