<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use InvalidArgumentException;

final class URLTest extends CIUnitTestCase
{
	/**
	 * @var App
	 */
	private $config;

	/**
	 * Sets a common base configuration.
	 * Overriden by individual tests.
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		$_SERVER['REQUEST_URI'] = '/';

		$this->config                            = new App();
		$this->config->baseURL                   = 'http://example.com/';
		$this->config->indexPage                 = 'index.php';
		$this->config->forceGlobalSecureRequests = false;

		Factories::injectMock('config', 'App', $this->config);
	}

	//--------------------------------------------------------------------

	/**
	 * Updates a single Config value.
	 *
	 * @param string $uri
	 *
	 * @return $this
	 */
	private function setConfig(string $key, string $value): self
	{
		$this->config->$key = $value;

		return $this;
	}

	/**
	 * Fakes the current URL and re-initializes the Request.
	 *
	 * @param string $uri
	 *
	 * @return $this
	 */
	private function setCurrent(string $uri): self
	{
		$parts = parse_url($uri);
		if ($parts === false)
		{
			throw InvalidArgumentException('Could not parse URI: ' . $uri);
		}

		$_SERVER['REQUEST_URI'] = $parts['path'] ?? '';

		if (! empty($parts['host']))
		{
			$_SERVER['HTTP_HOST'] = $parts['host'];
		}
		if (! empty($parts['query']))
		{
			$_SERVER['REQUEST_URI'] .= '?' . $parts['query'];
		}
		if (isset($parts['scheme']))
		{
			$_SERVER['HTTPS'] = strtolower(rtrim($parts['scheme'], ':/')) === 'https' ? 'on' : 'off';
		}

		// Recreate the Incoming Request to force detection
		$request = Services::request($this->config, false);
		Services::injectMock('request', $request);

		return $this;
	}

	//--------------------------------------------------------------------

	public function testDefault()
	{
		$url = new URL();

		$result = $this->getPrivateProperty($url, 'relativePath');
		$this->assertSame('', $result);

		$result = $this->getPrivateProperty($url, 'uri');
		$this->assertInstanceOf(URI::class, $result);
	}

	public function testGetPath()
	{
		$result = (new URL('banana'))->getPath();

		$this->assertSame('banana', $result);
	}

	public function testGetUri()
	{
		$url    = new URL('banana');
		$result = $url->getUri();

		$this->assertInstanceOf(URI::class, $result);
		$this->assertSame((string) $url, (string) $result);
	}

	public function testFullUri()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('URL class only accepts relative paths.');

		$url = new URL('http://example.com/bam');
	}

	public function testInvalidBaseURL()
	{
		$this->config->baseURL = '';

		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('URL class requires a valid baseURL.');

		$url = new URL('banana', $this->config);
	}

	/**
	 * This test mostly replicates URI::testRemoveDotSegments()
	 * but it demonstrates how input is cleaned up.
	 *
	 * @dataProvider pathProvider
	 */
	public function testConstructorPath(string $path, string $expected)
	{
		$result = (new URL($path))->getPath();

		$this->assertSame($expected, $result);
	}

	/**
	 * @dataProvider configProvider
	 */
	public function testConstructorConfig(array $configs, string $baseURL)
	{
		foreach ($configs as $key => $value)
		{
			$this->setConfig($key, $value);
		}

		$url      = new URL('testaroo', $this->config);
		$expected = rtrim($baseURL, '/') . '/testaroo';

		$this->assertSame($expected, (string) $url);
	}

	/**
	 * @dataProvider currentProvider
	 */
	public function testCurrent(string $uri, string $expected = null)
	{
		$this->setCurrent($uri);

		$url = URL::current();

		$this->assertInstanceOf(URL::class, $url);
		$this->assertSame($expected ?? $uri, (string) $url);
	}

	/**
	 * @dataProvider configProvider
	 */
	public function testBase(array $configs, string $expected)
	{
		foreach ($configs as $key => $value)
		{
			$this->setConfig($key, $value);
		}

		Factories::injectMock('config', 'App', $this->config);

		$url = URL::base();

		$this->assertSame($expected, (string) $url);
	}

	public function testTo()
	{
		$url = URL::to('fruit/basket');

		$this->assertSame('http://example.com/index.php/fruit/basket', (string) $url);
	}

	public function testToNamedRoute()
	{
		Services::routes()->add('apples', 'Home::index', ['as' => 'home']);

		$url = URL::to('home');

		$this->assertSame('http://example.com/index.php/apples', (string) $url);
	}

	public function testToReverseRoute()
	{
		Services::routes()->add('oranges', 'Basket::fruit');

		$url = URL::to('App\Controllers\Basket::fruit');

		$this->assertSame('http://example.com/index.php/oranges', (string) $url);
	}

	//--------------------------------------------------------------------

	public function pathProvider(): array
	{
		return [
			[
				'',
				'',
			],
			[
				'/',
				'',
			],
			[
				'//',
				'',
			],
			[
				'/foo/..',
				'',
			],
			[
				'/foo',
				'foo',
			],
			[
				'foo',
				'foo',
			],
			[
				'foo/',
				'foo/',
			],
			[
				'?bar=bam',
				'?bar=bam',
			],
			[
				'foo?bar=bam',
				'foo?bar=bam',
			],
		];
	}

	public function configProvider(): array
	{
		return [
			[
				[
					'baseURL' => 'http://bananas.com',
				],
				'http://bananas.com/index.php',
			],
			[
				[
					'baseURL' => 'http://bananas.com/',
				],
				'http://bananas.com/index.php',
			],
			[
				[
					'baseURL' => 'http://bananas.com/subfolder/',
				],
				'http://bananas.com/subfolder/index.php',
			],
			[
				[
					'indexPage' => '',
				],
				'http://example.com/',
			],
			[
				[
					'baseURL'   => 'http://bananas.com/subfolder/',
					'indexPage' => '',
				],
				'http://bananas.com/subfolder/',
			],
			[
				[
					'forceGlobalSecureRequests' => true,
				],
				'https://example.com/index.php',
			],
			[
				[
					'baseURL'                   => 'http://bananas.com/',
					'forceGlobalSecureRequests' => true,
				],
				'https://bananas.com/index.php',
			],
			[
				[
					'baseURL'                   => 'https://bananas.com/subfolder/',
					'forceGlobalSecureRequests' => true,
				],
				'https://bananas.com/subfolder/index.php',
			],
		];
	}

	public function currentProvider(): array
	{
		return [
			[
				'',
				'http://example.com/index.php',
			],
			[
				'/',
				'http://example.com/index.php',
			],
			[
				'http://example.com/index.php/sauce',
				null,
			],
			[
				'http://example.com/index.php/sauce/',
				null,
			],
			[
				'/sauce/',
				'http://example.com/index.php/sauce/',
			],
			[
				'http://bananas.com/index.php',
				'http://example.com/index.php',
			],
			[
				'https://example.com/index.php',
				'http://example.com/index.php',
			],
			[
				'?blahblah=true',
				'http://example.com/index.php?blahblah=true',
			],
			[
				'http://example.com/index.php?blahblah=true',
				'http://example.com/index.php?blahblah=true',
			],
		];
	}
}
