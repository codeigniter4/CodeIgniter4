<?php

namespace CodeIgniter\Helpers;

class TextHelperTest extends \CIUnitTestCase
{

	private $_long_string = 'Once upon a time, a framework had no tests. It sad. So some nice people began to write tests. The more time that went on, the happier it became. Everyone was happy.';

	protected function setUp(): void
	{
		parent::setUp();

		helper('text');
	}

	// --------------------------------------------------------------------

	public function testStripSlashes()
	{
		$expected = [
			"Is your name O'reilly?",
			"No, my name is O'connor.",
		];
		$str      = [
			"Is your name O\'reilly?",
			"No, my name is O\'connor.",
		];
		$this->assertEquals($expected, strip_slashes($str));
	}

	// --------------------------------------------------------------------
	public function testStripQuotes()
	{
		$strs = [
			'"me oh my!"'    => 'me oh my!',
			"it's a winner!" => 'its a winner!',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, strip_quotes($str));
		}
	}

	// --------------------------------------------------------------------
	public function testQuotesToEntities()
	{
		$strs = [
			'"me oh my!"'    => '&quot;me oh my!&quot;',
			"it's a winner!" => 'it&#39;s a winner!',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, quotes_to_entities($str));
		}
	}

	// --------------------------------------------------------------------
	public function testReduceDoubleSlashes()
	{
		$strs = [
			'http://codeigniter.com'      => 'http://codeigniter.com',
			'//var/www/html/example.com/' => '/var/www/html/example.com/',
			'/var/www/html//index.php'    => '/var/www/html/index.php',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, reduce_double_slashes($str));
		}
	}

	// --------------------------------------------------------------------
	public function testReduceMultiples()
	{
		$strs = [
			'Fred, Bill,, Joe, Jimmy' => 'Fred, Bill, Joe, Jimmy',
			'Ringo, John, Paul,,'     => 'Ringo, John, Paul,',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, reduce_multiples($str));
		}
		$strs = [
			'Fred, Bill,, Joe, Jimmy' => 'Fred, Bill, Joe, Jimmy',
			'Ringo, John, Paul,,'     => 'Ringo, John, Paul',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, reduce_multiples($str, ',', true));
		}
	}

	// --------------------------------------------------------------------
	public function testRandomString()
	{
		$this->assertEquals(16, strlen(random_string('alnum', 16)));
		$this->assertEquals(16, strlen(random_string('alpha', 16)));
		$this->assertEquals(16, strlen(random_string('nozero', 16)));
		$this->assertEquals(16, strlen(random_string('numeric', 16)));
		$this->assertEquals(8, strlen(random_string('numeric')));

		$this->assertInternalType('string', random_string('basic'));
		$this->assertEquals(16, strlen($random = random_string('crypto', 16)));
		$this->assertInternalType('string', $random);

		$this->assertEquals(32, strlen($random = random_string('md5')));
		$this->assertEquals(40, strlen($random = random_string('sha1')));
	}

	// --------------------------------------------------------------------
	public function testIncrementString()
	{
		$this->assertEquals('my-test_1', increment_string('my-test'));
		$this->assertEquals('my-test-1', increment_string('my-test', '-'));
		$this->assertEquals('file_5', increment_string('file_4'));
		$this->assertEquals('file-5', increment_string('file-4', '-'));
		$this->assertEquals('file-5', increment_string('file-4', '-'));
		$this->assertEquals('file-1', increment_string('file', '-', '1'));
		$this->assertEquals(124, increment_string('123', ''));
	}

	// -------------------------------------------------------------------
	// Functions from text_helper_test.php
	// -------------------------------------------------------------------

	public function testWordLimiter()
	{
		$this->assertEquals('Once upon a time,&#8230;', word_limiter($this->_long_string, 4));
		$this->assertEquals('Once upon a time,&hellip;', word_limiter($this->_long_string, 4, '&hellip;'));
		$this->assertEquals('', word_limiter('', 4));
		$this->assertEquals('Once upon a&hellip;', word_limiter($this->_long_string, 3, '&hellip;'));
		$this->assertEquals('Once upon a time', word_limiter('Once upon a time', 4, '&hellip;'));
	}

	// ------------------------------------------------------------------------
	public function testCharacterLimiter()
	{
		$this->assertEquals('Once upon a time, a&#8230;', character_limiter($this->_long_string, 20));
		$this->assertEquals('Once upon a time, a&hellip;', character_limiter($this->_long_string, 20, '&hellip;'));
		$this->assertEquals('Short', character_limiter('Short', 20));
		$this->assertEquals('Short', character_limiter('Short', 5));
	}

	// ------------------------------------------------------------------------
	public function testAsciiToEntities()
	{
		$strs = [
			'“‘ “test” '     => '&#8220;&#8216; &#8220;test&#8221; ',
			'†¥¨ˆøåß∂ƒ©˙∆˚¬' => '&#8224;&#165;&#168;&#710;&#248;&#229;&#223;&#8706;&#402;&#169;&#729;&#8710;&#730;&#172;',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, ascii_to_entities($str));
		}
	}

	// ------------------------------------------------------------------------
	public function testEntitiesToAscii()
	{
		$strs = [
			'&#8220;&#8216; &#8220;test&#8221; '                                                      => '“‘ “test” ',
			'&#8224;&#165;&#168;&#710;&#248;&#229;&#223;&#8706;&#402;&#169;&#729;&#8710;&#730;&#172;' => '†¥¨ˆøåß∂ƒ©˙∆˚¬',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, entities_to_ascii($str));
		}
	}

	public function testEntitiesToAsciiUnsafe()
	{
		$str = '&lt;&gt;';
		$this->assertEquals('<>', entities_to_ascii($str, true));
		$this->assertEquals('&lt;&gt;', entities_to_ascii($str, false));
	}

	public function testEntitiesToAsciiSmallOrdinals()
	{
		$str = '&#07;';
		$this->assertEquals(pack('c', 7), entities_to_ascii($str));
	}

	// ------------------------------------------------------------------------
	public function testConvertAccentedCharacters()
	{
		//$this->ci_vfs_clone('application/Config/ForeignChars.php');
		$this->assertEquals('AAAeEEEIIOOEUUUeY', convert_accented_characters('ÀÂÄÈÊËÎÏÔŒÙÛÜŸ'));
		$this->assertEquals('a e i o u n ue', convert_accented_characters('á é í ó ú ñ ü'));
	}

	// ------------------------------------------------------------------------
	public function testCensoredWords()
	{
		$censored = [
			'boob',
			'nerd',
			'ass',
			'fart',
		];
		$strs     = [
			'Ted bobbled the ball'         => 'Ted bobbled the ball',
			'Jake is a nerdo'              => 'Jake is a nerdo',
			'The borg will assimilate you' => 'The borg will assimilate you',
			'Did Mary Fart?'               => 'Did Mary $*#?',
			'Jake is really a boob'        => 'Jake is really a $*#',
			'Jake is really a (boob)'      => 'Jake is really a ($*#)',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, word_censor($str, $censored, '$*#'));
		}
	}

	// ------------------------------------------------------------------------
	public function testHighlightCode()
	{
		$expect = "<code><span style=\"color: #000000\">\n<span style=\"color: #0000BB\">&lt;?php&nbsp;var_dump</span><span style=\"color: #007700\">(</span><span style=\"color: #0000BB\">\$this</span><span style=\"color: #007700\">);&nbsp;</span><span style=\"color: #0000BB\">?&gt;&nbsp;</span>\n</span>\n</code>";
		$this->assertEquals($expect, highlight_code('<?php var_dump($this); ?>'));
	}

	// ------------------------------------------------------------------------
	public function testHighlightPhrase()
	{
		$strs = [
			'this is a phrase'        => '<mark>this is</mark> a phrase',
			'this is another'         => '<mark>this is</mark> another',
			'Gimme a test, Sally'     => 'Gimme a test, Sally',
			'Or tell me what this is' => 'Or tell me what <mark>this is</mark>',
			''                        => '',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, highlight_phrase($str, 'this is'));
		}
		$this->assertEquals('<strong>this is</strong> a strong test', highlight_phrase('this is a strong test', 'this is', '<strong>', '</strong>'));
	}

	// ------------------------------------------------------------------------
	public function testEllipsize()
	{
		$strs = [
			'0'  => [
				'this is my string'             => '&hellip; my string',
				"here's another one"            => '&hellip;nother one',
				'this one is just a bit longer' => '&hellip;bit longer',
				'short'                         => 'short',
			],
			'.5' => [
				'this is my string'             => 'this &hellip;tring',
				"here's another one"            => "here'&hellip;r one",
				'this one is just a bit longer' => 'this &hellip;onger',
				'short'                         => 'short',
			],
			'1'  => [
				'this is my string'             => 'this is my&hellip;',
				"here's another one"            => "here's ano&hellip;",
				'this one is just a bit longer' => 'this one i&hellip;',
				'short'                         => 'short',
			],
		];
		foreach ($strs as $pos => $s)
		{
			foreach ($s as $str => $expect)
			{
				$this->assertEquals($expect, ellipsize($str, 10, $pos));
			}
		}
	}

	// ------------------------------------------------------------------------
	public function testWordWrap()
	{
		$string   = 'Here is a simple string of text that will help us demonstrate this function.';
		$expected = "Here is a simple string\nof text that will help us\ndemonstrate this\nfunction.";
		$this->assertEquals(substr_count(word_wrap($string, 25), "\n"), 3);
		$this->assertEquals($expected, word_wrap($string, 25));

		$string2   = "Here is a\nbroken up sentence\rspanning lines\r\nwoohoo!";
		$expected2 = "Here is a\nbroken up sentence\nspanning lines\nwoohoo!";
		$this->assertEquals(substr_count(word_wrap($string2, 25), "\n"), 3);
		$this->assertEquals($expected2, word_wrap($string2, 25));

		$string3   = "Here is another slightly longer\nbroken up sentence\rspanning lines\r\nwoohoo!";
		$expected3 = "Here is another slightly\nlonger\nbroken up sentence\nspanning lines\nwoohoo!";
		$this->assertEquals(substr_count(word_wrap($string3, 25), "\n"), 4);
		$this->assertEquals($expected3, word_wrap($string3, 25));
	}

	public function testWordWrapUnwrap()
	{
		$string   = 'Here is a {unwrap}simple string of text{/unwrap} that will help us demonstrate this function.';
		$expected = "Here is a simple string of text\nthat will help us\ndemonstrate this\nfunction.";
		$this->assertEquals(substr_count(word_wrap($string, 25), "\n"), 3);
		$this->assertEquals($expected, word_wrap($string, 25));
	}

	public function testWordWrapLongWords()
	{
		// the really really long word will be split
		$string   = 'Here is an unbelievable super-complicated and reallyreallyquiteextraordinarily sophisticated sentence.';
		$expected = "Here is an unbelievable\nsuper-complicated and\nreallyreallyquiteextraor\ndinarily\nsophisticated sentence.";
		$this->assertEquals($expected, word_wrap($string, 25));
	}

	public function testWordWrapURL()
	{
		// the really really long word will be split
		$string   = 'Here is an unbelievable super-complicated and http://www.reallyreallyquiteextraordinarily.com sophisticated sentence.';
		$expected = "Here is an unbelievable\nsuper-complicated and\nhttp://www.reallyreallyquiteextraordinarily.com\nsophisticated sentence.";
		$this->assertEquals($expected, word_wrap($string, 25));
	}

	// ------------------------------------------------------------------------
	public function testDefaultWordWrapCharlim()
	{
		$string = 'Here is a longer string of text that will help us demonstrate the default charlim of this function.';
		$this->assertEquals(strpos(word_wrap($string), "\n"), 73);
	}

	// -----------------------------------------------------------------------

	public function testExcerpt()
	{
		$string = $this->_long_string;
		$result = ' Once upon a time, a framework had no tests. It sad  So some nice people began to write tests. The more time that went on, the happier it became. ...';
		$this->assertEquals(excerpt($string), $result);
	}

	// -----------------------------------------------------------------------

	public function testExcerptRadius()
	{
		$string = $this->_long_string;
		$phrase = 'began';
		$result = '... people began to ...';
		$this->assertEquals(excerpt($string, $phrase, 10), $result);
	}

	// -----------------------------------------------------------------------

	public function testAlternator()
	{
		$phrase = ' scream! ';
		$result = '';
		alternator();
		for ($i = 0; $i < 4; $i ++)
		{
			$result .= alternator('I', 'you', 'we') . $phrase;
		}
		$this->assertEquals('I scream! you scream! we scream! I scream! ', $result);
	}

	public function testEmptyAlternator()
	{
		$phrase = ' scream! ';
		$result = '';
		for ($i = 0; $i < 4; $i ++)
		{
			$result .= alternator() . $phrase;
		}
		$this->assertEquals(' scream!  scream!  scream!  scream! ', $result);
	}

}
