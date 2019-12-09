<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;

class MessageTest extends \CIUnitTestCase
{

	/**
	 * @var CodeIgniter\HTTP\Message
	 */
	protected $message;

	protected function setUp(): void
	{
		parent::setUp();

		$this->message = new Message();
	}

	//--------------------------------------------------------------------

	public function tearDown(): void
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
		$this->assertGreaterThanOrEqual(2, count($headers));

		$this->assertSame('daisyduke.com', $headers['Host']->getValue());
		$this->assertSame('RoscoePekoTrain.com', $headers['Referer']->getValue());
	}

	//--------------------------------------------------------------------

	public function testCanGrabSingleHeader()
	{
		$this->message->setHeader('Host', 'daisyduke.com');

		$header = $this->message->getHeader('Host');

		$this->assertInstanceOf(Header::class, $header);
		$this->assertEquals('daisyduke.com', $header->getValue());
	}

	//--------------------------------------------------------------------

	public function testCaseInsensitiveGetHeader()
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

	public function testSetHeaderOverwritesPrevious()
	{
		$this->message->setHeader('Pragma', 'cache');
		$this->message->setHeader('Pragma', 'no-cache');

		$this->assertEquals('no-cache', $this->message->getHeader('Pragma')->getValue());
	}

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

	public function testSetProtocolWorksWithNonNumericVersion()
	{
		$this->message->setProtocolVersion('HTTP/1.1');

		$this->assertEquals('1.1', $this->message->getProtocolVersion());
	}

	//--------------------------------------------------------------------

	public function testSetProtocolThrowsExceptionWithInvalidProtocol()
	{
		$this->expectException(HTTPException::class);
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

	public function testAppendBody()
	{
		$this->message->setBody('moo');

		$this->message->appendBody('\n');

		$this->assertEquals('moo' . '\n', $this->message->getBody());
	}

	//--------------------------------------------------------------------

	public function testSetHeaderReplacingHeader()
	{
		$this->message->setHeader('Accept', 'json');

		$this->assertEquals('json', $this->message->getHeaderLine('Accept'));
	}

	public function testSetHeaderDuplicateSettings()
	{
		$this->message->setHeader('Accept', 'json');
		$this->message->setHeader('Accept', 'xml');

		$this->assertEquals('xml', $this->message->getHeaderLine('Accept'));
	}

	public function testSetHeaderDuplicateSettingsInsensitive()
	{
		$this->message->setHeader('Accept', 'json');
		$this->message->setHeader('accept', 'xml');

		$this->assertEquals('xml', $this->message->getHeaderLine('Accept'));
	}

	public function testSetHeaderArrayValues()
	{
		$this->message->setHeader('Accept', ['json', 'html', 'xml']);

			$this->assertEquals('json, html, xml', $this->message->getHeaderLine('Accept'));
	}

	//--------------------------------------------------------------------

	public function testPopulateHeadersWithoutContentType()
	{
		// fail path, if the CONTENT_TYPE doesn't exist
		$original     = $_SERVER;
		$_SERVER      = ['HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.50'];
		$original_env = getenv('CONTENT_TYPE');
		putenv('CONTENT_TYPE');

		$this->message->populateHeaders();

		$this->assertNull($this->message->getHeader('content-type'));
		putenv("CONTENT_TYPE=$original_env");
		$this->message->removeHeader('accept-language');
		$_SERVER = $original; // restore so code coverage doesn't break
	}

	//--------------------------------------------------------------------

	public function testPopulateHeadersWithoutHTTP()
	{
		// fail path, if arguement does't have the HTTP_*
		$original = $_SERVER;
		$_SERVER  = [
			'USER_AGENT'     => 'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405',
			'REQUEST_METHOD' => 'POST',
		];

		$this->message->populateHeaders();

		$this->assertNull($this->message->getHeader('user-agent'));
		$this->assertNull($this->message->getHeader('request-method'));
		$_SERVER = $original; // restore so code coverage doesn't break
	}

	//--------------------------------------------------------------------

	public function testPopulateHeadersKeyNotExists()
	{
		// Success path, if array key is not exists, assign empty string to it's value
		$original = $_SERVER;
		$_SERVER  = [
			'CONTENT_TYPE'        => 'text/html; charset=utf-8',
			'HTTP_ACCEPT_CHARSET' => null,
		];

		$this->message->populateHeaders();

		$this->assertEquals('', $this->message->getHeader('accept-charset')->getValue());
		$this->message->removeHeader('accept-charset');
		$_SERVER = $original; // restore so code coverage doesn't break
	}

	//--------------------------------------------------------------------

	public function testPopulateHeaders()
	{
		// success path
		$original = $_SERVER;
		$_SERVER  = [
			'CONTENT_TYPE'         => 'text/html; charset=utf-8',
			'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.50',
		];

		$this->message->populateHeaders();

		$this->assertEquals('text/html; charset=utf-8', $this->message->getHeader('content-type')->getValue());
		$this->assertEquals('en-us,en;q=0.50', $this->message->getHeader('accept-language')->getValue());
		$this->message->removeHeader('content-type');
		$this->message->removeHeader('accept-language');
		$_SERVER = $original; // restore so code coverage doesn't break
	}

}
