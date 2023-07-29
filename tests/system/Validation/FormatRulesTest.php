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
     * @dataProvider urlProvider
     */
    public function testValidURL(?string $url, bool $isLoose, bool $isStrict)
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
     * @dataProvider urlProvider
     */
    public function testValidURLStrict(?string $url, bool $isLoose, bool $isStrict)
    {
        $data = [
            'foo' => $url,
        ];

        $this->validation->setRules([
            'foo' => 'valid_url_strict',
        ]);

        $this->assertSame($isStrict, $this->validation->run($data));
    }

    public function testValidURLStrictWithSchema()
    {
        $data = [
            'foo' => 'http://www.codeigniter.com',
        ];

        $this->validation->setRules([
            'foo' => 'valid_url_strict[https]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    public function urlProvider(): iterable
    {
        yield [
            'www.codeigniter.com',
            true,
            false,
        ];

        yield [
            'http://codeigniter.com',
            true,
            true,
        ];

        // https://bugs.php.net/bug.php?id=51192
        yield [
            'http://accept-dashes.tld',
            true,
            true,
        ];

        yield [
            'http://reject_underscores',
            false,
            false,
        ];

        // https://github.com/bcit-ci/CodeIgniter/issues/4415
        yield [
            'http://[::1]/ipv6',
            true,
            true,
        ];

        yield [
            'htt://www.codeigniter.com',
            false,
            false,
        ];

        yield [
            '',
            false,
            false,
        ];

        // https://github.com/codeigniter4/CodeIgniter4/issues/3156
        yield [
            'codeigniter',
            true,   // What?
            false,
        ];

        yield [
            'code igniter',
            false,
            false,
        ];

        yield [
            null,
            false,
            false,
        ];

        yield [
            'http://',
            true,   // Why?
            false,
        ];

        yield [
            'http:///oops.com',
            false,
            false,
        ];

        yield [
            '123.com',
            true,
            false,
        ];

        yield [
            'abc.123',
            true,
            false,
        ];

        yield [
            'http:8080//abc.com',
            true,   // Insane?
            false,
        ];

        yield [
            'mailto:support@codeigniter.com',
            true,
            false,
        ];

        yield [
            '//example.com',
            false,
            false,
        ];

        yield [
            "http://www.codeigniter.com\n",
            false,
            false,
        ];
    }

    /**
     * @dataProvider emailProviderSingle
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
     * @dataProvider emailsProvider
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

    public function emailProviderSingle(): iterable
    {
        yield [
            'email@sample.com',
            true,
        ];

        yield [
            'valid_email',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    public function emailsProvider(): iterable
    {
        yield [
            '1@sample.com,2@sample.com',
            true,
        ];

        yield [
            '1@sample.com, 2@sample.com',
            true,
        ];

        yield [
            'email@sample.com',
            true,
        ];

        yield [
            '@sample.com,2@sample.com,validemail@email.ca',
            false,
        ];

        yield [
            null,
            false,
        ];

        yield [
            ',',
            false,
        ];
    }

    /**
     * @dataProvider ipProvider
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

    public function ipProvider(): iterable
    {
        yield [
            '127.0.0.1',
            null,
            true,
        ];

        yield [
            '127.0.0.1',
            'ipv4',
            true,
        ];

        yield [
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            null,
            true,
        ];

        yield [
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'ipv6',
            true,
        ];

        yield [
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'ipv4',
            false,
        ];

        yield [
            '127.0.0.1',
            'ipv6',
            false,
        ];

        yield [
            'H001:0db8:85a3:0000:0000:8a2e:0370:7334',
            null,
            false,
        ];

        yield [
            '127.0.0.259',
            null,
            false,
        ];

        yield [
            null,
            null,
            false,
        ];
    }

    /**
     * @dataProvider stringProvider
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

    public function stringProvider(): iterable
    {
        yield [
            '123',
            true,
        ];

        yield [
            123,
            false,
        ];

        yield [
            'hello',
            true,
        ];
    }

    /**
     * @dataProvider alphaProvider
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

    public function alphaProvider(): iterable
    {
        yield [
            self::ALPHABET,
            true,
        ];

        yield [
            self::ALPHABET . ' ',
            false,
        ];

        yield [
            self::ALPHABET . '1',
            false,
        ];

        yield [
            self::ALPHABET . '*',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider alphaSpaceProvider
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

    public function alphaSpaceProvider(): iterable
    {
        yield [
            null,
            true,
        ];

        yield [
            self::ALPHABET,
            true,
        ];

        yield [
            self::ALPHABET . ' ',
            true,
        ];

        yield [
            self::ALPHABET . "\n",
            false,
        ];

        yield [
            self::ALPHABET . '1',
            false,
        ];

        yield [
            self::ALPHABET . '*',
            false,
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

    public function alphaNumericProvider(): iterable
    {
        yield [
            self::ALPHANUMERIC,
            true,
        ];

        yield [
            self::ALPHANUMERIC . '\ ',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '_',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider alphaNumericPunctProvider
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

    public function alphaNumericPunctProvider(): iterable
    {
        yield [
            self::ALPHANUMERIC . ' ~!#$%&*-_+=|:.',
            true,
        ];

        yield [
            self::ALPHANUMERIC . '`',
            false,
        ];

        yield [
            self::ALPHANUMERIC . "\n",
            false,
        ];

        yield [
            self::ALPHANUMERIC . '@',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '^',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '(',
            false,
        ];

        yield [
            self::ALPHANUMERIC . ')',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '\\',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '{',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '}',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '[',
            false,
        ];

        yield [
            self::ALPHANUMERIC . ']',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '"',
            false,
        ];

        yield [
            self::ALPHANUMERIC . "'",
            false,
        ];

        yield [
            self::ALPHANUMERIC . '<',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '>',
            false,
        ];

        yield [
            self::ALPHANUMERIC . '/',
            false,
        ];

        yield [
            null,
            false,
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
        yield [
            ' ' . self::ALPHANUMERIC,
            true,
        ];

        yield [
            ' ' . self::ALPHANUMERIC . '-',
            false,
        ];

        yield [
            ' ' . self::ALPHANUMERIC . "\n",
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider alphaDashProvider
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

    public function alphaDashProvider(): iterable
    {
        yield [
            self::ALPHANUMERIC . '-',
            true,
        ];

        yield [
            self::ALPHANUMERIC . '-\ ',
            false,
        ];

        yield [
            self::ALPHANUMERIC . "-\n",
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider hexProvider
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

    public function hexProvider(): iterable
    {
        yield [
            'abcdefABCDEF0123456789',
            true,
        ];

        yield [
            self::ALPHANUMERIC,
            false,
        ];

        yield [
            'asdfjkl;',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider numericProvider
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

    public function numericProvider(): iterable
    {
        yield [
            '0',
            true,
        ];

        yield [
            '12314',
            true,
        ];

        yield [
            '-42',
            true,
        ];

        yield [
            '+42',
            true,
        ];

        yield [
            "+42\n",
            false,
        ];

        yield [
            '123a',
            false,
        ];

        yield [
            '--1',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5374
     *
     * @dataProvider integerInvalidTypeDataProvider
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
     * @dataProvider integerInvalidTypeDataProvider
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

    public function integerInvalidTypeDataProvider(): iterable
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
     * @dataProvider integerProvider
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

    public function integerProvider(): iterable
    {
        yield [
            '0',
            true,
        ];

        yield [
            '42',
            true,
        ];

        yield [
            '-1',
            true,
        ];

        yield [
            "+42\n",
            false,
        ];

        yield [
            '123a',
            false,
        ];

        yield [
            '1.9',
            false,
        ];

        yield [
            '--1',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider decimalProvider
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

    public function decimalProvider(): iterable
    {
        yield [
            '1.0',
            true,
        ];

        yield [
            '-0.98',
            true,
        ];

        yield [
            '0',
            true,
        ];

        yield [
            "0\n",
            false,
        ];

        yield [
            '1.0a',
            false,
        ];

        yield [
            '-i',
            false,
        ];

        yield [
            '--1',
            false,
        ];

        yield [
            null,
            false,
        ];

        yield [
            '.25',
            true,
        ];
    }

    /**
     * @dataProvider naturalProvider
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

    public function naturalProvider(): iterable
    {
        yield [
            '0',
            true,
        ];

        yield [
            '12',
            true,
        ];

        yield [
            '42a',
            false,
        ];

        yield [
            '-1',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider naturalZeroProvider
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

    public function naturalZeroProvider(): iterable
    {
        yield [
            '0',
            false,
        ];

        yield [
            '12',
            true,
        ];

        yield [
            '42a',
            false,
        ];

        yield [
            '-1',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider base64Provider
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

    public function base64Provider(): iterable
    {
        yield [
            base64_encode('string'),
            true,
        ];

        yield [
            'FA08GG',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider jsonProvider
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

    public function jsonProvider(): iterable
    {
        yield [
            'null',
            true,
        ];

        yield [
            '"null"',
            true,
        ];

        yield [
            '600100825',
            true,
        ];

        yield [
            '{"A":"Yay.", "B":[0,5]}',
            true,
        ];

        yield [
            '[0,"2",2.2,"3.3"]',
            true,
        ];

        yield [
            null,
            false,
        ];

        yield [
            '600-Nope. Should not pass.',
            false,
        ];

        yield [
            '{"A":SHOULD_NOT_PASS}',
            false,
        ];

        yield [
            '[0,"2",2.2 "3.3"]',
            false,
        ];
    }

    /**
     * @dataProvider timezoneProvider
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

    public function timezoneProvider(): iterable
    {
        yield [
            'America/Chicago',
            true,
        ];

        yield [
            'america/chicago',
            false,
        ];

        yield [
            'foo/bar',
            false,
        ];

        yield [
            null,
            false,
        ];
    }

    /**
     * @dataProvider validDateProvider
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

    public function validDateProvider(): iterable
    {
        yield [
            null,
            'Y-m-d',
            false,
        ];

        yield [
            'Sun',
            'D',
            true,
        ];

        yield [
            'Sun',
            'd',
            false,
        ];

        yield [
            'Sun',
            null,
            true,
        ];

        yield [
            '1500',
            'Y',
            true,
        ];

        yield [
            '1500',
            'y',
            false,
        ];

        yield [
            '1500',
            null,
            true,
        ];

        yield [
            '09:26:05',
            'H:i:s',
            true,
        ];

        yield [
            '09:26:5',
            'H:i:s',
            false,
        ];

        yield [
            '1992-02-29',
            'Y-m-d',
            true,
        ];

        yield [
            '1991-02-29',
            'Y-m-d',
            false,
        ];

        yield [
            '1718-05-10 15:25:59',
            'Y-m-d H:i:s',
            true,
        ];

        yield [
            '1718-05-10 15:5:59',
            'Y-m-d H:i:s',
            false,
        ];

        yield [
            'Thu, 31 Oct 2013 13:31:00',
            'D, d M Y H:i:s',
            true,
        ];

        yield [
            'Thu, 31 Jun 2013 13:31:00',
            'D, d M Y H:i:s',
            false,
        ];

        yield [
            'Thu, 31 Jun 2013 13:31:00',
            null,
            true,
        ];

        yield [
            '07.05.03',
            'm.d.y',
            true,
        ];

        yield [
            '07.05.1803',
            'm.d.y',
            false,
        ];

        yield [
            '19890109',
            'Ymd',
            true,
        ];

        yield [
            '198919',
            'Ymd',
            false,
        ];

        yield [
            '2, 7, 2001',
            'j, n, Y',
            true,
        ];

        yield [
            '2, 17, 2001',
            'j, n, Y',
            false,
        ];

        yield [
            '09-42-25, 12-11-17',
            'h-i-s, j-m-y',
            true,
        ];

        yield [
            '09-42-25, 12-00-17',
            'h-i-s, j-m-y',
            false,
        ];

        yield [
            '09-42-25, 12-00-17',
            null,
            false,
        ];

        yield [
            'November 12, 2017, 9:42 am',
            'F j, Y, g:i a',
            true,
        ];

        yield [
            'November 12, 2017, 19:42 am',
            'F j, Y, g:i a',
            false,
        ];

        yield [
            'November 12, 2017, 9:42 am',
            null,
            true,
        ];

        yield [
            'Monday 8th of August 2005 03:12:46 PM',
            'l jS \of F Y h:i:s A',
            true,
        ];

        yield [
            'Monday 8th of August 2005 13:12:46 PM',
            'l jS \of F Y h:i:s A',
            false,
        ];

        yield [
            '23:01:59 is now',
            'H:m:s \i\s \n\o\w',
            true,
        ];

        yield [
            '23:01:59 is now',
            'H:m:s is now',
            false,
        ];

        yield [
            '12/11/2017',
            'd/m/Y',
            true,
        ];
    }
}
