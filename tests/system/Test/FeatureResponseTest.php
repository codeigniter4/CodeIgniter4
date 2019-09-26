<?php

use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureResponse;
use CodeIgniter\HTTP\RedirectResponse;

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
		$this->feature->response = new RedirectResponse(new Config\App());

		$this->assertTrue($this->feature->response instanceof RedirectResponse);
		$this->assertTrue($this->feature->isRedirect());
		$this->feature->assertRedirect();
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
		$config    = new \Config\Format();
		$formatter = $config->getFormatter('application/json');

		$this->assertEquals($formatter->format(['foo' => 'bar']), $this->feature->getJSON());
	}

	public function testInvalidJSON()
	{
		$this->getFeatureResponse('<h1>Hello World</h1>');
		$this->response->setJSON('');
		$config    = new \Config\Format();
		$formatter = $config->getFormatter('application/json');

		// this should fail because of empty JSON
		$this->assertFalse($this->feature->getJSON());
	}

	public function testGetXML()
	{
		$this->getFeatureResponse(['foo' => 'bar']);
		$config    = new \Config\Format();
		$formatter = $config->getFormatter('application/xml');

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

		$config    = new \Config\Format();
		$formatter = $config->getFormatter('application/json');
		$expected  = $formatter->format($data);

		$this->feature->assertJSONExact($expected);
	}

	protected function getFeatureResponse($body = null, array $responseOptions = [], array $headers = [])
	{
		$this->response = new Response(new \Config\App());
		$this->response->setBody($body);

		if (count($responseOptions))
		{
			foreach ($responseOptions as $key => $value)
			{
				$method = 'set' . ucfirst($key);

				if (method_exists($this->response, $method))
				{
					$this->response = $this->response->$method($value);
				}
			}
		}

		if (count($headers))
		{
			foreach ($headers as $key => $value)
			{
				$this->response = $this->response->setHeader($key, $value);
			}
		}

		$this->feature = new FeatureResponse($this->response);
	}

}
