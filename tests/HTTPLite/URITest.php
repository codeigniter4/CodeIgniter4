<?php

require_once 'system/HTTPLite/URI.php';
require_once 'system/Config/BaseConfig.php';
require_once 'application/config/AppConfig.php';

use CodeIgniter\HTTPLite\URI;

class URITest extends PHPUnit_Framework_TestCase {

	public function setUp()
	{

	}

	//--------------------------------------------------------------------

	public function tearDown()
	{

	}

	//--------------------------------------------------------------------

	public function testConstructorSetsAllParts()
	{
	    $uri = new URI('http://username:password@hostname:9090/path?arg=value#anchor');

		$this->assertEquals('http', $uri->scheme());
		$this->assertEquals('username:password', $uri->userInfo());
		$this->assertEquals('hostname', $uri->host());
		$this->assertEquals('/path', $uri->path());
		$this->assertEquals('arg=value', $uri->query());
		$this->assertEquals('9090', $uri->port());
		$this->assertEquals('anchor', $uri->fragment());
		$this->assertEquals('username:password@hostname:9090', $uri->authority());

		$this->assertEquals(['path'], $uri->segments());
	}

	//--------------------------------------------------------------------

	public function testSegmentsIsPopulatedRightForMultipleSegments()
	{
	    $uri = new URI('http://hostname/path/to/script');

		$this->assertEquals(['path', 'to', 'script'], $uri->segments());
		$this->assertEquals('path', $uri->segment(1));
		$this->assertEquals('to', $uri->segment(2));
		$this->assertEquals('script', $uri->segment(3));

		$this->assertEquals(3, $uri->totalSegments());
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 */
	public function testCanCastAsString()
	{
		$url = 'http://username:password@hostname:9090/path?arg=value#anchor';
	    $uri = new URI($url);

		$this->assertEquals($url, (string)$uri);
	}

	//--------------------------------------------------------------------



}
