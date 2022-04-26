<?php

namespace CodeIgniter\Mailer;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Mailer\Exceptions\MailerException;
use Config\Mailer;

class AddressTest extends CIUnitTestCase
{
	/**
	 * @dataProvider constructorProvider
	 */
	public function testConstructor(string $email, ?string $emailResult, ?string $name, ?string $nameResult)
	{
		if (is_null($emailResult))
		{
			$this->expectException(MailerException::class);
			$this->expectExceptionMessage(lang('Mailer.invalidAddress', [$email]));
		}

		$address = new Address($email, $name);

		$this->assertSame($emailResult, $address->getEmail());
		$this->assertSame($nameResult, $address->getName());
	}

	public function constructorProvider(): array
	{
		return [
			'justEmail' => [
				'leia@alderaan.org',
				'leia@alderaan.org',
				null,
				null,
			],
			'emailSpaces' => [
				' leia@alderaan.org ',
				'leia@alderaan.org',
				null,
				null,
			],
			'withName' => [
				'leia@alderaan.org',
				'leia@alderaan.org',
				'Princess Leia',
				'Princess Leia',
			],
			'nameSpaces' => [
				'leia@alderaan.org',
				'leia@alderaan.org',
				' Princess Leia ',
				'Princess Leia',
			],
			'nameQuotes' => [
				'leia@alderaan.org',
				'leia@alderaan.org',
				'"Princess Leia"',
				'Princess Leia',
			],
			'emptyEmail' => [
				'',
				null,
				null,
				null,
			],
			'invalidEmail' => [
				'leia@alderaan',
				null,
				'Princess Leia',
				'Princess Leia',
			],
			'illegalEmail' => [
				'le ia@alderaan.org',
				null,
				null,
				null,
			],
		];
	}

	/**
	 * @dataProvider addressProvider
	 */
	public function testCreate($address, ?string $emailResult, ?string $nameResult)
	{
		if (is_null($emailResult))
		{
			$this->expectException(MailerException::class);
			$this->expectExceptionMessage(lang('Mailer.invalidAddress', ['']));
		}

		$address = Address::create($address);

		$this->assertSame($emailResult, $address->getEmail());
		$this->assertSame($nameResult, $address->getName());
	}

	public function addressProvider(): array
	{
		return [
			'simple' => [
				'leia@alderaan.org',
				'leia@alderaan.org',
				null,
			],
			'simpleSpace' => [
				' leia@alderaan.org',
				'leia@alderaan.org',
				null,
			],
			'qualified' => [
				'Princess Leia <leia@alderaan.org>',
				'leia@alderaan.org',
				'Princess Leia',
			],
			'qualifiedSpaces' => [
				' Princess Leia  <leia@alderaan.org>',
				'leia@alderaan.org',
				'Princess Leia',
			],
			'qualifiedQuotes' => [
				'"Princess Leia" <leia@alderaan.org>',
				'leia@alderaan.org',
				'Princess Leia',
			],
			'emptyEmail' => [
				'',
				null,
				null,
			],
			'alreadyAddress' => [
				new Address('leia@alderaan.org'),
				'leia@alderaan.org',
				null,
			],
		];
	}

    /**
     * @dataProvider userProvider
     */
    public function testMerge(string $email, ?string $name, string $expected)
    {
        $this->assertEquals($expected, Address::merge($email, $name));
    }

    /**
     * @dataProvider userProvider
     */
    public function testStringify(string $email, ?string $name, string $expected)
    {
        $address = new Address($email, $name);
        $this->assertEquals($expected, (string)$address);
    }

    public function userProvider()
    {
        return [
            'emailOnly' => [
                'leia@alderaan.org',
                null,
                'leia@alderaan.org'
            ],
            'emailOnlySpace' => [
                ' leia@alderaan.org ',
                null,
                'leia@alderaan.org'
            ],
            'spaceName' => [
                ' leia@alderaan.org ',
                ' ',
                'leia@alderaan.org'
            ],
            'simpleName' => [
                'leia@alderaan.org',
                'Leia',
                '"Leia" <leia@alderaan.org>'
            ],
            'simpleNameSpaced' => [
                'leia@alderaan.org',
                ' Leia ',
                '"Leia" <leia@alderaan.org>'
            ],
            'simpleEmailSpaced' => [
                ' leia@alderaan.org ',
                ' Leia ',
                '"Leia" <leia@alderaan.org>'
            ],
            'quotedName' => [
                'leia@alderaan.org',
                'Princess Leia',
                '"Princess Leia" <leia@alderaan.org>'
            ],
        ];
    }
}
