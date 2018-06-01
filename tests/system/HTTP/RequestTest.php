<?php namespace CodeIgniter\HTTP;

use Config\App;

/**
 * @backupGlobals enabled
 */
class RequestTest extends \CIUnitTestCase
{
	/**
	 * @var \CodeIgniter\HTTP\Request
	 */
	protected $request;

	public function setUp()
	{
		parent::setUp();

		$this->request = new Request(new App());
		$_POST = [];
		$_GET = [];
	}

	public function testFetchGlobalsSingleValue()
	{
		$_POST['foo'] = 'bar';
		$_GET['bar']  = 'baz';

		$this->assertEquals('bar', $this->request->fetchGlobal('post', 'foo'));
		$this->assertEquals('baz', $this->request->fetchGlobal('get', 'bar'));
	}

	public function testFetchGlobalsReturnsNullWhenNotFound()
	{
		$this->assertNull($this->request->fetchGlobal('post', 'foo'));
	}

	public function testFetchGlobalsFiltersValues()
	{
		$this->request->setGlobal('post', [
			'foo' => 'bar<script>',
			'bar' => 'baz',
		]);

		$this->assertEquals('bar%3Cscript%3E', $this->request->fetchGlobal('post', 'foo', FILTER_SANITIZE_ENCODED));
		$this->assertEquals('baz', $this->request->fetchGlobal('post', 'bar'));
	}

	public function testFetchGlobalsWithFilterFlag()
	{
		$this->request->setGlobal('post', [
			'foo' => '`bar<script>',
			'bar' => 'baz',
		]);

		$this->assertEquals('bar%3Cscript%3E', $this->request->fetchGlobal('post','foo', FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
		$this->assertEquals('baz', $this->request->fetchGlobal('post', 'bar'));
	}

	public function testFetchGlobalReturnsAllWhenEmpty()
	{
		$post = [
			'foo' => 'bar',
			'bar' => 'baz',
			'xxx' => 'yyy',
			'yyy' => 'zzz'
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals($post, $this->request->fetchGlobal('post'));
	}

	public function testFetchGlobalFiltersAllValues()
	{
		$post = [
			'foo' => 'bar<script>',
			'bar' => 'baz<script>',
			'xxx' => 'yyy<script>',
			'yyy' => 'zzz<script>'
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
			'xxx' => 'yyy%3Cscript%3E',
			'yyy' => 'zzz%3Cscript%3E'
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', null, FILTER_SANITIZE_ENCODED));
	}

	public function testFetchGlobalFilterWithFlagAllValues()
	{
		$post = [
			'foo' => '`bar<script>',
			'bar' => '`baz<script>',
			'xxx' => '`yyy<script>',
			'yyy' => '`zzz<script>'
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
			'xxx' => 'yyy%3Cscript%3E',
			'yyy' => 'zzz%3Cscript%3E'
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post',null, FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
	}

	public function testFetchGlobalReturnsSelectedKeys()
	{
		$post = [
			'foo' => 'bar',
			'bar' => 'baz',
			'xxx' => 'yyy',
			'yyy' => 'zzz'
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', ['foo', 'bar']));
	}

	public function testFetchGlobalFiltersSelectedValues()
	{
		$post = [
			'foo' => 'bar<script>',
			'bar' => 'baz<script>',
			'xxx' => 'yyy<script>',
			'yyy' => 'zzz<script>'
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', ['foo', 'bar'], FILTER_SANITIZE_ENCODED));
	}

	public function testFetchGlobalFilterWithFlagSelectedValues()
	{
		$post = [
			'foo' => '`bar<script>',
			'bar' => '`baz<script>',
			'xxx' => '`yyy<script>',
			'yyy' => '`zzz<script>'
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', ['foo', 'bar'], FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
	}

	/**
	 * @see https://github.com/bcit-ci/CodeIgniter4/issues/353
	 */
	public function testFetchGlobalReturnsArrayValues()
	{
		$post = [
			'ANNOUNCEMENTS' => [
				1 => [
					'DETAIL' => 'asdf'
				],
				2 => [
					'DETAIL' => 'sdfg'
				]
			],
			'submit' => 'SAVE'
		];
		$this->request->setGlobal('post', $post);
		$result = $this->request->fetchGlobal('post');

		$this->assertEquals($post, $result);
		$this->assertInternalType('array', $result['ANNOUNCEMENTS']);
		$this->assertCount(2, $result['ANNOUNCEMENTS']);
	}

	public function testFetchGlobalWithArrayTop()
	{
		$post = [
			'clients' => [
				'address' => [
					'zipcode' => 90210
				]
			]
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals(['address' => ['zipcode' => 90210]], $this->request->fetchGlobal('post','clients'));
	}

	public function testFetchGlobalWithArrayChildNumeric()
	{
		$post = [
			'clients' => [
				[
					'address' => [
						'zipcode' => 90210
					],
				],
				[
					'address' => [
						'zipcode' => 60610
					],
				],
			]
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals(['zipcode' => 60610], $this->request->fetchGlobal('post','clients[1][address]'));
	}

	public function testFetchGlobalWithArrayChildElement()
	{
		$post = [
			'clients' => [
				'address' => [
					'zipcode' => 90210
				],
			]
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals(['zipcode' => 90210], $this->request->fetchGlobal('post','clients[address]'));
	}

	public function testFetchGlobalWithArrayLastElement()
	{
		$post = [
			'clients' => [
				'address' => [
					'zipcode' => 90210
				]
			]
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals(90210, $this->request->fetchGlobal('post', 'clients[address][zipcode]'));
	}

	public function ipAddressChecks()
	{
		return [
			'empty' => [false, ''],
			'zero'  => [false , 0],
			'large_ipv4' => [false, '256.256.256.999', 'ipv4'],
			'good_ipv4'  => [true, '100.100.100.0', 'ipv4'],
			'good_default'  => [true, '100.100.100.0'],
			'zeroed_ipv4' => [true, '0.0.0.0'],
			'large_ipv6' => [false, 'h123:0000:0000:0000:0000:0000:0000:0000', 'ipv6'],
			'good_ipv6' => [true, '2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
			'confused_ipv6' => [false, '255.255.255.255', 'ipv6'],
		];
	}

	/**
	 * @dataProvider ipAddressChecks
	 */
	public function testValidIPAddress($expected, $address, $type=null)
	{
		$this->assertEquals($expected, $this->request->isValidIP($address, $type));
	}

	public function testMethodReturnsRightStuff()
	{
		// Defaults method to GET now.
		$this->assertEquals('get', $this->request->getMethod());
		$this->assertEquals('GET', $this->request->getMethod(true));
	}
}
