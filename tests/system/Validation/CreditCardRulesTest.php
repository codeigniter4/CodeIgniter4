<?php namespace CodeIgniter\Validation;

class CreditCardRulesTest extends \CIUnitTestCase
{

	/**
	 * @var Validation
	 */
	protected $validation;
	protected $config = [
		'ruleSets'      => [
			\CodeIgniter\Validation\Rules::class,
			\CodeIgniter\Validation\FormatRules::class,
			\CodeIgniter\Validation\FileRules::class,
			\CodeIgniter\Validation\CreditCardRules::class,
			\Tests\Support\Validation\TestRules::class,
		],
		'groupA'        => [
			'foo' => 'required|min_length[5]',
		],
		'groupA_errors' => [
			'foo' => [
				'min_length' => 'Shame, shame. Too short.',
			],
		],
	];

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();
		$this->validation = new Validation((object) $this->config, \Config\Services::renderer());
		$this->validation->reset();

		$_FILES = [];
	}

	//--------------------------------------------------------------------
	// Credit Card Rules
	//--------------------------------------------------------------------

	/**
	 * @dataProvider creditCardProvider
	 *
	 * @param $type
	 * @param $number
	 * @param boolean $expected
	 */
	public function testValidCCNumber($type, $number, $expected = false)
	{
		$data = [
			'cc' => $number,
		];

		$this->validation->setRules([
			'cc' => "valid_cc_number[{$type}]",
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

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
			'null_test'      => [
				'amex',
				null,
				false,
			],
			'random_test'    => [
				'amex',
				$this->generateCardNum('37', 16),
				false,
			],
			'invalid_type'   => [
				'shorty',
				'1111 1111 1111 1111',
				false,
			],
			'invalid_length' => [
				'amex',
				'',
				false,
			],
			'not_numeric'    => [
				'amex',
				'abcd efgh ijkl mnop',
				false,
			],
			'bad_length'     => [
				'amex',
				'3782 8224 6310 0051',
				false,
			],
			'bad_prefix'     => [
				'amex',
				'3582 8224 6310 0051',
				false,
			],
			'amex1'          => [
				'amex',
				'3782 8224 6310 005',
				true,
			],
			'amex2'          => [
				'amex',
				'3714 4963 5398 431',
				true,
			],
			'dinersclub1'    => [
				'dinersclub',
				'3056 9309 0259 04',
				true,
			],
			'dinersculb2'    => [
				'dinersclub',
				'3852 0000 0232 37',
				true,
			],
			'discover1'      => [
				'discover',
				'6011 1111 1111 1117',
				true,
			],
			'discover2'      => [
				'discover',
				'6011 0009 9013 9424',
				true,
			],
			'jcb1'           => [
				'jcb',
				'3530 1113 3330 0000',
				true,
			],
			'jcb2'           => [
				'jcb',
				'3566 0020 2036 0505',
				true,
			],
			'mastercard1'    => [
				'mastercard',
				'5555 5555 5555 4444',
				true,
			],
			'mastercard2'    => [
				'mastercard',
				'5105 1051 0510 5100',
				true,
			],
			'visa1'          => [
				'visa',
				'4111 1111 1111 1111',
				true,
			],
			'visa2'          => [
				'visa',
				'4012 8888 8888 1881',
				true,
			],
			'visa3'          => [
				'visa',
				'4222 2222 2222 2',
				true,
			],
			'dankort1'       => [
				'dankort',
				'5019 7170 1010 3742',
				true,
			],
			'unionpay1'      => [
				'unionpay',
				$this->generateCardNum(62, 16),
				true,
			],
			'unionpay2'      => [
				'unionpay',
				$this->generateCardNum(62, 17),
				true,
			],
			'unionpay3'      => [
				'unionpay',
				$this->generateCardNum(62, 18),
				true,
			],
			'unionpay4'      => [
				'unionpay',
				$this->generateCardNum(62, 19),
				true,
			],
			'unionpay5'      => [
				'unionpay',
				$this->generateCardNum(63, 19),
				false,
			],
			'carteblanche1'  => [
				'carteblanche',
				$this->generateCardNum(300, 14),
				true,
			],
			'carteblanche2'  => [
				'carteblanche',
				$this->generateCardNum(301, 14),
				true,
			],
			'carteblanche3'  => [
				'carteblanche',
				$this->generateCardNum(302, 14),
				true,
			],
			'carteblanche4'  => [
				'carteblanche',
				$this->generateCardNum(303, 14),
				true,
			],
			'carteblanche5'  => [
				'carteblanche',
				$this->generateCardNum(304, 14),
				true,
			],
			'carteblanche6'  => [
				'carteblanche',
				$this->generateCardNum(305, 14),
				true,
			],
			'carteblanche7'  => [
				'carteblanche',
				$this->generateCardNum(306, 14),
				false,
			],
			'dinersclub3'    => [
				'dinersclub',
				$this->generateCardNum(300, 14),
				true,
			],
			'dinersclub4'    => [
				'dinersclub',
				$this->generateCardNum(301, 14),
				true,
			],
			'dinersclub5'    => [
				'dinersclub',
				$this->generateCardNum(302, 14),
				true,
			],
			'dinersclub6'    => [
				'dinersclub',
				$this->generateCardNum(303, 14),
				true,
			],
			'dinersclub7'    => [
				'dinersclub',
				$this->generateCardNum(304, 14),
				true,
			],
			'dinersclub8'    => [
				'dinersclub',
				$this->generateCardNum(305, 14),
				true,
			],
			'dinersclub9'    => [
				'dinersclub',
				$this->generateCardNum(309, 14),
				true,
			],
			'dinersclub10'   => [
				'dinersclub',
				$this->generateCardNum(36, 14),
				true,
			],
			'dinersclub11'   => [
				'dinersclub',
				$this->generateCardNum(38, 14),
				true,
			],
			'dinersclub12'   => [
				'dinersclub',
				$this->generateCardNum(39, 14),
				true,
			],
			'dinersclub13'   => [
				'dinersclub',
				$this->generateCardNum(54, 14),
				true,
			],
			'dinersclub14'   => [
				'dinersclub',
				$this->generateCardNum(55, 14),
				true,
			],
			'dinersclub15'   => [
				'dinersclub',
				$this->generateCardNum(300, 16),
				true,
			],
			'dinersclub16'   => [
				'dinersclub',
				$this->generateCardNum(301, 16),
				true,
			],
			'dinersclub17'   => [
				'dinersclub',
				$this->generateCardNum(302, 16),
				true,
			],
			'dinersclub18'   => [
				'dinersclub',
				$this->generateCardNum(303, 16),
				true,
			],
			'dinersclub19'   => [
				'dinersclub',
				$this->generateCardNum(304, 16),
				true,
			],
			'dinersclub20'   => [
				'dinersclub',
				$this->generateCardNum(305, 16),
				true,
			],
			'dinersclub21'   => [
				'dinersclub',
				$this->generateCardNum(309, 16),
				true,
			],
			'dinersclub22'   => [
				'dinersclub',
				$this->generateCardNum(36, 16),
				true,
			],
			'dinersclub23'   => [
				'dinersclub',
				$this->generateCardNum(38, 16),
				true,
			],
			'dinersclub24'   => [
				'dinersclub',
				$this->generateCardNum(39, 16),
				true,
			],
			'dinersclub25'   => [
				'dinersclub',
				$this->generateCardNum(54, 16),
				true,
			],
			'dinersclub26'   => [
				'dinersclub',
				$this->generateCardNum(55, 16),
				true,
			],
			'discover3'      => [
				'discover',
				$this->generateCardNum(6011, 16),
				true,
			],
			'discover4'      => [
				'discover',
				$this->generateCardNum(622, 16),
				true,
			],
			'discover5'      => [
				'discover',
				$this->generateCardNum(644, 16),
				true,
			],
			'discover6'      => [
				'discover',
				$this->generateCardNum(645, 16),
				true,
			],
			'discover7'      => [
				'discover',
				$this->generateCardNum(656, 16),
				true,
			],
			'discover8'      => [
				'discover',
				$this->generateCardNum(647, 16),
				true,
			],
			'discover9'      => [
				'discover',
				$this->generateCardNum(648, 16),
				true,
			],
			'discover10'     => [
				'discover',
				$this->generateCardNum(649, 16),
				true,
			],
			'discover11'     => [
				'discover',
				$this->generateCardNum(65, 16),
				true,
			],
			'discover12'     => [
				'discover',
				$this->generateCardNum(6011, 19),
				true,
			],
			'discover13'     => [
				'discover',
				$this->generateCardNum(622, 19),
				true,
			],
			'discover14'     => [
				'discover',
				$this->generateCardNum(644, 19),
				true,
			],
			'discover15'     => [
				'discover',
				$this->generateCardNum(645, 19),
				true,
			],
			'discover16'     => [
				'discover',
				$this->generateCardNum(656, 19),
				true,
			],
			'discover17'     => [
				'discover',
				$this->generateCardNum(647, 19),
				true,
			],
			'discover18'     => [
				'discover',
				$this->generateCardNum(648, 19),
				true,
			],
			'discover19'     => [
				'discover',
				$this->generateCardNum(649, 19),
				true,
			],
			'discover20'     => [
				'discover',
				$this->generateCardNum(65, 19),
				true,
			],
			'interpayment1'  => [
				'interpayment',
				$this->generateCardNum(4, 16),
				true,
			],
			'interpayment2'  => [
				'interpayment',
				$this->generateCardNum(4, 17),
				true,
			],
			'interpayment3'  => [
				'interpayment',
				$this->generateCardNum(4, 18),
				true,
			],
			'interpayment4'  => [
				'interpayment',
				$this->generateCardNum(4, 19),
				true,
			],
			'jcb1'           => [
				'jcb',
				$this->generateCardNum(352, 16),
				true,
			],
			'jcb2'           => [
				'jcb',
				$this->generateCardNum(353, 16),
				true,
			],
			'jcb3'           => [
				'jcb',
				$this->generateCardNum(354, 16),
				true,
			],
			'jcb4'           => [
				'jcb',
				$this->generateCardNum(355, 16),
				true,
			],
			'jcb5'           => [
				'jcb',
				$this->generateCardNum(356, 16),
				true,
			],
			'jcb6'           => [
				'jcb',
				$this->generateCardNum(357, 16),
				true,
			],
			'jcb7'           => [
				'jcb',
				$this->generateCardNum(358, 16),
				true,
			],
			'maestro1'       => [
				'maestro',
				$this->generateCardNum(50, 12),
				true,
			],
			'maestro2'       => [
				'maestro',
				$this->generateCardNum(56, 12),
				true,
			],
			'maestro3'       => [
				'maestro',
				$this->generateCardNum(57, 12),
				true,
			],
			'maestro4'       => [
				'maestro',
				$this->generateCardNum(58, 12),
				true,
			],
			'maestro5'       => [
				'maestro',
				$this->generateCardNum(59, 12),
				true,
			],
			'maestro6'       => [
				'maestro',
				$this->generateCardNum(60, 12),
				true,
			],
			'maestro7'       => [
				'maestro',
				$this->generateCardNum(61, 12),
				true,
			],
			'maestro8'       => [
				'maestro',
				$this->generateCardNum(62, 12),
				true,
			],
			'maestro9'       => [
				'maestro',
				$this->generateCardNum(63, 12),
				true,
			],
			'maestro10'      => [
				'maestro',
				$this->generateCardNum(64, 12),
				true,
			],
			'maestro11'      => [
				'maestro',
				$this->generateCardNum(65, 12),
				true,
			],
			'maestro12'      => [
				'maestro',
				$this->generateCardNum(66, 12),
				true,
			],
			'maestro13'      => [
				'maestro',
				$this->generateCardNum(67, 12),
				true,
			],
			'maestro14'      => [
				'maestro',
				$this->generateCardNum(68, 12),
				true,
			],
			'maestro15'      => [
				'maestro',
				$this->generateCardNum(69, 12),
				true,
			],
			'maestro16'      => [
				'maestro',
				$this->generateCardNum(50, 13),
				true,
			],
			'maestro17'      => [
				'maestro',
				$this->generateCardNum(56, 13),
				true,
			],
			'maestro18'      => [
				'maestro',
				$this->generateCardNum(57, 13),
				true,
			],
			'maestro19'      => [
				'maestro',
				$this->generateCardNum(58, 13),
				true,
			],
			'maestro20'      => [
				'maestro',
				$this->generateCardNum(59, 13),
				true,
			],
			'maestro21'      => [
				'maestro',
				$this->generateCardNum(60, 13),
				true,
			],
			'maestro22'      => [
				'maestro',
				$this->generateCardNum(61, 13),
				true,
			],
			'maestro23'      => [
				'maestro',
				$this->generateCardNum(62, 13),
				true,
			],
			'maestro24'      => [
				'maestro',
				$this->generateCardNum(63, 13),
				true,
			],
			'maestro25'      => [
				'maestro',
				$this->generateCardNum(64, 13),
				true,
			],
			'maestro26'      => [
				'maestro',
				$this->generateCardNum(65, 13),
				true,
			],
			'maestro27'      => [
				'maestro',
				$this->generateCardNum(66, 13),
				true,
			],
			'maestro28'      => [
				'maestro',
				$this->generateCardNum(67, 13),
				true,
			],
			'maestro29'      => [
				'maestro',
				$this->generateCardNum(68, 13),
				true,
			],
			'maestro30'      => [
				'maestro',
				$this->generateCardNum(69, 13),
				true,
			],
			'maestro31'      => [
				'maestro',
				$this->generateCardNum(50, 14),
				true,
			],
			'maestro32'      => [
				'maestro',
				$this->generateCardNum(56, 14),
				true,
			],
			'maestro33'      => [
				'maestro',
				$this->generateCardNum(57, 14),
				true,
			],
			'maestro34'      => [
				'maestro',
				$this->generateCardNum(58, 14),
				true,
			],
			'maestro35'      => [
				'maestro',
				$this->generateCardNum(59, 14),
				true,
			],
			'maestro36'      => [
				'maestro',
				$this->generateCardNum(60, 14),
				true,
			],
			'maestro37'      => [
				'maestro',
				$this->generateCardNum(61, 14),
				true,
			],
			'maestro38'      => [
				'maestro',
				$this->generateCardNum(62, 14),
				true,
			],
			'maestro39'      => [
				'maestro',
				$this->generateCardNum(63, 14),
				true,
			],
			'maestro40'      => [
				'maestro',
				$this->generateCardNum(64, 14),
				true,
			],
			'maestro41'      => [
				'maestro',
				$this->generateCardNum(65, 14),
				true,
			],
			'maestro42'      => [
				'maestro',
				$this->generateCardNum(66, 14),
				true,
			],
			'maestro43'      => [
				'maestro',
				$this->generateCardNum(67, 14),
				true,
			],
			'maestro44'      => [
				'maestro',
				$this->generateCardNum(68, 14),
				true,
			],
			'maestro45'      => [
				'maestro',
				$this->generateCardNum(69, 14),
				true,
			],
			'maestro46'      => [
				'maestro',
				$this->generateCardNum(50, 15),
				true,
			],
			'maestro47'      => [
				'maestro',
				$this->generateCardNum(56, 15),
				true,
			],
			'maestro48'      => [
				'maestro',
				$this->generateCardNum(57, 15),
				true,
			],
			'maestro49'      => [
				'maestro',
				$this->generateCardNum(58, 15),
				true,
			],
			'maestro50'      => [
				'maestro',
				$this->generateCardNum(59, 15),
				true,
			],
			'maestro51'      => [
				'maestro',
				$this->generateCardNum(60, 15),
				true,
			],
			'maestro52'      => [
				'maestro',
				$this->generateCardNum(61, 15),
				true,
			],
			'maestro53'      => [
				'maestro',
				$this->generateCardNum(62, 15),
				true,
			],
			'maestro54'      => [
				'maestro',
				$this->generateCardNum(63, 15),
				true,
			],
			'maestro55'      => [
				'maestro',
				$this->generateCardNum(64, 15),
				true,
			],
			'maestro56'      => [
				'maestro',
				$this->generateCardNum(65, 15),
				true,
			],
			'maestro57'      => [
				'maestro',
				$this->generateCardNum(66, 15),
				true,
			],
			'maestro58'      => [
				'maestro',
				$this->generateCardNum(67, 15),
				true,
			],
			'maestro59'      => [
				'maestro',
				$this->generateCardNum(68, 15),
				true,
			],
			'maestro60'      => [
				'maestro',
				$this->generateCardNum(69, 15),
				true,
			],
			'maestro61'      => [
				'maestro',
				$this->generateCardNum(50, 16),
				true,
			],
			'maestro62'      => [
				'maestro',
				$this->generateCardNum(56, 16),
				true,
			],
			'maestro63'      => [
				'maestro',
				$this->generateCardNum(57, 16),
				true,
			],
			'maestro64'      => [
				'maestro',
				$this->generateCardNum(58, 16),
				true,
			],
			'maestro65'      => [
				'maestro',
				$this->generateCardNum(59, 16),
				true,
			],
			'maestro66'      => [
				'maestro',
				$this->generateCardNum(60, 16),
				true,
			],
			'maestro67'      => [
				'maestro',
				$this->generateCardNum(61, 16),
				true,
			],
			'maestro68'      => [
				'maestro',
				$this->generateCardNum(62, 16),
				true,
			],
			'maestro69'      => [
				'maestro',
				$this->generateCardNum(63, 16),
				true,
			],
			'maestro70'      => [
				'maestro',
				$this->generateCardNum(64, 16),
				true,
			],
			'maestro71'      => [
				'maestro',
				$this->generateCardNum(65, 16),
				true,
			],
			'maestro72'      => [
				'maestro',
				$this->generateCardNum(66, 16),
				true,
			],
			'maestro73'      => [
				'maestro',
				$this->generateCardNum(67, 16),
				true,
			],
			'maestro74'      => [
				'maestro',
				$this->generateCardNum(68, 16),
				true,
			],
			'maestro75'      => [
				'maestro',
				$this->generateCardNum(69, 16),
				true,
			],
			'maestro91'      => [
				'maestro',
				$this->generateCardNum(50, 18),
				true,
			],
			'maestro92'      => [
				'maestro',
				$this->generateCardNum(56, 18),
				true,
			],
			'maestro93'      => [
				'maestro',
				$this->generateCardNum(57, 18),
				true,
			],
			'maestro94'      => [
				'maestro',
				$this->generateCardNum(58, 18),
				true,
			],
			'maestro95'      => [
				'maestro',
				$this->generateCardNum(59, 18),
				true,
			],
			'maestro96'      => [
				'maestro',
				$this->generateCardNum(60, 18),
				true,
			],
			'maestro97'      => [
				'maestro',
				$this->generateCardNum(61, 18),
				true,
			],
			'maestro98'      => [
				'maestro',
				$this->generateCardNum(62, 18),
				true,
			],
			'maestro99'      => [
				'maestro',
				$this->generateCardNum(63, 18),
				true,
			],
			'maestro100'     => [
				'maestro',
				$this->generateCardNum(64, 18),
				true,
			],
			'maestro101'     => [
				'maestro',
				$this->generateCardNum(65, 18),
				true,
			],
			'maestro102'     => [
				'maestro',
				$this->generateCardNum(66, 18),
				true,
			],
			'maestro103'     => [
				'maestro',
				$this->generateCardNum(67, 18),
				true,
			],
			'maestro104'     => [
				'maestro',
				$this->generateCardNum(68, 18),
				true,
			],
			'maestro105'     => [
				'maestro',
				$this->generateCardNum(69, 18),
				true,
			],
			'maestro106'     => [
				'maestro',
				$this->generateCardNum(50, 19),
				true,
			],
			'maestro107'     => [
				'maestro',
				$this->generateCardNum(56, 19),
				true,
			],
			'maestro108'     => [
				'maestro',
				$this->generateCardNum(57, 19),
				true,
			],
			'maestro109'     => [
				'maestro',
				$this->generateCardNum(58, 19),
				true,
			],
			'maestro110'     => [
				'maestro',
				$this->generateCardNum(59, 19),
				true,
			],
			'maestro111'     => [
				'maestro',
				$this->generateCardNum(60, 19),
				true,
			],
			'maestro112'     => [
				'maestro',
				$this->generateCardNum(61, 19),
				true,
			],
			'maestro113'     => [
				'maestro',
				$this->generateCardNum(62, 19),
				true,
			],
			'maestro114'     => [
				'maestro',
				$this->generateCardNum(63, 19),
				true,
			],
			'maestro115'     => [
				'maestro',
				$this->generateCardNum(64, 19),
				true,
			],
			'maestro116'     => [
				'maestro',
				$this->generateCardNum(65, 19),
				true,
			],
			'maestro117'     => [
				'maestro',
				$this->generateCardNum(66, 19),
				true,
			],
			'maestro118'     => [
				'maestro',
				$this->generateCardNum(67, 19),
				true,
			],
			'maestro119'     => [
				'maestro',
				$this->generateCardNum(68, 19),
				true,
			],
			'maestro120'     => [
				'maestro',
				$this->generateCardNum(69, 19),
				true,
			],
			'dankort1'       => [
				'dankort',
				$this->generateCardNum(5019, 16),
				true,
			],
			'dankort2'       => [
				'dankort',
				$this->generateCardNum(4175, 16),
				true,
			],
			'dankort3'       => [
				'dankort',
				$this->generateCardNum(4571, 16),
				true,
			],
			'dankort4'       => [
				'dankort',
				$this->generateCardNum(4, 16),
				true,
			],
			'mir1'           => [
				'mir',
				$this->generateCardNum(2200, 16),
				true,
			],
			'mir2'           => [
				'mir',
				$this->generateCardNum(2201, 16),
				true,
			],
			'mir3'           => [
				'mir',
				$this->generateCardNum(2202, 16),
				true,
			],
			'mir4'           => [
				'mir',
				$this->generateCardNum(2203, 16),
				true,
			],
			'mir5'           => [
				'mir',
				$this->generateCardNum(2204, 16),
				true,
			],
			'mastercard1'    => [
				'mastercard',
				$this->generateCardNum(51, 16),
				true,
			],
			'mastercard2'    => [
				'mastercard',
				$this->generateCardNum(52, 16),
				true,
			],
			'mastercard3'    => [
				'mastercard',
				$this->generateCardNum(53, 16),
				true,
			],
			'mastercard4'    => [
				'mastercard',
				$this->generateCardNum(54, 16),
				true,
			],
			'mastercard5'    => [
				'mastercard',
				$this->generateCardNum(55, 16),
				true,
			],
			'mastercard6'    => [
				'mastercard',
				$this->generateCardNum(22, 16),
				true,
			],
			'mastercard7'    => [
				'mastercard',
				$this->generateCardNum(23, 16),
				true,
			],
			'mastercard8'    => [
				'mastercard',
				$this->generateCardNum(24, 16),
				true,
			],
			'mastercard9'    => [
				'mastercard',
				$this->generateCardNum(25, 16),
				true,
			],
			'mastercard10'   => [
				'mastercard',
				$this->generateCardNum(26, 16),
				true,
			],
			'mastercard11'   => [
				'mastercard',
				$this->generateCardNum(27, 16),
				true,
			],
			'visa1'          => [
				'visa',
				$this->generateCardNum(4, 13),
				true,
			],
			'visa2'          => [
				'visa',
				$this->generateCardNum(4, 16),
				true,
			],
			'visa3'          => [
				'visa',
				$this->generateCardNum(4, 19),
				true,
			],
			'uatp'           => [
				'uatp',
				$this->generateCardNum(1, 15),
				true,
			],
			'verve1'         => [
				'verve',
				$this->generateCardNum(506, 16),
				true,
			],
			'verve2'         => [
				'verve',
				$this->generateCardNum(650, 16),
				true,
			],
			'verve3'         => [
				'verve',
				$this->generateCardNum(506, 19),
				true,
			],
			'verve4'         => [
				'verve',
				$this->generateCardNum(650, 19),
				true,
			],
			'cibc1'          => [
				'cibc',
				$this->generateCardNum(4506, 16),
				true,
			],
			'rbc1'           => [
				'rbc',
				$this->generateCardNum(45, 16),
				true,
			],
			'tdtrust'        => [
				'tdtrust',
				$this->generateCardNum(589297, 16),
				true,
			],
			'scotia1'        => [
				'scotia',
				$this->generateCardNum(4536, 16),
				true,
			],
			'bmoabm1'        => [
				'bmoabm',
				$this->generateCardNum(500, 16),
				true,
			],
			'hsbc'           => [
				'hsbc',
				$this->generateCardNum(56, 16),
				true,
			],
			'hsbc'           => [
				'hsbc',
				$this->generateCardNum(57, 16),
				false,
			],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Used to generate fake credit card numbers that will still pass the Luhn
	 * check used to validate the card so we can be sure the cards are recognized correctly.
	 *
	 * @param integer $prefix
	 * @param integer $length
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
		while ($pos < $length - 1)
		{
			$string[$pos ++] = random_int(0, 9);
		}

		// Calculate the Luhn checksum of the current values.
		$lenOffset = ($length + 1) % 2;
		for ($pos = 0; $pos < $length - 1; $pos ++)
		{
			if (($pos + $lenOffset) % 2)
			{
				$temp = $string[$pos] * 2;
				if ($temp > 9)
				{
					$temp -= 9;
				}

				$sum += $temp;
			}
			else
			{
				$sum += $string[$pos];
			}
		}

		// Make the last number whatever would cause the entire number to pass the checksum
		$finalDigit          = (10 - ($sum % 10)) % 10;
		$string[$length - 1] = $finalDigit;

		return implode('', $string);
	}

	//--------------------------------------------------------------------
}
