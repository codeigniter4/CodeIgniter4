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

use BadMethodCallException;
use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\Message;
use DateTimeInterface;

/**
 * Email Class
 *
 * Represents a single email message.
 * This class extends HTTP\Message and uses headers for most storage and retrieval.
 */
class Email extends Message
{
	private const PRIORITIES = [
		1 => '1 (Highest)',
		2 => '2 (High)',
		3 => '3 (Normal)',
		4 => '4 (Low)',
		5 => '5 (Lowest)',
	];

	/**
	 * Matches input keys to their setter method to store values.
	 *
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		// Check for any keys that match our setters
		foreach ([
			'body',
			'subject',
			'from',
			'to',
			'cc',
			'bcc',
			'replyTo',
			'returnPath',
			'priority',
			'date',			
		] as $method)
		{
			if (array_key_exists($method, $data))
			{
				$this->$method($data[$method]);
			}
		}
/*
		// Check for missing defaults and set the config values
		$config = config('Mailer');
		if (! array_key_exists('from', $data) && isset($config->from))
		{
			$this->from(Address::create($config->from));
		}
		if (! array_key_exists('priority', $data))
		{
			$this->priority($config->priority);
		}
*/
	}

	//--------------------------------------------------------------------
	// Setters
	//--------------------------------------------------------------------

	/**
	 * Intercepts calls to Message::setBody() to enforce formatting.
	 *
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setBody($body): self
	{
		return parent::setBody(rtrim(str_replace("\r", '', $body)));
	}

	/**
	 * @param string $body
	 *
	 * @return $this
	 */
	public function body(string $body)
	{
		return $this->setBody($body);
	}

	/**
	 * @param string $subject
	 *
	 * @return $this
	 */
	public function subject(string $subject)
	{
	    return $this->setHeader('Subject', $subject);
	}

	/**
	 * @param string $address
	 *
	 * @return $this
	 */
	public function from(string $address)
	{
	    return $this->setHeader('From', Address::create($address));
	}

	/**
	 * @param Address|string ...$addresses
	 *
	 * @return $this
	 */
	public function to(...$addresses)
	{
	    return $this->setHeader('To', Address::createArray($addresses));
	}

	/**
	 * @param Address|string ...$addresses
	 *
	 * @return $this
	 */
	public function cc(...$addresses)
	{
	    return $this->setHeader('Cc', Address::createArray($addresses));
	}

	/**
	 * @param Address|string ...$addresses
	 *
	 * @return $this
	 */
	public function bcc(...$addresses)
	{
	    return $this->setHeader('Bcc', Address::createArray($addresses));
	}

	/**
	 * @param string $address
	 *
	 * @return $this
	 */
	public function replyTo(string $address)
	{
	    return $this->setHeader('From', Address::create($address));
	}

	/**
	 * @param string $address
	 *
	 * @return $this
	 */
	public function returnPath(string $address)
	{
	    return $this->setHeader('Return-Path', Address::create($address));
	}

	/**
	 * Sets the priority, where 1 is the highest and 5 is the lowest.
	 *
	 * @param int $priority
	 *
	 * @return $this
	 */
	public function priority(int $priority)
	{
		$priority = max($priority, 1);
		$priority = min($priority, 5);

	    return $this->setHeader('X-Priority', self::PRIORITIES[$priority]);
	}

	/**
	 * @return $this
	 */
	public function date(DateTimeInterface $date)
	{
		return $this->setHeader('Date', (string) Time::createFromInstance($date));
	}

	//--------------------------------------------------------------------
	// Getters
	//--------------------------------------------------------------------

	/**
	 * @return string|null
	 */
	public function getSubject(): ?string
	{
		return $this->hasHeader('Subject') ? $this->getHeader('Subject')->getValue() : null;
	}

	/**
	 * @return Address|null
	 */
	public function getFrom(): ?Address
	{
		return $this->hasHeader('From') ? Address::create($this->getHeader('From')->getValue()) : null;
	}

	/**
	 * @return Address[]|null
	 */
	public function getTo(): ?array
	{
		return $this->hasHeader('To') ? Address::createArray($this->getHeader('To')->getValue()) : null;
	}

	/**
	 * @return Address[]|null
	 */
	public function getCc(): ?array
	{
		return $this->hasHeader('Cc') ? Address::createArray($this->getHeader('Cc')->getValue()) : null;
	}

	/**
	 * @return Address[]|null
	 */
	public function getBcc(): ?array
	{
		return $this->hasHeader('Bcc') ? Address::createArray($this->getHeader('Bcc')->getValue()) : null;
	}

	/**
	 * @return Address|null
	 */
	public function getReplyTo(): ?Address
	{
		return $this->hasHeader('Reply-To') ? Address::create($this->getHeader('Reply-To')->getValue()) : null;
	}

	/**
	 * @return Address|null
	 */
	public function getReturnPath(): ?Address
	{
		return $this->hasHeader('Return-Path') ? Address::create($this->getHeader('Return-Path')->getValue()) : null;
	}

	/**
	 * Returns the priority as an integer parsed from the header,
	 * where 1 is the highest and 5 is the lowest. Default is 3.
	 *
	 * @return int
	 */
	public function getPriority(): int
	{
		if ($this->hasHeader('X-Priority'))
		{
			return array_search($this->getHeader('X-Priority')->getValue(), self::PRIORITIES, true) ?: 3;
		}

		return 3;
	}

	/**
	 * @return Time|null
	 */
	public function getDate(): ?Time
	{
		return $this->hasHeader('Date') ? Time::parse($this->getHeader('Date')->getValue()) : null;
	}

	//--------------------------------------------------------------------
	// Backwards Compatibility
	//--------------------------------------------------------------------

	/**
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setMessage($body)
	{
		return $this->body(rtrim(str_replace("\r", '', $body)));
	}

	/**
	 * Magic method to allow CI3-style methods (like "setReplyTo()") to
	 * forward to their new equivalent.
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		if (strpos($name, 'set') === 0 && method_exists($this, $method = lcfirst(substr($name, 3))))
		{
			return $this->$method(...$arguments);
		}

		throw new BadMethodCallException("Method {$name} does not exist");
	}
}
