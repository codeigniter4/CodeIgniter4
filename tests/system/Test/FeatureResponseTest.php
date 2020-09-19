<?php

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureResponse;
use Config\App;
use Config\Services;

class FeatureResponseTest extends CIUnitTestCase
{
	/**
	 * @var FeatureResponse
	 */
	protected $feature;

	/**
	 * @var Response
	 */
	protected $response;

	protected function setUp(): void
	{
		parent::setUp();
	}

	public function testIsOKFailsSmall()
	{
		$this->getFeatureResponse('Hello World');
		$this->response->setStatusCode(100);

		$this->assertFalse($this->feature->isOK());
	}

	public function testIsOKFailsLarge()
	{
		$this->getFeatureResponse('Hello World');
		$this->response->setStatusCode(400);

		$this->assertFalse($this->feature->isOK());
	}

	public function testIsOKSuccess()
	{
		$this->getFeatureResponse('Hello World');
		$this->response->setStatusCode(200);

		$this->assertTrue($this->feature->isOK());
	}

	public function testIsOKEmpty()
	{
		$this->getFeatureResponse('Hi there');
		$this->response->setStatusCode(200);
		$this->response->setBody('');

		$this->assertFalse($this->feature->isOK());
	}

	public function testAssertSee()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->feature->assertSee('Hello');
		$this->feature->assertSee('World', 'h1');
	}

	public function testAssertDontSee()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->feature->assertDontSee('Worlds');
		$this->feature->assertDontSee('World', 'h2');
	}

	public function testAssertSeeElement()
	{
		$this->getFeatureResponse('<h1 class="header">Hello <span>World</span></h1>');

		$this->feature->assertSeeElement('h1');
		$this->feature->assertSeeElement('span');
		$this->feature->assertSeeElement('h1.header');
	}

	public function testAssertDontSeeElement()
	{
		$this->getFeatureResponse('<h1 class="header">Hello <span>World</span></h1>');

		$this->feature->assertDontSeeElement('h2');
		$this->feature->assertDontSeeElement('.span');
		$this->feature->assertDontSeeElement('h1.para');
	}

	public function testAssertSeeLink()
	{
		$this->getFeatureResponse('<h1 class="header"><a href="http://example.com/hello">Hello</a> <span>World</span></h1>');

		$this->feature->assertSeeElement('h1');
		$this->feature->assertSeeLink('Hello');
	}

	public function testAssertSeeInField()
	{
		$this->getFeatureResponse('<html><body><input type="text" name="user[name]" value="Foobar"></body></html>');

		$this->feature->assertSeeInField('user[name]', 'Foobar');
	}

	public function testAssertRedirectFail()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->assertFalse($this->feature->response instanceof RedirectResponse);
		$this->assertFalse($this->feature->isRedirect());
	}

	public function testAssertRedirectSuccess()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');
		$this->feature->response = new RedirectResponse(new App());

		$this->assertTrue($this->feature->response instanceof RedirectResponse);
		$this->assertTrue($this->feature->isRedirect());
		$this->feature->assertRedirect();
	}

	public function testAssertRedirectSuccessWithoutRedirectResponse()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');
		$this->response->redirect('foo/bar');

		$this->assertFalse($this->feature->response instanceof RedirectResponse);
		$this->assertTrue($this->feature->isRedirect());
		$this->feature->assertRedirect();
		$this->assertEquals('foo/bar', $this->feature->getRedirectUrl());
	}

	public function testGetRedirectUrlReturnsUrl()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');
		$this->feature->response = new RedirectResponse(new App());
		$this->feature->response->redirect('foo/bar');

		$this->assertEquals('foo/bar', $this->feature->getRedirectUrl());
	}

	public function testGetRedirectUrlReturnsNull()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->assertNull($this->feature->getRedirectUrl());
	}

	public function testAssertStatus()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>', ['statusCode' => 201]);

		$this->feature->assertStatus(201);
	}

	public function testAssertIsOK()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>', ['statusCode' => 201]);
		$this->feature->assertOK();

		$this->getFeatureResponse('<h1>Hello World</h1>', ['statusCode' => 301]);
		$this->feature->assertOK();

		$this->getFeatureResponse('<h1>Hello World</h1>', ['statusCode' => 401]);
		$this->assertFalse($this->feature->isOK());
	}

	public function testAssertSessionHas()
	{
		$_SESSION['foo'] = 'bar';
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->feature->assertSessionHas('foo');
		$this->feature->assertSessionHas('foo', 'bar');
	}

	public function testAssertSessionMissing()
	{
		$_SESSION = [];
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->feature->assertSessionMissing('foo');
	}

	public function testAssertHeader()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>', [], ['foo' => 'bar']);

		$this->feature->assertHeader('foo');
		$this->feature->assertHeader('foo', 'bar');
	}

	public function testAssertHeaderMissing()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>', [], ['foo' => 'bar']);

		$this->feature->assertHeader('foo');
		$this->feature->assertHeaderMissing('banana');
	}

	public function testAssertCookie()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->response = $this->response->setCookie('foo', 'bar');

		$this->feature->assertCookie('foo');
		$this->feature->assertCookie('foo', 'bar');
	}

	public function testAssertCookieMissing()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->response = $this->response->setCookie('foo', 'bar');

		$this->feature->assertCookieMissing('bar');
	}

	public function testAssertCookieExpired()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');

		$this->response = $this->response->setCookie('foo', 'bar', strtotime('-1 day'));

		$this->feature->assertCookieExpired('foo');
	}

	public function testGetJSON()
	{
		$this->getFeatureResponse(['foo' => 'bar']);
		$formatter = Services::format()->getFormatter('application/json');

		$this->assertEquals($formatter->format(['foo' => 'bar']), $this->feature->getJSON());
	}

	public function testEmptyJSON()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');
		$this->response->setJSON('', true);

		// this should be "" - json_encode('');
		$this->assertEquals('""', $this->feature->getJSON());
	}

	public function testFalseJSON()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');
		$this->response->setJSON(false, true);

		// this should be FALSE - json_encode(false)
		$this->assertEquals('false', $this->feature->getJSON());
	}

	public function testTrueJSON()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');
		$this->response->setJSON(true, true);

		// this should be TRUE - json_encode(true)
		$this->assertEquals('true', $this->feature->getJSON());
	}

	public function testInvalidJSON()
	{
		$tmp = ' test " case ';
		$this->getFeatureResponse('<h1>Hello World</h1>');
		$this->response->setBody($tmp);

		// this should be FALSE - invalid JSON - will see if this is working that way ;-)
		$this->assertFalse($this->response->getBody() === $this->feature->getJSON());
	}

	public function testGetXML()
	{
		$this->getFeatureResponse(['foo' => 'bar']);
		$formatter = Services::format()->getFormatter('application/xml');

		$this->assertEquals($formatter->format(['foo' => 'bar']), $this->feature->getXML());
	}

	public function testJsonFragment()
	{
		$this->getFeatureResponse([
			'config' => [
				'key-a',
				'key-b',
			],
		]);

		$this->feature->assertJSONFragment(['config' => ['key-a']]);
		$this->feature->assertJSONFragment(['config' => ['key-a']], true);
	}

	public function testAssertJSONFragmentFollowingAssertArraySubset()
	{
		$this->getFeatureResponse([
			'config' => '124',
		]);

		$this->feature->assertJSONFragment(['config' => 124]); // must fail on strict
		$this->feature->assertJSONFragment(['config' => '124'], true);
	}

	public function testJsonExact()
	{
		$data = [
			'config' => [
				'key-a',
				'key-b',
			],
		];

		$this->getFeatureResponse($data);

		$this->feature->assertJSONExact($data);
	}

	public function testJsonExactString()
	{
		$data = [
			'config' => [
				'key-a',
				'key-b',
			],
		];

		$this->getFeatureResponse($data);
		$formatter = Services::format()->getFormatter('application/json');

		$this->feature->assertJSONExact($formatter->format($data));
	}

	protected function getFeatureResponse($body = null, array $responseOptions = [], array $headers = [])
	{
		$this->response = new Response(new App());
		$this->response->setBody($body);

		foreach ($responseOptions as $key => $value)
		{
			$method = 'set' . ucfirst($key);

			if (method_exists($this->response, $method))
			{
				$this->response = $this->response->$method($value);
			}
		}

		foreach ($headers as $key => $value)
		{
			$this->response = $this->response->setHeader($key, $value);
		}

		$this->feature = new FeatureResponse($this->response);
	}
}
