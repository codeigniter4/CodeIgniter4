<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Mailer;

use InvalidArgumentException;

class Address
{
	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * Creates a new Address from a simple or full email address string.
	 *
	 * @param string $address
	 *
	 * @return static
	 */
	public static function create(string $address)
	{
		return new static(...array_values(self::split($address)));
	}

	/**
	 * Creates an array of Addresses from an array of inputs.
	 * Accounts for the niche case of a single CSV of addresses
	 * @see Email::to()
	 *
	 * @param string[] $addresses
	 *
	 * @return static[]
	 */
	public static function createArray(array $addresses)
	{
    	// Check for a CSV first
    	if (count($addresses) === 1)
    	{
    		$element = reset($addresses);

    		if (is_string($element) && strpos($element, ',') !== false)
	    	{
    			$addresses = preg_split('/[\s,]/', $element, -1, PREG_SPLIT_NO_EMPTY);
    		}
    	}

		return array_map('self::create', $addresses);
	}


	/**
	 * Parses an address into an email and (optional) display name.
	 * Trims content but does no validation or encoding.
	 *
	 * @param string $address
	 */
	final public static function split(string $address): array
	{
		if (preg_match('/\<(.*)\>/', $address, $matches))
		{
			return [
				'email' => trim($matches[1]),
				'name'  => trim(substr($address, 0, -1 * strlen($matches[0])), ' \'"'),
			];
		}

		return [
			'email' => trim($address),
			'name'  => null,
		];
	}

	/**
	 * Combines an email and (optional) display name into an address.
	 * Trims content but does no validation or encoding.
	 *
	 * @param string $email
	 * @param string|null $name
	 */
	final public static function merge(string $email, string $name = null): string
	{
		return empty($name) ? $email : $name . ' <' . $email . '>';
	}

	/**
	 * Validates the email and stores the values.
	 *
	 * @param string $email
	 * @param string|null $name
	 */
	final public function __construct(string $email, string $name = null)
	{
		$this->email = trim($email);

		if (! service('validation')->check($this->email, 'required|valid_email'))
		{
			throw new InvalidArgumentException(lang('Mailer.invalidAddress', [$email]));
		}

		$this->name  = isset($name) ? trim($name, ' \'"') : null;
	}

	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * @return string|null $name
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return self::merge($this->email, $this->name);
	}
}
