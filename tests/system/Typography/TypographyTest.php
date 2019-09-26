<?php namespace CodeIgniter\Typography;

class TypographyTest extends \CIUnitTestCase
{
	protected $typography;

	protected function setUp(): void
	{
		parent::setUp();
		$this->typography = new Typography();
	}

	public function testAutoTypographyEmptyString()
	{
		$this->assertEquals('', $this->typography->autoTypography(''));
	}

	public function testAutoTypographyNormalString()
	{
		$strs = [
			'this sentence has no punctuations' => '<p>this sentence has no punctuations</p>',
			'Hello World !!, How are you?'      => '<p>Hello World !!, How are you?</p>',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, $this->typography->autoTypography($str));
		}
	}

	public function testAutoTypographyMultipleSpaces()
	{
		$strs = [
			'this sentence has  a double spacing'              => '<p>this sentence has  a double spacing</p>',
			'this  sentence   has    a     weird      spacing' => '<p>this  sentence &nbsp; has &nbsp;  a &nbsp;   weird &nbsp;  &nbsp; spacing</p>',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, $this->typography->autoTypography($str));
		}
	}

	public function testAutoTypographyLineBreaks()
	{
		$strs = [
			"\n"                                   => "\n\n<p>&nbsp;</p>",
			"\n\n"                                 => "\n\n<p>&nbsp;</p>",
			"\n\n\n"                               => "\n\n<p>&nbsp;</p>",
			"Line One\n"                           => "<p>Line One</p>\n\n",
			"Line One\nLine Two"                   => "<p>Line One<br />\nLine Two</p>",
			"Line One\r\n"                         => "<p>Line One</p>\n\n",
			"Line One\r\nLine Two"                 => "<p>Line One<br />\nLine Two</p>",
			"Line One\r"                           => "<p>Line One</p>\n\n",
			"Line One\rLine Two"                   => "<p>Line One<br />\nLine Two</p>",
			"Line One\n\nLine Two\n\n\nLine Three" => "<p>Line One</p>\n\n<p>Line Two</p>\n\n<p>Line Three</p>",
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, $this->typography->autoTypography($str));
		}
	}

	public function testAutoTypographyReduceLineBreaks()
	{
		$strs = [
			"\n"                                   => "\n\n",
			"\n\n"                                 => "\n\n",
			"\n\n\n"                               => "\n\n\n\n",
			"Line One\n"                           => "<p>Line One</p>\n\n",
			"Line One\nLine Two"                   => "<p>Line One<br />\nLine Two</p>",
			"Line One\r\n"                         => "<p>Line One</p>\n\n",
			"Line One\r\nLine Two"                 => "<p>Line One<br />\nLine Two</p>",
			"Line One\r"                           => "<p>Line One</p>\n\n",
			"Line One\rLine Two"                   => "<p>Line One<br />\nLine Two</p>",
			"Line One\n\nLine Two\n\n\nLine Three" => "<p>Line One</p>\n\n<p>Line Two</p>\n\n<p><br />\nLine Three</p>",
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, $this->typography->autoTypography($str, true));
		}
	}

	public function testAutoTypographyHTMLComment()
	{
		$strs = [
			'<!-- this is an HTML comment -->'                       => '<!-- this is an HTML comment -->',
			'This is not a comment.<!-- this is an HTML comment -->' => '<p>This is not a comment.<!-- this is an HTML comment --></p>',
			'<!-- this is an HTML comment -->This is not a comment.' => '<p><!-- this is an HTML comment -->This is not a comment.</p>',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, $this->typography->autoTypography($str));
		}
	}

	public function testAutoTypographyHTMLTags()
	{
		$strs = [
			'<b>Hello World !!</b>, How are you?'               => '<p><b>Hello World !!</b>, How are you?</p>',
			'<p>Hello World !!, How are you?</p>'               => '<p>Hello World !!, How are you?</p>',
			'<pre>Code goes here.</pre>'                        => '<pre>Code goes here.</pre>',
			"<pre>Line One\nLine Two\n\nLine Three\n\n\n</pre>" => "<pre>Line One\nLine Two\n\nLine Three\n\n</pre>",
			'Line One</pre>'                                    => 'Line One</pre>',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, $this->typography->autoTypography($str));
		}
	}

	public function testAutoTypographySpecialCharacters()
	{
		$strs = [
			'\'Text in single quotes\''      => '<p>&#8216;Text in single quotes&#8217;</p>',
			'"Text in double quotes"'        => '<p>&#8220;Text in double quotes&#8221;</p>',
			'Double dash -- becomes em-dash' => '<p>Double dash&#8212;becomes em-dash</p>',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, $this->typography->autoTypography($str));
		}
	}

	public function testNewlinesToHTMLLineBreaksExceptWithinPRE()
	{
		$strs = [
			"Line One\nLine Two"            => "Line One<br />\nLine Two",
			"<pre>Line One\nLine Two</pre>" => "<pre>Line One\nLine Two</pre>",
			"<div>Line One\nLine Two</div>" => "<div>Line One<br />\nLine Two</div>",
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, $this->typography->nl2brExceptPre($str));
		}
	}
}
