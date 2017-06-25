<?php namespace CodeIgniter\Helpers;

class TextHelperTest extends \CIUnitTestCase
{
    private $_long_string = 'Once upon a time, a framework had no tests. It sad. So some nice people began to write tests. The more time that went on, the happier it became. Everyone was happy.';
    
    public function setUp()
    {
        helper('text');
    }
    
    // --------------------------------------------------------------------
    
    public function test_strip_slashes()
	{
		$expected = [
			"Is your name O'reilly?",
			"No, my name is O'connor."
		];
		$str = [
			"Is your name O\'reilly?",
			"No, my name is O\'connor."
		];
		$this->assertEquals($expected, strip_slashes($str));
	}
	// --------------------------------------------------------------------
	public function test_strip_quotes()
	{
		$strs = [
			'"me oh my!"'		=> 'me oh my!',
			"it's a winner!"	=> 'its a winner!',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, strip_quotes($str));
		}
	}
	// --------------------------------------------------------------------
	public function test_quotes_to_entities()
	{
		$strs = [
			'"me oh my!"'		=> '&quot;me oh my!&quot;',
			"it's a winner!"	=> 'it&#39;s a winner!',
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, quotes_to_entities($str));
		}
	}
	// --------------------------------------------------------------------
	public function test_reduce_double_slashes()
	{
		$strs = [
			'http://codeigniter.com'		=> 'http://codeigniter.com',
			'//var/www/html/example.com/'	=> '/var/www/html/example.com/',
			'/var/www/html//index.php'		=> '/var/www/html/index.php'
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, reduce_double_slashes($str));
		}
	}
	// --------------------------------------------------------------------
	public function test_reduce_multiples()
	{
		$strs = [
			'Fred, Bill,, Joe, Jimmy'	=> 'Fred, Bill, Joe, Jimmy',
			'Ringo, John, Paul,,'		=> 'Ringo, John, Paul,'
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, reduce_multiples($str));
		}
		$strs = [
			'Fred, Bill,, Joe, Jimmy'	=> 'Fred, Bill, Joe, Jimmy',
			'Ringo, John, Paul,,'		=> 'Ringo, John, Paul'
		];
		foreach ($strs as $str => $expect)
		{
			$this->assertEquals($expect, reduce_multiples($str, ',', TRUE));
		}
	}
	// --------------------------------------------------------------------
	public function test_random_string()
	{
		$this->assertEquals(16, strlen(random_string('alnum', 16)));
		$this->assertInternalType('string', random_string('numeric', 16));
	}
	// --------------------------------------------------------------------
	public function test_increment_string()
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
	
    public function test_word_limiter()
    {
        $this->assertEquals('Once upon a time,&#8230;', word_limiter($this->_long_string, 4));
        $this->assertEquals('Once upon a time,&hellip;', word_limiter($this->_long_string, 4, '&hellip;'));
        $this->assertEquals('', word_limiter('', 4));
    }
    // ------------------------------------------------------------------------
    public function test_character_limiter()
    {
        $this->assertEquals('Once upon a time, a&#8230;', character_limiter($this->_long_string, 20));
        $this->assertEquals('Once upon a time, a&hellip;', character_limiter($this->_long_string, 20, '&hellip;'));
        $this->assertEquals('Short', character_limiter('Short', 20));
        $this->assertEquals('Short', character_limiter('Short', 5));
    }
    // ------------------------------------------------------------------------
    public function test_ascii_to_entities()
    {
        $strs = [
                '“‘ “test”'			=> '&#8220;&#8216; &#8220;test&#8221;',
                '†¥¨ˆøåß∂ƒ©˙∆˚¬'	=> '&#8224;&#165;&#168;&#710;&#248;&#229;&#223;&#8706;&#402;&#169;&#729;&#8710;&#730;&#172;'
        ];
        foreach ($strs as $str => $expect)
        {
            $this->assertEquals($expect, ascii_to_entities($str));
        }
    }
    // ------------------------------------------------------------------------
    public function test_entities_to_ascii()
    {
        $strs = [
                '&#8220;&#8216; &#8220;test&#8221;' => '“‘ “test”',
                '&#8224;&#165;&#168;&#710;&#248;&#229;&#223;&#8706;&#402;&#169;&#729;&#8710;&#730;&#172;' => '†¥¨ˆøåß∂ƒ©˙∆˚¬'
        ];
        foreach ($strs as $str => $expect)
        {
            $this->assertEquals($expect, entities_to_ascii($str));
        }
    }
    // ------------------------------------------------------------------------
    public function test_convert_accented_characters()
    {
        //$this->ci_vfs_clone('application/Config/ForeignChars.php');
        $this->assertEquals('AAAeEEEIIOOEUUUeY', convert_accented_characters('ÀÂÄÈÊËÎÏÔŒÙÛÜŸ'));
        $this->assertEquals('a e i o u n ue', convert_accented_characters('á é í ó ú ñ ü'));
    }
    // ------------------------------------------------------------------------
    public function test_censored_words()
    {
        $censored = ['boob', 'nerd', 'ass', 'fart'];
        $strs = [
                'Ted bobbled the ball' 			=> 'Ted bobbled the ball',
                'Jake is a nerdo'				=> 'Jake is a nerdo',
                'The borg will assimilate you'	=> 'The borg will assimilate you',
                'Did Mary Fart?'				=> 'Did Mary $*#?',
                'Jake is really a boob'			=> 'Jake is really a $*#'
        ];
        foreach ($strs as $str => $expect)
        {
            $this->assertEquals($expect, word_censor($str, $censored, '$*#'));
        }
        // test censored words being sent as a string
        $this->assertEquals('test', word_censor('test', 'test'));
    }
    // ------------------------------------------------------------------------
    public function test_highlight_code()
    {
        $expect = "<code><span style=\"color: #000000\">\n<span style=\"color: #0000BB\">&lt;?php&nbsp;var_dump</span><span style=\"color: #007700\">(</span><span style=\"color: #0000BB\">\$this</span><span style=\"color: #007700\">);&nbsp;</span><span style=\"color: #0000BB\">?&gt;&nbsp;</span>\n</span>\n</code>";
        $this->assertEquals($expect, highlight_code('<?php var_dump($this); ?>'));
    }
    // ------------------------------------------------------------------------
    public function test_highlight_phrase()
    {
        $strs = [
                'this is a phrase'          => '<mark>this is</mark> a phrase',
                'this is another'           => '<mark>this is</mark> another',
                'Gimme a test, Sally'       => 'Gimme a test, Sally',
                'Or tell me what this is'   => 'Or tell me what <mark>this is</mark>',
                ''                          => ''
        ];
        foreach ($strs as $str => $expect)
        {
            $this->assertEquals($expect, highlight_phrase($str, 'this is'));
        }
        $this->assertEquals('<strong>this is</strong> a strong test', highlight_phrase('this is a strong test', 'this is', '<strong>', '</strong>'));
    }
    // ------------------------------------------------------------------------
    public function test_ellipsize()
    {
        $strs = [
                '0'		=> [
                        'this is my string'				=> '&hellip; my string',
                        "here's another one"			=> '&hellip;nother one',
                        'this one is just a bit longer'	=> '&hellip;bit longer',
                        'short'							=> 'short'
                ],
                '.5'	=> [
                        'this is my string'				=> 'this &hellip;tring',
                        "here's another one"			=> "here'&hellip;r one",
                        'this one is just a bit longer'	=> 'this &hellip;onger',
                        'short'							=> 'short'
                ],
                '1'	=> [
                        'this is my string'				=> 'this is my&hellip;',
                        "here's another one"			=> "here's ano&hellip;",
                        'this one is just a bit longer'	=> 'this one i&hellip;',
                        'short'							=> 'short'
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
    public function test_word_wrap()
    {
        $string = 'Here is a simple string of text that will help us demonstrate this function.';
        $this->assertEquals(substr_count(word_wrap($string, 25), "\n"), 4);
    }
    // ------------------------------------------------------------------------
    public function test_default_word_wrap_charlim()
    {
        $string = "Here is a longer string of text that will help us demonstrate the default charlim of this function.";
        $this->assertEquals(strpos(word_wrap($string), "\n"), 73);
    }
    
    // -----------------------------------------------------------------------
    
    public function test_excerpt()
    {
        $string = $this->_long_string;
        $result = ' Once upon a time, a framework had no tests. It sad  So some nice people began to write tests. The more time that went on, the happier it became. ...';
        $this->assertEquals(excerpt($string), $result);
    }
    
    // -----------------------------------------------------------------------
    
    public function test_excerpt_radius()
    {
        $string = $this->_long_string;
        $phrase = 'began';
        $result = '... people began to ...';
        $this->assertEquals(excerpt($string, $phrase, 10), $result);
    }
}
