<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Test;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use Config\Format;
use PHPUnit\Framework\TestCase;

/**
 * Assertions for a response
 */
class FeatureResponse extends TestCase
{

	/**
	 * The response.
	 *
	 * @var \CodeIgniter\HTTP\Response
	 */
	public $response;

	/**
	 * DOM for the body.
	 *
	 * @var \CodeIgniter\Test\DOMParser
	 */
	protected $domParser;

	/**
	 * Constructor.
	 *
	 * @param Response $response
	 */
	public function __construct(Response $response = null)
	{
		$this->response = $response;

		$body = $response->getBody();
		if (! empty($body) && is_string($body))
		{
			$this->domParser = (new DOMParser())->withString($body);
		}
	}

	//--------------------------------------------------------------------
	// Simple Response Checks
	//--------------------------------------------------------------------

	/**
	 * Boils down the possible responses into a bolean valid/not-valid
	 * response type.
	 *
	 * @return boolean
	 */
	public function isOK(): bool
	{
		// Only 200 and 300 range status codes
		// are considered valid.
		if ($this->response->getStatusCode() >= 400 || $this->response->getStatusCode() < 200)
		{
			return false;
		}

		// Empty bodies are not considered valid.
		if (empty($this->response->getBody()))
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns whether or not the Response was a redirect response
	 *
	 * @return boolean
	 */
	public function isRedirect(): bool
	{
		return $this->response instanceof RedirectResponse;
	}

	/**
	 * Assert that the given response was a redirect.
	 *
	 * @throws \Exception
	 */
	public function assertRedirect()
	{
		$this->assertTrue($this->isRedirect(), 'Response is not a RedirectResponse.');
	}

	/**
	 * Asserts that the status is a specific value.
	 *
	 * @param integer $code
	 *
	 * @throws \Exception
	 */
	public function assertStatus(int $code)
	{
		$this->assertEquals($code, (int) $this->response->getStatusCode());
	}

	/**
	 * Asserts that the Response is considered OK.
	 *
	 * @throws \Exception
	 */
	public function assertOK()
	{
		$this->assertTrue($this->isOK(), "{$this->response->getStatusCode()} is not a successful status code, or the Response has an empty body.");
	}

	//--------------------------------------------------------------------
	// Session Assertions
	//--------------------------------------------------------------------

	/**
	 * Asserts that an SESSION key has been set and, optionally, test it's value.
	 *
	 * @param string $key
	 * @param null   $value
	 *
	 * @throws \Exception
	 */
	public function assertSessionHas(string $key, $value = null)
	{
		$this->assertTrue(array_key_exists($key, $_SESSION), "'{$key}' is not in the current \$_SESSION");

		if ($value !== null)
		{
			$this->assertEquals($value, $_SESSION[$key], "The value of '{$key}' ({$value}) does not match expected value.");
		}
	}

	/**
	 * Asserts the session is missing $key.
	 *
	 * @param string $key
	 *
	 * @throws \Exception
	 */
	public function assertSessionMissing(string $key)
	{
		$this->assertFalse(array_key_exists($key, $_SESSION), "'{$key}' should not be present in \$_SESSION.");
	}

	//--------------------------------------------------------------------
	// Header Assertions
	//--------------------------------------------------------------------

	/**
	 * Asserts that the Response contains a specific header.
	 *
	 * @param string $key
	 * @param null   $value
	 *
	 * @throws \Exception
	 */
	public function assertHeader(string $key, $value = null)
	{
		$this->assertTrue($this->response->hasHeader($key), "'{$key}' is not a valid Response header.");

		if ($value !== null)
		{
			$this->assertEquals($value, $this->response->getHeaderLine($key), "The value of '{$key}' header ({$this->response->getHeaderLine($key)}) does not match expected value.");
		}
	}

	/**
	 * Asserts the Response headers does not contain the specified header.
	 *
	 * @param string $key
	 *
	 * @throws \Exception
	 */
	public function assertHeaderMissing(string $key)
	{
		$this->assertFalse($this->response->hasHeader($key), "'{$key}' should not be in the Response headers.");
	}

	//--------------------------------------------------------------------
	// Cookie Assertions
	//--------------------------------------------------------------------

	/**
	 * Asserts that the response has the specified cookie.
	 *
	 * @param string      $key
	 * @param null        $value
	 * @param string|null $prefix
	 *
	 * @throws \Exception
	 */
	public function assertCookie(string $key, $value = null, string $prefix = '')
	{
		$this->assertTrue($this->response->hasCookie($key, $value, $prefix), "No cookie found named '{$key}'.");
	}

	/**
	 * Assert the Response does not have the specified cookie set.
	 *
	 * @param string $key
	 */
	public function assertCookieMissing(string $key)
	{
		$this->assertFalse($this->response->hasCookie($key), "Cookie named '{$key}' should not be set.");
	}

	/**
	 * Asserts that a cookie exists and has an expired time.
	 *
	 * @param string $key
	 * @param string $prefix
	 *
	 * @throws \Exception
	 */
	public function assertCookieExpired(string $key, string $prefix = '')
	{
		$this->assertTrue($this->response->hasCookie($key, null, $prefix));
		$this->assertGreaterThan(time(), $this->response->getCookie($key, $prefix)['expires']);
	}

	//--------------------------------------------------------------------
	// DomParser Assertions
	//--------------------------------------------------------------------

	/**
	 * Assert that the desired text can be found in the result body.
	 *
	 * @param string|null $search
	 * @param string|null $element
	 *
	 * @throws \Exception
	 */
	public function assertSee(string $search = null, string $element = null)
	{
		$this->assertTrue($this->domParser->see($search, $element), "Do not see '{$search}' in response.");
	}

	/**
	 * Asserts that we do not see the specified text.
	 *
	 * @param string|null $search
	 * @param string|null $element
	 *
	 * @throws \Exception
	 */
	public function assertDontSee(string $search = null, string $element = null)
	{
		$this->assertTrue($this->domParser->dontSee($search, $element), "I should not see '{$search}' in response.");
	}

	/**
	 * Assert that we see an element selected via a CSS selector.
	 *
	 * @param string $search
	 *
	 * @throws \Exception
	 */
	public function assertSeeElement(string $search)
	{
		$this->assertTrue($this->domParser->seeElement($search), "Do not see element with selector '{$search} in response.'");
	}

	/**
	 * Assert that we do not see an element selected via a CSS selector.
	 *
	 * @param string $search
	 *
	 * @throws \Exception
	 */
	public function assertDontSeeElement(string $search)
	{
		$this->assertTrue($this->domParser->dontSeeElement($search), "I should not see an element with selector '{$search}' in response.'");
	}

	/**
	 * Assert that we see a link with the matching text and/or class.
	 *
	 * @param string      $text
	 * @param string|null $details
	 *
	 * @throws \Exception
	 */
	public function assertSeeLink(string $text, string $details = null)
	{
		$this->assertTrue($this->domParser->seeLink($text, $details), "Do no see anchor tag with the text {$text} in response.");
	}

	/**
	 * Assert that we see an input with name/value.
	 *
	 * @param string      $field
	 * @param string|null $value
	 *
	 * @throws \Exception
	 */
	public function assertSeeInField(string $field, string $value = null)
	{
		$this->assertTrue($this->domParser->seeInField($field, $value), "Do no see input named {$field} with value {$value} in response.");
	}

	//--------------------------------------------------------------------
	// JSON Methods
	//--------------------------------------------------------------------

	/**
	 * Returns the response's body as JSON
	 *
	 * @return mixed|false
	 */
	public function getJSON()
	{
		$response = $this->response->getJSON();

		if (is_null($response))
		{
			return false;
		}

		return $response;
	}

	/**
	 * Test that the response contains a matching JSON fragment.
	 *
	 * @param array $fragment
	 *
	 * @throws \Exception
	 */
	public function assertJSONFragment(array $fragment)
	{
		$json = json_decode($this->getJSON(), true);

		$this->assertArraySubset($fragment, $json, false, 'Response does not contain a matching JSON fragment.');
	}

	/**
	 * Asserts that the JSON exactly matches the passed in data.
	 * If the value being passed in is a string, it must be a json_encoded string.
	 *
	 * @param string|array $test
	 *
	 * @throws \Exception
	 */
	public function assertJSONExact($test)
	{
		$json = $this->getJSON();

		if (is_array($test))
		{
			$config    = new Format();
			$formatter = $config->getFormatter('application/json');
			$test      = $formatter->format($test);
		}

		$this->assertJsonStringEqualsJsonString($test, $json, 'Response does not contain matching JSON.');
	}

	//--------------------------------------------------------------------
	// XML Methods
	//--------------------------------------------------------------------

	/**
	 * Returns the response' body as XML
	 *
	 * @return mixed|string
	 */
	public function getXML()
	{
		return $this->response->getXML();
	}

}
