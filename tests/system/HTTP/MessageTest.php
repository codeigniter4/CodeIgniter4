<?php namespace CodeIgniter\HTTP;

class MessageTest extends \CIUnitTestCase
{
	/**
	 * @var CodeIgniter\HTTP\Message
	 */
	protected $message;

	public function setUp()
	{
		$this->message = new Message();
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

		$headers = $this->message->getHeaders();

		// Content-Type is likely set...
		$this->assertTrue(count($headers) >= 2);

		$this->assertTrue($headers['Host']->getValue() == 'daisyduke.com');
		$this->assertTrue($headers['Referer']->getValue() == 'RoscoePekoTrain.com');
	}

	//--------------------------------------------------------------------

	public function testCanGrabSingleHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

		$header = $this->message->getHeader('Host');

		$this->assertTrue($header instanceof Header);
		$this->assertEquals('daisyduke.com', $header->getValue());
	}

	//--------------------------------------------------------------------

	public function testCaseInsensitveGetHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->assertEquals('daisyduke.com', $this->message->getHeader('host')->getValue());
		$this->assertEquals('daisyduke.com', $this->message->getHeader('HOST')->getValue());
	}

	//--------------------------------------------------------------------

	public function testCanSetHeaders()
	{
		$this->message->setHeader('first', 'kiss');
		$this->message->setHeader('second', ['black', 'book']);

		$this->assertEquals('kiss', $this->message->getHeader('FIRST')->getValue());
		$this->assertEquals(['black', 'book'], $this->message->getHeader('Second')->getValue());
	}

	//--------------------------------------------------------------------

	public function testHeaderLineIsReadable()
	{
		$this->message->setHeader('Accept', ['json', 'html']);
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->assertEquals('json, html', $this->message->getHeader('Accept')->getValueLine());
		$this->assertEquals('daisyduke.com', $this->message->getHeader('Host')->getValueLine());
	}

	//--------------------------------------------------------------------

	public function testCanRemoveHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

		$this->message->removeHeader('host');

		$this->assertEquals('', $this->message->getHeader('host'));
	}

	//--------------------------------------------------------------------

	public function testCanAppendHeader()
	{
		$this->message->setHeader('accept', ['json', 'html']);

		$this->message->appendHeader('Accept', 'xml');

		$this->assertEquals(['json', 'html', 'xml'], $this->message->getHeader('accept')->getValue());
	}

	//--------------------------------------------------------------------

	public function testCanPrependHeader()
	{
		$this->message->setHeader('accept', ['json', 'html']);

		$this->message->prependHeader('Accept', 'xml');

		$this->assertEquals(['xml', 'json', 'html'], $this->message->getHeader('accept')->getValue());
	}

	//--------------------------------------------------------------------

	public function testSetProtocolWorks()
	{
		$this->message->setProtocolVersion('1.1');

		$this->assertEquals('1.1', $this->message->getProtocolVersion());
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

		$this->assertEquals($body, $this->message->getBody());
	}

	//--------------------------------------------------------------------

	public function testCanHaveMultipleHeadersWithSameName()
	{
		$this->message->setHeader('Line', 'foo1');
		$this->message->setHeader('Line', 'foo2');

		$headers = $this->message->getHeader('line');

		$this->assertTrue(is_array($headers));
		$this->assertEquals(2, count($headers));
		$this->assertTrue($headers[0] instanceof Header);
		$this->assertTrue($headers[1] instanceof Header);
		$this->assertEquals('foo1', $headers[0]->getValueLine());
		$this->assertEquals('foo2', $headers[1]->getValueLine());
	}

	//--------------------------------------------------------------------
}
