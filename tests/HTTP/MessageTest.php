<?php

class MessageTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var CodeIgniter\HTTP\Message
	 */
	protected $message;

	public function setUp()
	{
	    $this->message = new \CodeIgniter\HTTP\Message();
	}

	//--------------------------------------------------------------------

	public function tearDown()
	{
		$this->message = null;
	    unset($this->message);
	}

	//--------------------------------------------------------------------


	// We can only test the headers retrieved from $_SERVER
	// This test might fail under apache.
	public function testHeadersRetrievesHeaders()
	{
		$this->message->setHeader('Host', 'daisyduke.com');
		$this->message->setHeader('Referer', 'RoscoePekoTrain.com');

		$headers = $this->message->headers();

		// Content-Type is likely set...
		$this->assertTrue(count($headers) >= 2);

		$this->assertTrue($headers['Host'] == 'daisyduke.com');
		$this->assertTrue($headers['Referer'] == 'RoscoePekoTrain.com');
	}

	//--------------------------------------------------------------------

	public function testCanGrabSingleHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

	    $this->assertEquals('daisyduke.com', $this->message->header('Host'));
	}

	//--------------------------------------------------------------------

	public function testCaseInsensitveGetHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->assertEquals('daisyduke.com', $this->message->header('host'));
		$this->assertEquals('daisyduke.com', $this->message->header('HOST'));
	}

	//--------------------------------------------------------------------

	public function testCanSetHeaders()
	{
	    $this->message->setHeader('first', 'kiss');
		$this->message->setHeader('second', ['black', 'book']);

		$this->assertEquals('kiss', $this->message->header('FIRST'));
		$this->assertEquals(['black', 'book'], $this->message->header('Second'));
	}

	//--------------------------------------------------------------------

	public function testHeaderLineIsReadable()
	{
		$this->message->setHeader('Accept', ['json', 'html']);
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->assertEquals('json, html', $this->message->headerLine('Accept'));
		$this->assertEquals('daisyduke.com', $this->message->headerLine('Host'));
	}

	//--------------------------------------------------------------------

	public function testCanRemoveHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->message->removeHeader('host');

		$this->assertEquals('', $this->message->header('host'));
	}

	//--------------------------------------------------------------------

	public function testCanAppendHeader()
	{
	    $this->message->setHeader('accept', ['json', 'html']);

		$this->message->appendHeader('Accept', 'xml');

		$this->assertEquals(['json', 'html', 'xml'], $this->message->header('accept'));
	}

	//--------------------------------------------------------------------

	public function testAppendHeaderThrowsExceptionOnNotArray()
	{
	    $this->message->setHeader('accept', 'json');

		$this->setExpectedException('LogicException');

		$this->message->appendHeader('Accept', 'xml');
	}

	//--------------------------------------------------------------------

	public function testSetProtocolWorks()
	{
	    $this->message->setProtocolVersion('1.1');

		$this->assertEquals('1.1', $this->message->protocolVersion());
	}

	//--------------------------------------------------------------------

	public function testSetProtocolThrowsExceptionWithInvalidProtocol()
	{
		$this->setExpectedException('InvalidArgumentException');
	    $this->message->setProtocolVersion('1.2');
	}

	//--------------------------------------------------------------------

	public function testBodyBasics()
	{
		$body = 'a strange little fellow.';

	    $this->message->setBody($body);

		$this->assertEquals($body, $this->message->body());
	}

	//--------------------------------------------------------------------

}
