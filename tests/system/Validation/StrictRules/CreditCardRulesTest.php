<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Validation;
use Config\Services;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @group Others
 */
final class CreditCardRulesTest extends CIUnitTestCase
{
    private Validation $validation;
    private array $config = [
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

    /**
     * @dataProvider provideValidCCNumber
     */
    public function testValidCCNumber(string $type, ?string $number, bool $expected): void
    {
        $data = ['cc' => $number];
        $this->validation->setRules(['cc' => "valid_cc_number[{$type}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    /**
     * Cards shown are test cards found around the web.
     *
     * @see https://www.paypalobjects.com/en_US/vhelp/paypalmanager_help/credit_card_numbers.htm
     */
    public static function provideValidCCNumber(): iterable
    {
        yield from [
            'null_test' => [
                'amex',
                null,
                false,
            ],
            'random_test' => [
                'amex',
                self::generateCardNumber(37, 16),
                false,
            ],
            'invalid_type' => [
                'shorty',
                '1111 1111 1111 1111',
                false,
            ],
            'invalid_length' => [
                'amex',
                '',
                false,
            ],
            'not_numeric' => [
                'amex',
                'abcd efgh ijkl mnop',
                false,
            ],
            'bad_length' => [
                'amex',
                '3782 8224 6310 0051',
                false,
            ],
            'bad_prefix' => [
                'amex',
                '3582 8224 6310 0051',
                false,
            ],
            'amex1' => [
                'amex',
                '3782 8224 6310 005',
                true,
            ],
            'amex2' => [
                'amex',
                '3714 4963 5398 431',
                true,
            ],
            'dinersclub1' => [
                'dinersclub',
                '3056 9309 0259 04',
                true,
            ],
            'dinersculb2' => [
                'dinersclub',
                '3852 0000 0232 37',
                true,
            ],
            'discover1' => [
                'discover',
                '6011 1111 1111 1117',
                true,
            ],
            'discover2' => [
                'discover',
                '6011 0009 9013 9424',
                true,
            ],
            'jcb8' => [
                'jcb',
                '3530 1113 3330 0000',
                true,
            ],
            'jcb9' => [
                'jcb',
                '3566 0020 2036 0505',
                true,
            ],
            'mastercard12' => [
                'mastercard',
                '5555 5555 5555 4444',
                true,
            ],
            'mastercard13' => [
                'mastercard',
                '5105 1051 0510 5100',
                true,
            ],
            'visa4' => [
                'visa',
                '4111 1111 1111 1111',
                true,
            ],
            'visa5' => [
                'visa',
                '4012 8888 8888 1881',
                true,
            ],
            'visa6' => [
                'visa',
                '4222 2222 2222 2',
                true,
            ],
            'dankort5' => [
                'dankort',
                '5019 7170 1010 3742',
                true,
            ],
            'unionpay1' => [
                'unionpay',
                self::generateCardNumber(62, 16),
                true,
            ],
            'unionpay2' => [
                'unionpay',
                self::generateCardNumber(62, 17),
                true,
            ],
            'unionpay3' => [
                'unionpay',
                self::generateCardNumber(62, 18),
                true,
            ],
            'unionpay4' => [
                'unionpay',
                self::generateCardNumber(62, 19),
                true,
            ],
            'unionpay5' => [
                'unionpay',
                self::generateCardNumber(63, 19),
                false,
            ],
            'carteblanche1' => [
                'carteblanche',
                self::generateCardNumber(300, 14),
                true,
            ],
            'carteblanche2' => [
                'carteblanche',
                self::generateCardNumber(301, 14),
                true,
            ],
            'carteblanche3' => [
                'carteblanche',
                self::generateCardNumber(302, 14),
                true,
            ],
            'carteblanche4' => [
                'carteblanche',
                self::generateCardNumber(303, 14),
                true,
            ],
            'carteblanche5' => [
                'carteblanche',
                self::generateCardNumber(304, 14),
                true,
            ],
            'carteblanche6' => [
                'carteblanche',
                self::generateCardNumber(305, 14),
                true,
            ],
            'carteblanche7' => [
                'carteblanche',
                self::generateCardNumber(306, 14),
                false,
            ],
            'dinersclub3' => [
                'dinersclub',
                self::generateCardNumber(300, 14),
                true,
            ],
            'dinersclub4' => [
                'dinersclub',
                self::generateCardNumber(301, 14),
                true,
            ],
            'dinersclub5' => [
                'dinersclub',
                self::generateCardNumber(302, 14),
                true,
            ],
            'dinersclub6' => [
                'dinersclub',
                self::generateCardNumber(303, 14),
                true,
            ],
            'dinersclub7' => [
                'dinersclub',
                self::generateCardNumber(304, 14),
                true,
            ],
            'dinersclub8' => [
                'dinersclub',
                self::generateCardNumber(305, 14),
                true,
            ],
            'dinersclub9' => [
                'dinersclub',
                self::generateCardNumber(309, 14),
                true,
            ],
            'dinersclub10' => [
                'dinersclub',
                self::generateCardNumber(36, 14),
                true,
            ],
            'dinersclub11' => [
                'dinersclub',
                self::generateCardNumber(38, 14),
                true,
            ],
            'dinersclub12' => [
                'dinersclub',
                self::generateCardNumber(39, 14),
                true,
            ],
            'dinersclub13' => [
                'dinersclub',
                self::generateCardNumber(54, 14),
                true,
            ],
            'dinersclub14' => [
                'dinersclub',
                self::generateCardNumber(55, 14),
                true,
            ],
            'dinersclub15' => [
                'dinersclub',
                self::generateCardNumber(300, 16),
                true,
            ],
            'dinersclub16' => [
                'dinersclub',
                self::generateCardNumber(301, 16),
                true,
            ],
            'dinersclub17' => [
                'dinersclub',
                self::generateCardNumber(302, 16),
                true,
            ],
            'dinersclub18' => [
                'dinersclub',
                self::generateCardNumber(303, 16),
                true,
            ],
            'dinersclub19' => [
                'dinersclub',
                self::generateCardNumber(304, 16),
                true,
            ],
            'dinersclub20' => [
                'dinersclub',
                self::generateCardNumber(305, 16),
                true,
            ],
            'dinersclub21' => [
                'dinersclub',
                self::generateCardNumber(309, 16),
                true,
            ],
            'dinersclub22' => [
                'dinersclub',
                self::generateCardNumber(36, 16),
                true,
            ],
            'dinersclub23' => [
                'dinersclub',
                self::generateCardNumber(38, 16),
                true,
            ],
            'dinersclub24' => [
                'dinersclub',
                self::generateCardNumber(39, 16),
                true,
            ],
            'dinersclub25' => [
                'dinersclub',
                self::generateCardNumber(54, 16),
                true,
            ],
            'dinersclub26' => [
                'dinersclub',
                self::generateCardNumber(55, 16),
                true,
            ],
            'discover3' => [
                'discover',
                self::generateCardNumber(6011, 16),
                true,
            ],
            'discover4' => [
                'discover',
                self::generateCardNumber(622, 16),
                true,
            ],
            'discover5' => [
                'discover',
                self::generateCardNumber(644, 16),
                true,
            ],
            'discover6' => [
                'discover',
                self::generateCardNumber(645, 16),
                true,
            ],
            'discover7' => [
                'discover',
                self::generateCardNumber(656, 16),
                true,
            ],
            'discover8' => [
                'discover',
                self::generateCardNumber(647, 16),
                true,
            ],
            'discover9' => [
                'discover',
                self::generateCardNumber(648, 16),
                true,
            ],
            'discover10' => [
                'discover',
                self::generateCardNumber(649, 16),
                true,
            ],
            'discover11' => [
                'discover',
                self::generateCardNumber(65, 16),
                true,
            ],
            'discover12' => [
                'discover',
                self::generateCardNumber(6011, 19),
                true,
            ],
            'discover13' => [
                'discover',
                self::generateCardNumber(622, 19),
                true,
            ],
            'discover14' => [
                'discover',
                self::generateCardNumber(644, 19),
                true,
            ],
            'discover15' => [
                'discover',
                self::generateCardNumber(645, 19),
                true,
            ],
            'discover16' => [
                'discover',
                self::generateCardNumber(656, 19),
                true,
            ],
            'discover17' => [
                'discover',
                self::generateCardNumber(647, 19),
                true,
            ],
            'discover18' => [
                'discover',
                self::generateCardNumber(648, 19),
                true,
            ],
            'discover19' => [
                'discover',
                self::generateCardNumber(649, 19),
                true,
            ],
            'discover20' => [
                'discover',
                self::generateCardNumber(65, 19),
                true,
            ],
            'interpayment1' => [
                'interpayment',
                self::generateCardNumber(4, 16),
                true,
            ],
            'interpayment2' => [
                'interpayment',
                self::generateCardNumber(4, 17),
                true,
            ],
            'interpayment3' => [
                'interpayment',
                self::generateCardNumber(4, 18),
                true,
            ],
            'interpayment4' => [
                'interpayment',
                self::generateCardNumber(4, 19),
                true,
            ],
            'jcb1' => [
                'jcb',
                self::generateCardNumber(352, 16),
                true,
            ],
            'jcb2' => [
                'jcb',
                self::generateCardNumber(353, 16),
                true,
            ],
            'jcb3' => [
                'jcb',
                self::generateCardNumber(354, 16),
                true,
            ],
            'jcb4' => [
                'jcb',
                self::generateCardNumber(355, 16),
                true,
            ],
            'jcb5' => [
                'jcb',
                self::generateCardNumber(356, 16),
                true,
            ],
            'jcb6' => [
                'jcb',
                self::generateCardNumber(357, 16),
                true,
            ],
            'jcb7' => [
                'jcb',
                self::generateCardNumber(358, 16),
                true,
            ],
            'maestro1' => [
                'maestro',
                self::generateCardNumber(50, 12),
                true,
            ],
            'maestro2' => [
                'maestro',
                self::generateCardNumber(56, 12),
                true,
            ],
            'maestro3' => [
                'maestro',
                self::generateCardNumber(57, 12),
                true,
            ],
            'maestro4' => [
                'maestro',
                self::generateCardNumber(58, 12),
                true,
            ],
            'maestro5' => [
                'maestro',
                self::generateCardNumber(59, 12),
                true,
            ],
            'maestro6' => [
                'maestro',
                self::generateCardNumber(60, 12),
                true,
            ],
            'maestro7' => [
                'maestro',
                self::generateCardNumber(61, 12),
                true,
            ],
            'maestro8' => [
                'maestro',
                self::generateCardNumber(62, 12),
                true,
            ],
            'maestro9' => [
                'maestro',
                self::generateCardNumber(63, 12),
                true,
            ],
            'maestro10' => [
                'maestro',
                self::generateCardNumber(64, 12),
                true,
            ],
            'maestro11' => [
                'maestro',
                self::generateCardNumber(65, 12),
                true,
            ],
            'maestro12' => [
                'maestro',
                self::generateCardNumber(66, 12),
                true,
            ],
            'maestro13' => [
                'maestro',
                self::generateCardNumber(67, 12),
                true,
            ],
            'maestro14' => [
                'maestro',
                self::generateCardNumber(68, 12),
                true,
            ],
            'maestro15' => [
                'maestro',
                self::generateCardNumber(69, 12),
                true,
            ],
            'maestro16' => [
                'maestro',
                self::generateCardNumber(50, 13),
                true,
            ],
            'maestro17' => [
                'maestro',
                self::generateCardNumber(56, 13),
                true,
            ],
            'maestro18' => [
                'maestro',
                self::generateCardNumber(57, 13),
                true,
            ],
            'maestro19' => [
                'maestro',
                self::generateCardNumber(58, 13),
                true,
            ],
            'maestro20' => [
                'maestro',
                self::generateCardNumber(59, 13),
                true,
            ],
            'maestro21' => [
                'maestro',
                self::generateCardNumber(60, 13),
                true,
            ],
            'maestro22' => [
                'maestro',
                self::generateCardNumber(61, 13),
                true,
            ],
            'maestro23' => [
                'maestro',
                self::generateCardNumber(62, 13),
                true,
            ],
            'maestro24' => [
                'maestro',
                self::generateCardNumber(63, 13),
                true,
            ],
            'maestro25' => [
                'maestro',
                self::generateCardNumber(64, 13),
                true,
            ],
            'maestro26' => [
                'maestro',
                self::generateCardNumber(65, 13),
                true,
            ],
            'maestro27' => [
                'maestro',
                self::generateCardNumber(66, 13),
                true,
            ],
            'maestro28' => [
                'maestro',
                self::generateCardNumber(67, 13),
                true,
            ],
            'maestro29' => [
                'maestro',
                self::generateCardNumber(68, 13),
                true,
            ],
            'maestro30' => [
                'maestro',
                self::generateCardNumber(69, 13),
                true,
            ],
            'maestro31' => [
                'maestro',
                self::generateCardNumber(50, 14),
                true,
            ],
            'maestro32' => [
                'maestro',
                self::generateCardNumber(56, 14),
                true,
            ],
            'maestro33' => [
                'maestro',
                self::generateCardNumber(57, 14),
                true,
            ],
            'maestro34' => [
                'maestro',
                self::generateCardNumber(58, 14),
                true,
            ],
            'maestro35' => [
                'maestro',
                self::generateCardNumber(59, 14),
                true,
            ],
            'maestro36' => [
                'maestro',
                self::generateCardNumber(60, 14),
                true,
            ],
            'maestro37' => [
                'maestro',
                self::generateCardNumber(61, 14),
                true,
            ],
            'maestro38' => [
                'maestro',
                self::generateCardNumber(62, 14),
                true,
            ],
            'maestro39' => [
                'maestro',
                self::generateCardNumber(63, 14),
                true,
            ],
            'maestro40' => [
                'maestro',
                self::generateCardNumber(64, 14),
                true,
            ],
            'maestro41' => [
                'maestro',
                self::generateCardNumber(65, 14),
                true,
            ],
            'maestro42' => [
                'maestro',
                self::generateCardNumber(66, 14),
                true,
            ],
            'maestro43' => [
                'maestro',
                self::generateCardNumber(67, 14),
                true,
            ],
            'maestro44' => [
                'maestro',
                self::generateCardNumber(68, 14),
                true,
            ],
            'maestro45' => [
                'maestro',
                self::generateCardNumber(69, 14),
                true,
            ],
            'maestro46' => [
                'maestro',
                self::generateCardNumber(50, 15),
                true,
            ],
            'maestro47' => [
                'maestro',
                self::generateCardNumber(56, 15),
                true,
            ],
            'maestro48' => [
                'maestro',
                self::generateCardNumber(57, 15),
                true,
            ],
            'maestro49' => [
                'maestro',
                self::generateCardNumber(58, 15),
                true,
            ],
            'maestro50' => [
                'maestro',
                self::generateCardNumber(59, 15),
                true,
            ],
            'maestro51' => [
                'maestro',
                self::generateCardNumber(60, 15),
                true,
            ],
            'maestro52' => [
                'maestro',
                self::generateCardNumber(61, 15),
                true,
            ],
            'maestro53' => [
                'maestro',
                self::generateCardNumber(62, 15),
                true,
            ],
            'maestro54' => [
                'maestro',
                self::generateCardNumber(63, 15),
                true,
            ],
            'maestro55' => [
                'maestro',
                self::generateCardNumber(64, 15),
                true,
            ],
            'maestro56' => [
                'maestro',
                self::generateCardNumber(65, 15),
                true,
            ],
            'maestro57' => [
                'maestro',
                self::generateCardNumber(66, 15),
                true,
            ],
            'maestro58' => [
                'maestro',
                self::generateCardNumber(67, 15),
                true,
            ],
            'maestro59' => [
                'maestro',
                self::generateCardNumber(68, 15),
                true,
            ],
            'maestro60' => [
                'maestro',
                self::generateCardNumber(69, 15),
                true,
            ],
            'maestro61' => [
                'maestro',
                self::generateCardNumber(50, 16),
                true,
            ],
            'maestro62' => [
                'maestro',
                self::generateCardNumber(56, 16),
                true,
            ],
            'maestro63' => [
                'maestro',
                self::generateCardNumber(57, 16),
                true,
            ],
            'maestro64' => [
                'maestro',
                self::generateCardNumber(58, 16),
                true,
            ],
            'maestro65' => [
                'maestro',
                self::generateCardNumber(59, 16),
                true,
            ],
            'maestro66' => [
                'maestro',
                self::generateCardNumber(60, 16),
                true,
            ],
            'maestro67' => [
                'maestro',
                self::generateCardNumber(61, 16),
                true,
            ],
            'maestro68' => [
                'maestro',
                self::generateCardNumber(62, 16),
                true,
            ],
            'maestro69' => [
                'maestro',
                self::generateCardNumber(63, 16),
                true,
            ],
            'maestro70' => [
                'maestro',
                self::generateCardNumber(64, 16),
                true,
            ],
            'maestro71' => [
                'maestro',
                self::generateCardNumber(65, 16),
                true,
            ],
            'maestro72' => [
                'maestro',
                self::generateCardNumber(66, 16),
                true,
            ],
            'maestro73' => [
                'maestro',
                self::generateCardNumber(67, 16),
                true,
            ],
            'maestro74' => [
                'maestro',
                self::generateCardNumber(68, 16),
                true,
            ],
            'maestro75' => [
                'maestro',
                self::generateCardNumber(69, 16),
                true,
            ],
            'maestro91' => [
                'maestro',
                self::generateCardNumber(50, 18),
                true,
            ],
            'maestro92' => [
                'maestro',
                self::generateCardNumber(56, 18),
                true,
            ],
            'maestro93' => [
                'maestro',
                self::generateCardNumber(57, 18),
                true,
            ],
            'maestro94' => [
                'maestro',
                self::generateCardNumber(58, 18),
                true,
            ],
            'maestro95' => [
                'maestro',
                self::generateCardNumber(59, 18),
                true,
            ],
            'maestro96' => [
                'maestro',
                self::generateCardNumber(60, 18),
                true,
            ],
            'maestro97' => [
                'maestro',
                self::generateCardNumber(61, 18),
                true,
            ],
            'maestro98' => [
                'maestro',
                self::generateCardNumber(62, 18),
                true,
            ],
            'maestro99' => [
                'maestro',
                self::generateCardNumber(63, 18),
                true,
            ],
            'maestro100' => [
                'maestro',
                self::generateCardNumber(64, 18),
                true,
            ],
            'maestro101' => [
                'maestro',
                self::generateCardNumber(65, 18),
                true,
            ],
            'maestro102' => [
                'maestro',
                self::generateCardNumber(66, 18),
                true,
            ],
            'maestro103' => [
                'maestro',
                self::generateCardNumber(67, 18),
                true,
            ],
            'maestro104' => [
                'maestro',
                self::generateCardNumber(68, 18),
                true,
            ],
            'maestro105' => [
                'maestro',
                self::generateCardNumber(69, 18),
                true,
            ],
            'maestro106' => [
                'maestro',
                self::generateCardNumber(50, 19),
                true,
            ],
            'maestro107' => [
                'maestro',
                self::generateCardNumber(56, 19),
                true,
            ],
            'maestro108' => [
                'maestro',
                self::generateCardNumber(57, 19),
                true,
            ],
            'maestro109' => [
                'maestro',
                self::generateCardNumber(58, 19),
                true,
            ],
            'maestro110' => [
                'maestro',
                self::generateCardNumber(59, 19),
                true,
            ],
            'maestro111' => [
                'maestro',
                self::generateCardNumber(60, 19),
                true,
            ],
            'maestro112' => [
                'maestro',
                self::generateCardNumber(61, 19),
                true,
            ],
            'maestro113' => [
                'maestro',
                self::generateCardNumber(62, 19),
                true,
            ],
            'maestro114' => [
                'maestro',
                self::generateCardNumber(63, 19),
                true,
            ],
            'maestro115' => [
                'maestro',
                self::generateCardNumber(64, 19),
                true,
            ],
            'maestro116' => [
                'maestro',
                self::generateCardNumber(65, 19),
                true,
            ],
            'maestro117' => [
                'maestro',
                self::generateCardNumber(66, 19),
                true,
            ],
            'maestro118' => [
                'maestro',
                self::generateCardNumber(67, 19),
                true,
            ],
            'maestro119' => [
                'maestro',
                self::generateCardNumber(68, 19),
                true,
            ],
            'maestro120' => [
                'maestro',
                self::generateCardNumber(69, 19),
                true,
            ],
            'dankort1' => [
                'dankort',
                self::generateCardNumber(5019, 16),
                true,
            ],
            'dankort2' => [
                'dankort',
                self::generateCardNumber(4175, 16),
                true,
            ],
            'dankort3' => [
                'dankort',
                self::generateCardNumber(4571, 16),
                true,
            ],
            'dankort4' => [
                'dankort',
                self::generateCardNumber(4, 16),
                true,
            ],
            'mir1' => [
                'mir',
                self::generateCardNumber(2200, 16),
                true,
            ],
            'mir2' => [
                'mir',
                self::generateCardNumber(2201, 16),
                true,
            ],
            'mir3' => [
                'mir',
                self::generateCardNumber(2202, 16),
                true,
            ],
            'mir4' => [
                'mir',
                self::generateCardNumber(2203, 16),
                true,
            ],
            'mir5' => [
                'mir',
                self::generateCardNumber(2204, 16),
                true,
            ],
            'mastercard1' => [
                'mastercard',
                self::generateCardNumber(51, 16),
                true,
            ],
            'mastercard2' => [
                'mastercard',
                self::generateCardNumber(52, 16),
                true,
            ],
            'mastercard3' => [
                'mastercard',
                self::generateCardNumber(53, 16),
                true,
            ],
            'mastercard4' => [
                'mastercard',
                self::generateCardNumber(54, 16),
                true,
            ],
            'mastercard5' => [
                'mastercard',
                self::generateCardNumber(55, 16),
                true,
            ],
            'mastercard6' => [
                'mastercard',
                self::generateCardNumber(22, 16),
                true,
            ],
            'mastercard7' => [
                'mastercard',
                self::generateCardNumber(23, 16),
                true,
            ],
            'mastercard8' => [
                'mastercard',
                self::generateCardNumber(24, 16),
                true,
            ],
            'mastercard9' => [
                'mastercard',
                self::generateCardNumber(25, 16),
                true,
            ],
            'mastercard10' => [
                'mastercard',
                self::generateCardNumber(26, 16),
                true,
            ],
            'mastercard11' => [
                'mastercard',
                self::generateCardNumber(27, 16),
                true,
            ],
            'visa1' => [
                'visa',
                self::generateCardNumber(4, 13),
                true,
            ],
            'visa2' => [
                'visa',
                self::generateCardNumber(4, 16),
                true,
            ],
            'visa3' => [
                'visa',
                self::generateCardNumber(4, 19),
                true,
            ],
            'uatp' => [
                'uatp',
                self::generateCardNumber(1, 15),
                true,
            ],
            'verve1' => [
                'verve',
                self::generateCardNumber(506, 16),
                true,
            ],
            'verve2' => [
                'verve',
                self::generateCardNumber(650, 16),
                true,
            ],
            'verve3' => [
                'verve',
                self::generateCardNumber(506, 19),
                true,
            ],
            'verve4' => [
                'verve',
                self::generateCardNumber(650, 19),
                true,
            ],
            'cibc1' => [
                'cibc',
                self::generateCardNumber(4506, 16),
                true,
            ],
            'rbc1' => [
                'rbc',
                self::generateCardNumber(45, 16),
                true,
            ],
            'tdtrust' => [
                'tdtrust',
                self::generateCardNumber(589297, 16),
                true,
            ],
            'scotia1' => [
                'scotia',
                self::generateCardNumber(4536, 16),
                true,
            ],
            'bmoabm1' => [
                'bmoabm',
                self::generateCardNumber(500, 16),
                true,
            ],
            'hsbc1' => [
                'hsbc',
                self::generateCardNumber(56, 16),
                true,
            ],
            'hsbc2' => [
                'hsbc',
                self::generateCardNumber(57, 16),
                false,
            ],
        ];
    }

    /**
     * Generate a fake credit card number that still passes the Luhn algorithm.
     */
    private static function generateCardNumber(int $prefix, int $length): string
    {
        $prefix = (string) $prefix;
        $cursor = strlen($prefix);

        $digits = str_split($prefix);
        $digits = array_pad($digits, $length, '0');

        while ($cursor < $length - 1) {
            $digits[$cursor++] = (string) random_int(0, 9);
        }

        $digits[$length - 1] = (string) self::calculateLuhnChecksum($digits, $length);

        return implode('', $digits);
    }

    private static function calculateLuhnChecksum(array $digits, int $length): int
    {
        $parity = $length % 2;

        $sum = 0;

        for ($i = $length - 1; $i >= 0; $i--) {
            $current = $digits[$i];

            if ($i % 2 === $parity) {
                $current *= 2;

                if ($current > 9) {
                    $current -= 9;
                }
            }

            $sum += $current;
        }

        return ($sum * 9) % 10;
    }
}
