<?php namespace CodeIgniter\Helpers;

class SecurityHelperTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		helper('security');
	}

	public function testSanitizeFilenameSimpleSuccess()
	{
		$this->assertEquals('hello.doc', sanitize_filename('hello.doc'));
	}

	public function testSanitizeFilenameStripsExtras()
	{
		$filename = './<!--foo -->';
		$this->assertEquals('foo ', sanitize_filename($filename));
	}

	public function testStripImageTags()
	{
		$this->assertEquals('http://example.com/spacer.gif', strip_image_tags('http://example.com/spacer.gif'));

		$this->assertEquals('http://example.com/spacer.gif', strip_image_tags('<img src="http://example.com/spacer.gif" alt="Who needs CSS when you have a spacer.gif?" />'));
	}

	function test_encode_php_tags()
	{
		$this->assertEquals('&lt;? echo $foo; ?&gt;', encode_php_tags('<? echo $foo; ?>'));
	}

}
