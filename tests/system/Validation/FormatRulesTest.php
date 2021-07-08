<?php

namespace CodeIgniter\Validation;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 */
final class FormatRulesTest extends CIUnitTestCase
{
    public const ALPHABET     = 'abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ';
    public const ALPHANUMERIC = 'abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * @var Validation
     */
    protected $validation;
    protected $config = [
        'ruleSets' => [
            Rules::class,
            FormatRules::class,
            FileRules::class,
            CreditCardRules::class,
            TestRules::class,
        ],
        'groupA' => [
            'foo' => 'required|min_length[5]',
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->validation = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();

        $_FILES = [];
    }

    public function testRegexMatch()
    {
        $data = [
            'foo'   => 'abcde',
            'phone' => '0987654321',
        ];

        $this->validation->setRules([
            'foo'   => 'regex_match[/[a-z]/]',
            'phone' => 'regex_match[/^(01[2689]|09)[0-9]{8}$/]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    public function testRegexMatchFalse()
    {
        $data = [
            'foo'   => 'abcde',
            'phone' => '09876543214',
        ];

        $this->validation->setRules([
            'foo'   => 'regex_match[\d]',
            'phone' => 'regex_match[/^(01[2689]|09)[0-9]{8}$/]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    /**
     * @dataProvider urlProvider
     */
    public function testValidURL(?string $url, bool $expected)
    {
        $data = [
            'foo' => $url,
        ];

        $this->validation->setRules([
            'foo' => 'valid_url',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function urlProvider()
    {
        return [
            [
                'www.codeigniter.com',
                true,
            ],
            [
                'http://codeigniter.com',
                true,
            ],
            //https://bugs.php.net/bug.php?id=51192
            [
                'http://accept-dashes.tld',
                true,
            ],
            [
                'http://reject_underscores',
                false,
            ],
            // https://github.com/codeigniter4/CodeIgniter/issues/4415
            [
                'http://[::1]/ipv6',
                true,
            ],
            [
                'htt://www.codeigniter.com',
                false,
            ],
            [
                '',
                false,
            ],
            [
                'code igniter',
                false,
            ],
            [
                null,
                false,
            ],
            [
                'http://',
                true,
            ], // this is apparently valid!
            [
                'http:///oops.com',
                false,
            ],
            [
                '123.com',
                true,
            ],
            [
                'abc.123',
                true,
            ],
            [
                'http:8080//abc.com',
                true,
            ],
        ];
    }

    /**
     * @dataProvider emailProviderSingle
     *
     * @param $email
     * @param $expected
     */
    public function testValidEmail($email, $expected)
    {
        $data = [
            'foo' => $email,
        ];

        $this->validation->setRules([
            'foo' => 'valid_email',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    /**
     * @dataProvider emailsProvider
     *
     * @param $email
     * @param $expected
     */
    public function testValidEmails($email, $expected)
    {
        $data = [
            'foo' => $email,
        ];

        $this->validation->setRules([
            'foo' => 'valid_emails',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function emailProviderSingle()
    {
        return [
            [
                'email@sample.com',
                true,
            ],
            [
                'valid_email',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    public function emailsProvider()
    {
        return [
            [
                '1@sample.com,2@sample.com',
                true,
            ],
            [
                '1@sample.com, 2@sample.com',
                true,
            ],
            [
                'email@sample.com',
                true,
            ],
            [
                '@sample.com,2@sample.com,validemail@email.ca',
                false,
            ],
            [
                null,
                false,
            ],
            [
                ',',
                false,
            ],
        ];
    }

    /**
     * @dataProvider ipProvider
     *
     * @param $ip
     * @param $which
     * @param $expected
     */
    public function testValidIP($ip, $which, $expected)
    {
        $data = [
            'foo' => $ip,
        ];

        $this->validation->setRules([
            'foo' => "valid_ip[{$which}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function ipProvider()
    {
        return [
            [
                '127.0.0.1',
                null,
                true,
            ],
            [
                '127.0.0.1',
                'ipv4',
                true,
            ],
            [
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                null,
                true,
            ],
            [
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                'ipv6',
                true,
            ],
            [
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                'ipv4',
                false,
            ],
            [
                '127.0.0.1',
                'ipv6',
                false,
            ],
            [
                'H001:0db8:85a3:0000:0000:8a2e:0370:7334',
                null,
                false,
            ],
            [
                '127.0.0.259',
                null,
                false,
            ],
            [
                null,
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider stringProvider
     *
     * @param $str
     * @param $expected
     */
    public function testString($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'string',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function stringProvider()
    {
        return [
            [
                '123',
                true,
            ],
            [
                123,
                false,
            ],
            [
                'hello',
                true,
            ],
        ];
    }

    /**
     * @dataProvider alphaProvider
     *
     * @param $str
     * @param $expected
     */
    public function testAlpha($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function alphaProvider()
    {
        return [
            [
                self::ALPHABET,
                true,
            ],
            [
                self::ALPHABET . ' ',
                false,
            ],
            [
                self::ALPHABET . '1',
                false,
            ],
            [
                self::ALPHABET . '*',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * Test alpha with spaces.
     *
     * @param mixed $value    Value.
     * @param bool  $expected Expected.
     *
     * @dataProvider alphaSpaceProvider
     */
    public function testAlphaSpace($value, $expected)
    {
        $data = [
            'foo' => $value,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_space',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function alphaSpaceProvider()
    {
        return [
            [
                null,
                true,
            ],
            [
                self::ALPHABET,
                true,
            ],
            [
                self::ALPHABET . ' ',
                true,
            ],
            [
                self::ALPHABET . "\n",
                false,
            ],
            [
                self::ALPHABET . '1',
                false,
            ],
            [
                self::ALPHABET . '*',
                false,
            ],
        ];
    }

    /**
     * @dataProvider alphaNumericProvider
     *
     * @param $str
     * @param $expected
     */
    public function testAlphaNumeric($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_numeric',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function alphaNumericProvider()
    {
        return [
            [
                self::ALPHANUMERIC,
                true,
            ],
            [
                self::ALPHANUMERIC . '\ ',
                false,
            ],
            [
                self::ALPHANUMERIC . '_',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider alphaNumericPunctProvider
     *
     * @param $str
     * @param $expected
     */
    public function testAlphaNumericPunct($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_numeric_punct',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function alphaNumericPunctProvider()
    {
        return [
            [
                self::ALPHANUMERIC . ' ~!#$%&*-_+=|:.',
                true,
            ],
            [
                self::ALPHANUMERIC . '`',
                false,
            ],
            [
                self::ALPHANUMERIC . "\n",
                false,
            ],
            [
                self::ALPHANUMERIC . '@',
                false,
            ],
            [
                self::ALPHANUMERIC . '^',
                false,
            ],
            [
                self::ALPHANUMERIC . '(',
                false,
            ],
            [
                self::ALPHANUMERIC . ')',
                false,
            ],
            [
                self::ALPHANUMERIC . '\\',
                false,
            ],
            [
                self::ALPHANUMERIC . '{',
                false,
            ],
            [
                self::ALPHANUMERIC . '}',
                false,
            ],
            [
                self::ALPHANUMERIC . '[',
                false,
            ],
            [
                self::ALPHANUMERIC . ']',
                false,
            ],
            [
                self::ALPHANUMERIC . '"',
                false,
            ],
            [
                self::ALPHANUMERIC . "'",
                false,
            ],
            [
                self::ALPHANUMERIC . '<',
                false,
            ],
            [
                self::ALPHANUMERIC . '>',
                false,
            ],
            [
                self::ALPHANUMERIC . '/',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider alphaNumericProvider
     *
     * @param $str
     * @param $expected
     */
    public function testAlphaNumericSpace($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_numeric_space',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function alphaNumericSpaceProvider()
    {
        return [
            [
                ' ' . self::ALPHANUMERIC,
                true,
            ],
            [
                ' ' . self::ALPHANUMERIC . '-',
                false,
            ],
            [
                ' ' . self::ALPHANUMERIC . "\n",
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider alphaDashProvider
     *
     * @param $str
     * @param $expected
     */
    public function testAlphaDash($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_dash',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function alphaDashProvider()
    {
        return [
            [
                self::ALPHANUMERIC . '-',
                true,
            ],
            [
                self::ALPHANUMERIC . '-\ ',
                false,
            ],
            [
                self::ALPHANUMERIC . "-\n",
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider hexProvider
     *
     * @param $str
     * @param $expected
     */
    public function testHex($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'hex',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function hexProvider()
    {
        return [
            [
                'abcdefABCDEF0123456789',
                true,
            ],
            [
                self::ALPHANUMERIC,
                false,
            ],
            [
                'asdfjkl;',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider numericProvider
     *
     * @param $str
     * @param $expected
     */
    public function testNumeric($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'numeric',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function numericProvider()
    {
        return [
            [
                '0',
                true,
            ],
            [
                '12314',
                true,
            ],
            [
                '-42',
                true,
            ],
            [
                '+42',
                true,
            ],
            [
                "+42\n",
                false,
            ],
            [
                '123a',
                false,
            ],
            [
                '--1',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider integerProvider
     *
     * @param $str
     * @param $expected
     */
    public function testInteger($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'integer',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function integerProvider()
    {
        return [
            [
                '0',
                true,
            ],
            [
                '42',
                true,
            ],
            [
                '-1',
                true,
            ],
            [
                "+42\n",
                false,
            ],
            [
                '123a',
                false,
            ],
            [
                '1.9',
                false,
            ],
            [
                '--1',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider decimalProvider
     *
     * @param $str
     * @param $expected
     */
    public function testDecimal($str, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'decimal',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function decimalProvider()
    {
        return [
            [
                '1.0',
                true,
            ],
            [
                '-0.98',
                true,
            ],
            [
                '0',
                true,
            ],
            [
                "0\n",
                false,
            ],
            [
                '1.0a',
                false,
            ],
            [
                '-i',
                false,
            ],
            [
                '--1',
                false,
            ],
            [
                null,
                false,
            ],
            [
                '.25',
                true,
            ],
        ];
    }

    /**
     * @dataProvider naturalProvider
     *
     * @param $str
     * @param $expected
     */
    public function testNatural($first, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => 'is_natural',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function naturalProvider()
    {
        return [
            [
                '0',
                true,
            ],
            [
                '12',
                true,
            ],
            [
                '42a',
                false,
            ],
            [
                '-1',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider naturalZeroProvider
     *
     * @param $str
     * @param $expected
     */
    public function testNaturalNoZero($first, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => 'is_natural_no_zero',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function naturalZeroProvider()
    {
        return [
            [
                '0',
                false,
            ],
            [
                '12',
                true,
            ],
            [
                '42a',
                false,
            ],
            [
                '-1',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider base64Provider
     *
     * @param $str
     * @param $expected
     */
    public function testBase64($first, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => 'valid_base64',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function base64Provider()
    {
        return [
            [
                base64_encode('string'),
                true,
            ],
            [
                'FA08GG',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider jsonProvider
     *
     * @param $str
     * @param $expected
     */
    public function testJson($first, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => 'valid_json',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function jsonProvider()
    {
        return [
            [
                'null',
                true,
            ],
            [
                '"null"',
                true,
            ],
            [
                '600100825',
                true,
            ],
            [
                '{"A":"Yay.", "B":[0,5]}',
                true,
            ],
            [
                '[0,"2",2.2,"3.3"]',
                true,
            ],
            [
                null,
                false,
            ],
            [
                '600-Nope. Should not pass.',
                false,
            ],
            [
                '{"A":SHOULD_NOT_PASS}',
                false,
            ],
            [
                '[0,"2",2.2 "3.3"]',
                false,
            ],
        ];
    }

    /**
     * @dataProvider timezoneProvider
     *
     * @param $value
     * @param $expected
     */
    public function testTimeZone($value, $expected)
    {
        $data = [
            'foo' => $value,
        ];

        $this->validation->setRules([
            'foo' => 'timezone',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function timezoneProvider()
    {
        return [
            [
                'America/Chicago',
                true,
            ],
            [
                'america/chicago',
                false,
            ],
            [
                'foo/bar',
                false,
            ],
            [
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider validDateProvider
     *
     * @param $str
     * @param $format
     * @param $expected
     */
    public function testValidDate($str, $format, $expected)
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => "valid_date[{$format}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function validDateProvider()
    {
        return [
            [
                'Sun',
                'D',
                true,
            ],
            [
                'Sun',
                'd',
                false,
            ],
            [
                'Sun',
                null,
                true,
            ],
            [
                '1500',
                'Y',
                true,
            ],
            [
                '1500',
                'y',
                false,
            ],
            [
                '1500',
                null,
                true,
            ],
            [
                '09:26:05',
                'H:i:s',
                true,
            ],
            [
                '09:26:5',
                'H:i:s',
                false,
            ],
            [
                '1992-02-29',
                'Y-m-d',
                true,
            ],
            [
                '1991-02-29',
                'Y-m-d',
                false,
            ],
            [
                '1718-05-10 15:25:59',
                'Y-m-d H:i:s',
                true,
            ],
            [
                '1718-05-10 15:5:59',
                'Y-m-d H:i:s',
                false,
            ],
            [
                'Thu, 31 Oct 2013 13:31:00',
                'D, d M Y H:i:s',
                true,
            ],
            [
                'Thu, 31 Jun 2013 13:31:00',
                'D, d M Y H:i:s',
                false,
            ],
            [
                'Thu, 31 Jun 2013 13:31:00',
                null,
                true,
            ],
            [
                '07.05.03',
                'm.d.y',
                true,
            ],
            [
                '07.05.1803',
                'm.d.y',
                false,
            ],
            [
                '19890109',
                'Ymd',
                true,
            ],
            [
                '198919',
                'Ymd',
                false,
            ],
            [
                '2, 7, 2001',
                'j, n, Y',
                true,
            ],
            [
                '2, 17, 2001',
                'j, n, Y',
                false,
            ],
            [
                '09-42-25, 12-11-17',
                'h-i-s, j-m-y',
                true,
            ],
            [
                '09-42-25, 12-00-17',
                'h-i-s, j-m-y',
                false,
            ],
            [
                '09-42-25, 12-00-17',
                null,
                false,
            ],
            [
                'November 12, 2017, 9:42 am',
                'F j, Y, g:i a',
                true,
            ],
            [
                'November 12, 2017, 19:42 am',
                'F j, Y, g:i a',
                false,
            ],
            [
                'November 12, 2017, 9:42 am',
                null,
                true,
            ],
            [
                'Monday 8th of August 2005 03:12:46 PM',
                'l jS \of F Y h:i:s A',
                true,
            ],
            [
                'Monday 8th of August 2005 13:12:46 PM',
                'l jS \of F Y h:i:s A',
                false,
            ],
            [
                '23:01:59 is now',
                'H:m:s \i\s \n\o\w',
                true,
            ],
            [
                '23:01:59 is now',
                'H:m:s is now',
                false,
            ],
            [
                '12/11/2017',
                'd/m/Y',
                true,
            ],
        ];
    }
}
