<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 *
 * @group Others
 */
final class TextHelperTest extends CIUnitTestCase
{
    private string $_long_string = 'Once upon a time, a framework had no tests. It sad. So some nice people began to write tests. The more time that went on, the happier it became. Everyone was happy.';

    protected function setUp(): void
    {
        parent::setUp();

        helper('text');
    }

    public function testStripSlashes(): void
    {
        $expected = [
            "Is your name O'reilly?",
            "No, my name is O'connor.",
        ];
        $str = [
            "Is your name O\\'reilly?",
            "No, my name is O\\'connor.",
        ];
        $this->assertSame($expected, strip_slashes($str));
    }

    public function testStripQuotes(): void
    {
        $strs = [
            '"me oh my!"'    => 'me oh my!',
            "it's a winner!" => 'its a winner!',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, strip_quotes($str));
        }
    }

    public function testQuotesToEntities(): void
    {
        $strs = [
            '"me oh my!"'    => '&quot;me oh my!&quot;',
            "it's a winner!" => 'it&#39;s a winner!',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, quotes_to_entities($str));
        }
    }

    public function testReduceDoubleSlashes(): void
    {
        $strs = [
            'http://codeigniter.com'      => 'http://codeigniter.com',
            '//var/www/html/example.com/' => '/var/www/html/example.com/',
            '/var/www/html//index.php'    => '/var/www/html/index.php',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, reduce_double_slashes($str));
        }
    }

    public function testReduceMultiples(): void
    {
        $strs = [
            'Fred, Bill,, Joe, Jimmy' => 'Fred, Bill, Joe, Jimmy',
            'Ringo, John, Paul,,'     => 'Ringo, John, Paul,',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, reduce_multiples($str));
        }
        $strs = [
            'Fred, Bill,, Joe, Jimmy' => 'Fred, Bill, Joe, Jimmy',
            'Ringo, John, Paul,,'     => 'Ringo, John, Paul',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, reduce_multiples($str, ',', true));
        }
    }

    public function testRandomString(): void
    {
        $this->assertSame(16, strlen(random_string('alnum', 16)));
        $this->assertSame(16, strlen(random_string('alpha', 16)));
        $this->assertSame(16, strlen(random_string('nozero', 16)));
        $this->assertSame(16, strlen(random_string('numeric', 16)));
        $this->assertSame(8, strlen(random_string('numeric')));

        $this->assertIsString(random_string('basic'));
        $this->assertSame(16, strlen($random = random_string('crypto', 16)));
        $this->assertIsString($random);

        $this->assertSame(32, strlen($random = random_string('md5')));
        $this->assertSame(40, strlen($random = random_string('sha1')));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6330
     */
    public function testRandomStringCryptoOddNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'You must set an even number to the second parameter when you use `crypto`'
        );

        random_string('crypto', 9);
    }

    public function testIncrementString(): void
    {
        $this->assertSame('my-test_1', increment_string('my-test'));
        $this->assertSame('my-test-1', increment_string('my-test', '-'));
        $this->assertSame('file_5', increment_string('file_4'));
        $this->assertSame('file-5', increment_string('file-4', '-'));
        $this->assertSame('file-5', increment_string('file-4', '-'));
        $this->assertSame('file-1', increment_string('file', '-', '1'));
        $this->assertSame('124', increment_string('123', ''));
    }

    // Functions from text_helper_test.php

    public function testWordLimiter(): void
    {
        $this->assertSame('Once upon a time,&#8230;', word_limiter($this->_long_string, 4));
        $this->assertSame('Once upon a time,&hellip;', word_limiter($this->_long_string, 4, '&hellip;'));
        $this->assertSame('', word_limiter('', 4));
        $this->assertSame('Once upon a&hellip;', word_limiter($this->_long_string, 3, '&hellip;'));
        $this->assertSame('Once upon a time', word_limiter('Once upon a time', 4, '&hellip;'));
    }

    public function testCharacterLimiter(): void
    {
        $this->assertSame('Once upon a time, a&#8230;', character_limiter($this->_long_string, 20));
        $this->assertSame('Once upon a time, a&hellip;', character_limiter($this->_long_string, 20, '&hellip;'));
        $this->assertSame('Short', character_limiter('Short', 20));
        $this->assertSame('Short', character_limiter('Short', 5));
    }

    public function testAsciiToEntities(): void
    {
        $strs = [
            'Œ'              => '&#338;',
            'Âé'             => '&#194;&#233;',
            'Â? '            => '&#194;? ',
            '“‘ “test” '     => '&#8220;&#8216; &#8220;test&#8221; ',
            '†¥¨ˆøåß∂ƒ©˙∆˚¬' => '&#8224;&#165;&#168;&#710;&#248;&#229;&#223;&#8706;&#402;&#169;&#729;&#8710;&#730;&#172;',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, ascii_to_entities($str));
        }
    }

    public function testEntitiesToAscii(): void
    {
        $strs = [
            '&#338;'                                                                                  => 'Œ',
            '&#194;&#233;'                                                                            => 'Âé',
            '&#194;? '                                                                                => 'Â? ',
            '&#8220;&#8216; &#8220;test&#8221; '                                                      => '“‘ “test” ',
            '&#8224;&#165;&#168;&#710;&#248;&#229;&#223;&#8706;&#402;&#169;&#729;&#8710;&#730;&#172;' => '†¥¨ˆøåß∂ƒ©˙∆˚¬',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, entities_to_ascii($str));
        }
    }

    public function testEntitiesToAsciiUnsafe(): void
    {
        $str = '&lt;&gt;';
        $this->assertSame('<>', entities_to_ascii($str, true));
        $this->assertSame('&lt;&gt;', entities_to_ascii($str, false));
    }

    public function testEntitiesToAsciiSmallOrdinals(): void
    {
        $str = '&#07;';
        $this->assertSame(pack('c', 7), entities_to_ascii($str));
    }

    public function testConvertAccentedCharacters(): void
    {
        $this->assertSame('AAAeEEEIIOOEUUUeY', convert_accented_characters('ÀÂÄÈÊËÎÏÔŒÙÛÜŸ'));
        $this->assertSame('a e i o u n ue', convert_accented_characters('á é í ó ú ñ ü'));
    }

    public function testCensoredWords(): void
    {
        $this->assertSame('fuck', word_censor('fuck', []));
    }

    public function testCensoredWordsWithReplacement(): void
    {
        $censored = [
            'boob',
            'nerd',
            'ass',
            'asshole',
            'fart',
            'fuck',
            'fucking',
        ];
        $strs = [
            'Ted bobbled the ball'         => 'Ted bobbled the ball',
            'Jake is a nerdo'              => 'Jake is a nerdo',
            'The borg will assimilate you' => 'The borg will assimilate you',
            'Did Mary Fart?'               => 'Did Mary $*#?',
            'Jake is really a boob'        => 'Jake is really a $*#',
            'Jake is really a (boob)'      => 'Jake is really a ($*#)',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, word_censor($str, $censored, '$*#'));
        }
    }

    public function testCensoredWordsNonReplacement(): void
    {
        $censored = [
            'boob',
            'nerd',
            'ass',
            'asshole',
            'fart',
            'fuck',
            'fucking',
        ];
        $strs = [
            'How are you today?'          => 'How are you today?',
            'I am fine, thankyou!'        => 'I am fine, thankyou!',
            'Are you fucking kidding me?' => 'Are you ####### kidding me?',
            'Fucking asshole!'            => '####### #######!',
        ];

        foreach ($strs as $str => $expected) {
            $this->assertSame($expected, word_censor($str, $censored));
        }
    }

    public function testHighlightCode(): void
    {
        // PHP 8.3 changes the output.
        if (PHP_VERSION_ID >= 80300) {
            $expect = '<pre><code style="color: #000000"><span style="color: #0000BB">&lt;?php var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt; ?&gt;</span></code></pre>';
        } else {
            $expect = "<code><span style=\"color: #000000\">\n<span style=\"color: #0000BB\">&lt;?php&nbsp;var_dump</span><span style=\"color: #007700\">(</span><span style=\"color: #0000BB\">\$this</span><span style=\"color: #007700\">);&nbsp;</span><span style=\"color: #0000BB\">?&gt;&nbsp;</span>\n</span>\n</code>";
        }

        $this->assertSame($expect, highlight_code('<?php var_dump($this); ?>'));
    }

    public function testHighlightPhrase(): void
    {
        $strs = [
            'this is a phrase'        => '<mark>this is</mark> a phrase',
            'this is another'         => '<mark>this is</mark> another',
            'Gimme a test, Sally'     => 'Gimme a test, Sally',
            'Or tell me what this is' => 'Or tell me what <mark>this is</mark>',
            ''                        => '',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, highlight_phrase($str, 'this is'));
        }

        $this->assertSame('<strong>this is</strong> a strong test', highlight_phrase('this is a strong test', 'this is', '<strong>', '</strong>'));
    }

    public function testEllipsize(): void
    {
        $strs = [
            '0' => [
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
            '1' => [
                'this is my string'             => 'this is my&hellip;',
                "here's another one"            => "here's ano&hellip;",
                'this one is just a bit longer' => 'this one i&hellip;',
                'short'                         => 'short',
            ],
        ];

        foreach ($strs as $pos => $s) {
            foreach ($s as $str => $expect) {
                $this->assertSame($expect, ellipsize($str, 10, $pos));
            }
        }
    }

    public function testWordWrap(): void
    {
        $string   = 'Here is a simple string of text that will help us demonstrate this function.';
        $expected = "Here is a simple string\nof text that will help us\ndemonstrate this\nfunction.";
        $this->assertSame(substr_count(word_wrap($string, 25), "\n"), 3);
        $this->assertSame($expected, word_wrap($string, 25));

        $string2   = "Here is a\nbroken up sentence\rspanning lines\r\nwoohoo!";
        $expected2 = "Here is a\nbroken up sentence\nspanning lines\nwoohoo!";
        $this->assertSame(substr_count(word_wrap($string2, 25), "\n"), 3);
        $this->assertSame($expected2, word_wrap($string2, 25));

        $string3   = "Here is another slightly longer\nbroken up sentence\rspanning lines\r\nwoohoo!";
        $expected3 = "Here is another slightly\nlonger\nbroken up sentence\nspanning lines\nwoohoo!";
        $this->assertSame(substr_count(word_wrap($string3, 25), "\n"), 4);
        $this->assertSame($expected3, word_wrap($string3, 25));
    }

    public function testWordWrapUnwrap(): void
    {
        $string   = 'Here is a {unwrap}simple string of text{/unwrap} that will help us demonstrate this function.';
        $expected = "Here is a simple string of text\nthat will help us\ndemonstrate this\nfunction.";
        $this->assertSame(substr_count(word_wrap($string, 25), "\n"), 3);
        $this->assertSame($expected, word_wrap($string, 25));
    }

    public function testWordWrapLongWords(): void
    {
        // the really really long word will be split
        $string   = 'Here is an unbelievable super-complicated and reallyreallyquiteextraordinarily sophisticated sentence.';
        $expected = "Here is an unbelievable\nsuper-complicated and\nreallyreallyquiteextraor\ndinarily\nsophisticated sentence.";
        $this->assertSame($expected, word_wrap($string, 25));
    }

    public function testWordWrapURL(): void
    {
        // the really really long word will be split
        $string   = 'Here is an unbelievable super-complicated and http://www.reallyreallyquiteextraordinarily.com sophisticated sentence.';
        $expected = "Here is an unbelievable\nsuper-complicated and\nhttp://www.reallyreallyquiteextraordinarily.com\nsophisticated sentence.";
        $this->assertSame($expected, word_wrap($string, 25));
    }

    public function testDefaultWordWrapCharlim(): void
    {
        $string = 'Here is a longer string of text that will help us demonstrate the default charlim of this function.';
        $this->assertSame(strpos(word_wrap($string), "\n"), 73);
    }

    public function testExcerpt(): void
    {
        $string = $this->_long_string;
        $result = ' Once upon a time, a framework had no tests. It sad  So some nice people began to write tests. The more time that went on, the happier it became. ...';
        $this->assertSame(excerpt($string), $result);
    }

    public function testExcerptRadius(): void
    {
        $string = $this->_long_string;
        $phrase = 'began';
        $result = '... people began to ...';
        $this->assertSame(excerpt($string, $phrase, 10), $result);
    }

    public function testAlternator(): void
    {
        $phrase = ' scream! ';
        $result = '';
        alternator();

        for ($i = 0; $i < 4; $i++) {
            $result .= alternator('I', 'you', 'we') . $phrase;
        }
        $this->assertSame('I scream! you scream! we scream! I scream! ', $result);
    }

    public function testEmptyAlternator(): void
    {
        $phrase = ' scream! ';
        $result = '';

        for ($i = 0; $i < 4; $i++) {
            $result .= alternator() . $phrase;
        }

        $this->assertSame(' scream!  scream!  scream!  scream! ', $result);
    }
}
