<?php

namespace CodeIgniter\Test;

use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\App;

/**
 * This test suite has been created separately from
 * TestCaseTest because it messes with output
 * buffering from PHPUnit, and the individual
 * test cases need to be run as separate processes.
 */
class TestCaseEmissionsTest extends \CIUnitTestCase
{

	public function setUp()
	{
//		while( count( ob_list_handlers() ) > 0 )
//		{
//			ob_end_clean();
//		}
		ob_start();
	}

	public function tearDown()
	{
		ob_end_clean();
	}

	//--------------------------------------------------------------------
	/**
	 * This needs to be run as a separate process, since phpunit
	 * has already captured the "normal" output, and we will get
	 * a "Cannot modify headers" message if we try to change
	 * headers or cookies now.
	 * 
	 * Furthermore, this test needs to flush the output buffering
	 * that might be in progress, and start our own output buffer
	 * capture.
	 * 
	 * This test includes a basic sanity check, to make sure that
	 * the body we thought would be sent actually was.
	 * 
	 * @runInSeparateProcess
	 */
	public function testHeaderEmitted()
	{
		$response = new Response(new App());
		$response->pretend(FALSE);

		$body = 'Hello';
		$expected = $body;

		// what do we think we're about to send?
		$response->setCookie('foo', 'bar');
		$this->assertTrue($response->hasCookie('foo'));
		$this->assertTrue($response->hasCookie('foo', 'bar'));

		// send it
		$response->setBody($body);
		$response->send();

		// and what actually got sent?; test both ways
		$actual = $response->getBody(); // what we thought was sent
//		$buffer = ob_get_clean();

		$this->assertEquals($expected, $actual);
		$this->assertHeaderEmitted("Set-Cookie: foo=bar;");
		$this->assertHeaderEmitted("set-cookie: FOO=bar", true);
	}

	/**
	 * This needs to be run as a separate process, since phpunit
	 * has already captured the "normal" output, and we will get
	 * a "Cannot modify headers" message if we try to change
	 * headers or cookies now.
	 * 
	 * Furthermore, this test needs to flush the output buffering
	 * that might be in progress, and start our own output buffer
	 * capture.
	 * 
	 * This test includes a basic sanity check, to make sure that
	 * the body we thought would be sent actually was.
	 * 
	 * @runInSeparateProcess
	 */
//	public function testHeaderNotEmitted()
//	{
//		$response = new Response(new App());
//		$response->pretend(FALSE);
//
//		$body = 'Hello';
//		$expected = $body;
//
//		// what do we think we're about to send?
//		$response->setCookie('foo', 'bar');
//		$this->assertTrue($response->hasCookie('foo'));
//		$this->assertTrue($response->hasCookie('foo', 'bar'));
//
//		// send it
//		$response->setBody($body);
//
//		ob_start();
//		$response->send();
//		$output = ob_get_clean(); // what really was sent
//		// and what actually got sent?; test both ways
//		$actual = $response->getBody(); // what we thought was sent
//
//		$this->assertEquals($expected, $actual);
//		$this->assertEquals($expected, $output);
//
//		$this->assertHeaderNotEmitted("Set-Cookie: pop=corn", true);
//	}
}
