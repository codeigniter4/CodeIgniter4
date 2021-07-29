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
use Tests\Support\Validation\TestRules;

/**
 * @internal
 */
final class CreditCardRulesTest extends CIUnitTestCase
{
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

    // Credit Card Rules

    /**
     * @dataProvider creditCardProvider
     *
     * @param string      $type
     * @param string|null $number
     * @param bool        $expected
     */
    public function testValidCCNumber($type, $number, $expected = false)
    {
        $data = [
            'cc' => $number,
        ];

        $this->validation->setRules([
            'cc' => "valid_cc_number[{$type}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    /**
     * Cards shown are test cards found around the web.
     *
     * @see https://www.paypalobjects.com/en_US/vhelp/paypalmanager_help/credit_card_numbers.htm
     *
     * @return array
     */
    public function creditCardProvider()
    {
        return [
            [
                'amex',
                null,
                false,
            ],
            [
                'amex',
                $this->generateCardNum('37', 16),
                false,
            ],
            [
                'shorty',
                '1111 1111 1111 1111',
                false,
            ],
            [
                'amex',
                '',
                false,
            ],
            [
                'amex',
                'abcd efgh ijkl mnop',
                false,
            ],
            [
                'amex',
                '3782 8224 6310 0051',
                false,
            ],
            [
                'amex',
                '3582 8224 6310 0051',
                false,
            ],
            [
                'amex',
                '3782 8224 6310 005',
                true,
            ],
            [
                'amex',
                '3714 4963 5398 431',
                true,
            ],
            [
                'dinersclub',
                '3056 9309 0259 04',
                true,
            ],
            [
                'dinersclub',
                '3852 0000 0232 37',
                true,
            ],
            [
                'discover',
                '6011 1111 1111 1117',
                true,
            ],
            [
                'discover',
                '6011 0009 9013 9424',
                true,
            ],
            [
                'jcb',
                '3530 1113 3330 0000',
                true,
            ],
            [
                'jcb',
                '3566 0020 2036 0505',
                true,
            ],
            [
                'mastercard',
                '5555 5555 5555 4444',
                true,
            ],
            [
                'mastercard',
                '5105 1051 0510 5100',
                true,
            ],
            [
                'visa',
                '4111 1111 1111 1111',
                true,
            ],
            [
                'visa',
                '4012 8888 8888 1881',
                true,
            ],
            [
                'visa',
                '4222 2222 2222 2',
                true,
            ],
            [
                'dankort',
                '5019 7170 1010 3742',
                true,
            ],
            [
                'unionpay',
                $this->generateCardNum(62, 16),
                true,
            ],
            [
                'unionpay',
                $this->generateCardNum(62, 17),
                true,
            ],
            [
                'unionpay',
                $this->generateCardNum(62, 18),
                true,
            ],
            [
                'unionpay',
                $this->generateCardNum(62, 19),
                true,
            ],
            [
                'unionpay',
                $this->generateCardNum(63, 19),
                false,
            ],
            [
                'carteblanche',
                $this->generateCardNum(300, 14),
                true,
            ],
            [
                'carteblanche',
                $this->generateCardNum(301, 14),
                true,
            ],
            [
                'carteblanche',
                $this->generateCardNum(302, 14),
                true,
            ],
            [
                'carteblanche',
                $this->generateCardNum(303, 14),
                true,
            ],
            [
                'carteblanche',
                $this->generateCardNum(304, 14),
                true,
            ],
            [
                'carteblanche',
                $this->generateCardNum(305, 14),
                true,
            ],
            [
                'carteblanche',
                $this->generateCardNum(306, 14),
                false,
            ],
            [
                'dinersclub',
                $this->generateCardNum(300, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(301, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(302, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(303, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(304, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(305, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(309, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(36, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(38, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(39, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(54, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(55, 14),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(300, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(301, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(302, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(303, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(304, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(305, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(309, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(36, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(38, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(39, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(54, 16),
                true,
            ],
            [
                'dinersclub',
                $this->generateCardNum(55, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(6011, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(622, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(644, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(645, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(656, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(647, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(648, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(649, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(65, 16),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(6011, 19),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(622, 19),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(644, 19),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(645, 19),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(656, 19),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(647, 19),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(648, 19),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(649, 19),
                true,
            ],
            [
                'discover',
                $this->generateCardNum(65, 19),
                true,
            ],
            [
                'interpayment',
                $this->generateCardNum(4, 16),
                true,
            ],
            [
                'interpayment',
                $this->generateCardNum(4, 17),
                true,
            ],
            [
                'interpayment',
                $this->generateCardNum(4, 18),
                true,
            ],
            [
                'interpayment',
                $this->generateCardNum(4, 19),
                true,
            ],
            [
                'jcb',
                $this->generateCardNum(352, 16),
                true,
            ],
            [
                'jcb',
                $this->generateCardNum(353, 16),
                true,
            ],
            [
                'jcb',
                $this->generateCardNum(354, 16),
                true,
            ],
            [
                'jcb',
                $this->generateCardNum(355, 16),
                true,
            ],
            [
                'jcb',
                $this->generateCardNum(356, 16),
                true,
            ],
            [
                'jcb',
                $this->generateCardNum(357, 16),
                true,
            ],
            [
                'jcb',
                $this->generateCardNum(358, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(50, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(56, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(57, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(58, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(59, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(60, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(61, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(62, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(63, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(64, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(65, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(66, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(67, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(68, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(69, 12),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(50, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(56, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(57, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(58, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(59, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(60, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(61, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(62, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(63, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(64, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(65, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(66, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(67, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(68, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(69, 13),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(50, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(56, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(57, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(58, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(59, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(60, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(61, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(62, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(63, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(64, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(65, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(66, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(67, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(68, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(69, 14),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(50, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(56, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(57, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(58, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(59, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(60, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(61, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(62, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(63, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(64, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(65, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(66, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(67, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(68, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(69, 15),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(50, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(56, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(57, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(58, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(59, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(60, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(61, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(62, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(63, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(64, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(65, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(66, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(67, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(68, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(69, 16),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(50, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(56, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(57, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(58, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(59, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(60, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(61, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(62, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(63, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(64, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(65, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(66, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(67, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(68, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(69, 18),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(50, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(56, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(57, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(58, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(59, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(60, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(61, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(62, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(63, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(64, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(65, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(66, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(67, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(68, 19),
                true,
            ],
            [
                'maestro',
                $this->generateCardNum(69, 19),
                true,
            ],
            [
                'dankort',
                $this->generateCardNum(5019, 16),
                true,
            ],
            [
                'dankort',
                $this->generateCardNum(4175, 16),
                true,
            ],
            [
                'dankort',
                $this->generateCardNum(4571, 16),
                true,
            ],
            [
                'dankort',
                $this->generateCardNum(4, 16),
                true,
            ],
            [
                'mir',
                $this->generateCardNum(2200, 16),
                true,
            ],
            [
                'mir',
                $this->generateCardNum(2201, 16),
                true,
            ],
            [
                'mir',
                $this->generateCardNum(2202, 16),
                true,
            ],
            [
                'mir',
                $this->generateCardNum(2203, 16),
                true,
            ],
            [
                'mir',
                $this->generateCardNum(2204, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(51, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(52, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(53, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(54, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(55, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(22, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(23, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(24, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(25, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(26, 16),
                true,
            ],
            [
                'mastercard',
                $this->generateCardNum(27, 16),
                true,
            ],
            [
                'visa',
                $this->generateCardNum(4, 13),
                true,
            ],
            [
                'visa',
                $this->generateCardNum(4, 16),
                true,
            ],
            [
                'visa',
                $this->generateCardNum(4, 19),
                true,
            ],
            [
                'uatp',
                $this->generateCardNum(1, 15),
                true,
            ],
            [
                'verve',
                $this->generateCardNum(506, 16),
                true,
            ],
            [
                'verve',
                $this->generateCardNum(650, 16),
                true,
            ],
            [
                'verve',
                $this->generateCardNum(506, 19),
                true,
            ],
            [
                'verve',
                $this->generateCardNum(650, 19),
                true,
            ],
            [
                'cibc',
                $this->generateCardNum(4506, 16),
                true,
            ],
            [
                'rbc',
                $this->generateCardNum(45, 16),
                true,
            ],
            [
                'tdtrust',
                $this->generateCardNum(589297, 16),
                true,
            ],
            [
                'scotia',
                $this->generateCardNum(4536, 16),
                true,
            ],
            [
                'bmoabm',
                $this->generateCardNum(500, 16),
                true,
            ],
            [
                'hsbc',
                $this->generateCardNum(56, 16),
                true,
            ],
            [
                'hsbc',
                $this->generateCardNum(57, 16),
                false,
            ],
        ];
    }

    /**
     * Used to generate fake credit card numbers that will still pass the Luhn
     * check used to validate the card so we can be sure the cards are recognized correctly.
     *
     * @return string
     */
    protected function generateCardNum(int $prefix, int $length)
    {
        $pos        = mb_strlen($prefix);
        $finalDigit = 0;
        $sum        = 0;

        // Fill in the first values of the string based on $prefix
        $string = str_split($prefix);

        // Pad out the array to the appropriate length
        $string = array_pad($string, $length, 0);

        // Fill all of the remaining values with random numbers, except the last one.
        while ($pos < $length - 1) {
            $string[$pos++] = random_int(0, 9);
        }

        // Calculate the Luhn checksum of the current values.
        $lenOffset = ($length + 1) % 2;

        for ($pos = 0; $pos < $length - 1; $pos++) {
            if (($pos + $lenOffset) % 2) {
                $temp = $string[$pos] * 2;
                if ($temp > 9) {
                    $temp -= 9;
                }

                $sum += $temp;
            } else {
                $sum += $string[$pos];
            }
        }

        // Make the last number whatever would cause the entire number to pass the checksum
        $finalDigit          = (10 - ($sum % 10)) % 10;
        $string[$length - 1] = $finalDigit;

        return implode('', $string);
    }
}
