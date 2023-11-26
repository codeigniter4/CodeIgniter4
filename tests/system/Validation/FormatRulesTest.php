<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Generator;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @group Others
 *
 * @no-final
 */
class FormatRulesTest extends CIUnitTestCase
{
    public const ALPHABET     = 'abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ';
    public const ALPHANUMERIC = 'abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789';

    protected Validation $validation;
    protected array $config = [
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
    }

    public function testRegexMatch(): void
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

    public function testRegexMatchFalse(): void
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
     * @dataProvider provideValidUrl
     */
    public function testValidURL(?string $url, bool $isLoose, bool $isStrict): void
    {
        $data = [
            'foo' => $url,
        ];

        $this->validation->setRules([
            'foo' => 'valid_url',
        ]);

        $this->assertSame($isLoose, $this->validation->run($data));
    }

    /**
     * @dataProvider provideValidUrl
     */
    public function testValidURLStrict(?string $url, bool $isLoose, bool $isStrict): void
    {
        $data = [
            'foo' => $url,
        ];

        $this->validation->setRules([
            'foo' => 'valid_url_strict',
        ]);

        $this->assertSame($isStrict, $this->validation->run($data));
    }

    public function testValidURLStrictWithSchema(): void
    {
        $data = [
            'foo' => 'http://www.codeigniter.com',
        ];

        $this->validation->setRules([
            'foo' => 'valid_url_strict[https]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    public static function provideValidUrl(): iterable
    {
        yield from [
            [
                'www.codeigniter.com',
                true,
                false,
            ],
            [
                'http://codeigniter.com',
                true,
                true,
            ],
            // https://bugs.php.net/bug.php?id=51192
            [
                'http://accept-dashes.tld',
                true,
                true,
            ],
            [
                'http://reject_underscores',
                false,
                false,
            ],
            // https://github.com/bcit-ci/CodeIgniter/issues/4415
            [
                'http://[::1]/ipv6',
                true,
                true,
            ],
            [
                'htt://www.codeigniter.com',
                false,
                false,
            ],
            [
                '',
                false,
                false,
            ],
            // https://github.com/codeigniter4/CodeIgniter4/issues/3156
            [
                'codeigniter',
                true,   // What?
                false,
            ],
            [
                'code igniter',
                false,
                false,
            ],
            [
                null,
                false,
                false,
            ],
            [
                'http://',
                true,   // Why?
                false,
            ],
            [
                'http:///oops.com',
                false,
                false,
            ],
            [
                '123.com',
                true,
                false,
            ],
            [
                'abc.123',
                true,
                false,
            ],
            [
                'http:8080//abc.com',
                true,   // Insane?
                false,
            ],
            [
                'mailto:support@codeigniter.com',
                true,
                false,
            ],
            [
                '//example.com',
                false,
                false,
            ],
            [
                "http://www.codeigniter.com\n",
                false,
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideValidEmail
     */
    public function testValidEmail(?string $email, bool $expected): void
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
     * @dataProvider provideValidEmails
     */
    public function testValidEmails(?string $email, bool $expected): void
    {
        $data = [
            'foo' => $email,
        ];

        $this->validation->setRules([
            'foo' => 'valid_emails',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideValidEmail(): iterable
    {
        yield from [
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

    public static function provideValidEmails(): iterable
    {
        yield from [
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
     * @dataProvider provideValidIP
     */
    public function testValidIP(?string $ip, ?string $which, bool $expected): void
    {
        $data = [
            'foo' => $ip,
        ];

        $this->validation->setRules([
            'foo' => "valid_ip[{$which}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideValidIP(): iterable
    {
        yield from [
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
     * @dataProvider provideString
     *
     * @param int|string $str
     */
    public function testString($str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'string',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideString(): iterable
    {
        yield from [
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
     * @dataProvider provideAlpha
     */
    public function testAlpha(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideAlpha(): iterable
    {
        yield from [
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
     * @dataProvider provideAlphaSpace
     */
    public function testAlphaSpace(?string $value, bool $expected): void
    {
        $data = [
            'foo' => $value,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_space',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideAlphaSpace(): iterable
    {
        yield from [
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
     */
    public function testAlphaNumeric(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_numeric',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function alphaNumericProvider(): iterable
    {
        yield from [
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
     * @dataProvider provideAlphaNumericPunct
     */
    public function testAlphaNumericPunct(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_numeric_punct',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideAlphaNumericPunct(): iterable
    {
        yield from [
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
     */
    public function testAlphaNumericSpace(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_numeric_space',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public function alphaNumericSpaceProvider(): Generator
    {
        yield from [
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
     * @dataProvider provideAlphaDash
     */
    public function testAlphaDash(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'alpha_dash',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideAlphaDash(): iterable
    {
        yield from [
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
     * @dataProvider provideHex
     */
    public function testHex(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'hex',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideHex(): iterable
    {
        yield from [
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
     * @dataProvider provideNumeric
     */
    public function testNumeric(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'numeric',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideNumeric(): iterable
    {
        yield from [
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
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5374
     *
     * @dataProvider provideInvalidIntegerType
     *
     * @param mixed $value
     */
    public function testIntegerWithInvalidTypeData($value, bool $expected): void
    {
        $this->validation->setRules([
            'foo' => 'integer',
        ]);

        $data = [
            'foo' => $value,
        ];
        $this->assertsame($expected, $this->validation->run($data));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5374
     *
     * @dataProvider provideInvalidIntegerType
     *
     * @param mixed $value
     */
    public function testNumericWithInvalidTypeData($value, bool $expected): void
    {
        $this->validation->setRules([
            'foo' => 'numeric',
        ]);

        $data = [
            'foo' => $value,
        ];
        $this->assertsame($expected, $this->validation->run($data));
    }

    public static function provideInvalidIntegerType(): iterable
    {
        // TypeError : CodeIgniter\Validation\FormatRules::integer(): Argument #1 ($str) must be of type ?string, array given
        // yield 'array with int' => [
        // [555],
        // false,
        // ];

        // TypeError : CodeIgniter\Validation\FormatRules::integer(): Argument #1 ($str) must be of type ?string, array given
        // yield 'empty array' => [
        // [],
        // false,
        // ];

        yield 'bool true' => [
            true,
            true,  // incorrect
        ];

        yield 'bool false' => [
            false,
            false,
        ];

        yield 'null' => [
            null,
            false,
        ];
    }

    /**
     * @dataProvider provideInteger
     */
    public function testInteger(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'integer',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideInteger(): iterable
    {
        yield from [
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
     * @dataProvider provideDecimal
     */
    public function testDecimal(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'decimal',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideDecimal(): iterable
    {
        yield from [
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
     * @dataProvider provideNatural
     */
    public function testNatural(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'is_natural',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideNatural(): iterable
    {
        yield from [
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
     * @dataProvider provideNaturalNoZero
     */
    public function testNaturalNoZero(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'is_natural_no_zero',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideNaturalNoZero(): iterable
    {
        yield from [
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
     * @dataProvider provideBase64
     */
    public function testBase64(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'valid_base64',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideBase64(): iterable
    {
        yield from [
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
     * @dataProvider provideJson
     */
    public function testJson(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'valid_json',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideJson(): iterable
    {
        yield from [
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
     * @dataProvider provideTimeZone
     */
    public function testTimeZone(?string $str, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => 'timezone',
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideTimeZone(): iterable
    {
        yield from [
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
     * @dataProvider provideValidDate
     */
    public function testValidDate(?string $str, ?string $format, bool $expected): void
    {
        $data = [
            'foo' => $str,
        ];

        $this->validation->setRules([
            'foo' => "valid_date[{$format}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideValidDate(): iterable
    {
        yield from [
            [
                null,
                'Y-m-d',
                false,
            ],
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
