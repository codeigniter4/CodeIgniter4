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
	 * This email's unique Message ID.
	 *
	 * @var string|null
	 */
	private $messageId;

	/**
	 * Stores any initial values.
	 *
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->set($data);
	}

	/**
	 * Resets $messageId for clones to ensure each is unique.
	 */
	public function __clone()
	{
		unset($this->messageId);
	}

	//--------------------------------------------------------------------

	/**
	 * Bulk stores values by matching input keys to their setter method.
	 *
	 * @param array $data
	 * @param boolean $overwrite
	 *
	 * @return $this
	 */
	public function set(array $data, bool $overwrite = true): self
	{
		// Check for matching keys
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
		] as $setter)
		{
			$getter = 'get' . ucfirst($setter);

			if (array_key_exists($setter, $data))
			{
				if ($overwrite || is_null($this->$getter()))
				{
					$this->$method($data[$method]);
				}
			}
		}
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
		return $this->body($body);
	}

	/**
	 * @param string $body
	 *
	 * @return $this
	 */
	public function body(string $body)
	{
		return parent::setBody(rtrim(str_replace("\r", '', $body)));
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
	 * Return-Path must be only an email enclosed in angle brackets, e.g. "<admin@codeigniter.com>"
	 *
	 * @param string $address
	 *
	 * @return $this
	 */
	public function returnPath(string $address)
	{
		// Force Address to use angle braces by giving an empty display name
		$email = Address::split($address)['email'];

	    return $this->setHeader('Return-Path', new Address($email, ''));
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
		if (! $this->hasHeader('Return-Path'))
		{
			return null;
		}

		// Get just the email portion of the stored address
		$address = $this->getHeader('Return-Path')->getValue();
		$email   = Address::split($address)['email'];

		// Force Address to use angle braces by giving an empty display name
	    return new Address($email, '');
	}

	/**
	 * Returns the priority as an integer parsed from the header,
	 * where 1 is the highest and 5 is the lowest.
	 *
	 * @return int
	 */
	public function getPriority(): ?int
	{
		if ($this->hasHeader('X-Priority'))
		{
			return array_search($this->getHeader('X-Priority')->getValue(), self::PRIORITIES, true) ?: null;
		}

		return null;
	}

	/**
	 * @return Time|null
	 */
	public function getDate(): ?Time
	{
		return $this->hasHeader('Date') ? Time::parse($this->getHeader('Date')->getValue()) : null;
	}

	/**
	 * Gets or creates the unique Message ID.
	 * Requires Return-Path to be set.
	 *
	 * @return string|null
	 */
	public function getMessageId(): ?string
	{
		if (is_null($this->messageId) && $returnPath = $this->getReturnPath())
		{
			// Use a unique ID with the same domain as the Return-Path email
			$this->messageId = '<' . uniqid('', true) . strstr($returnPath->getEmail(), '@'). '>';
		}

		return $this->messageId;
	}

	//--------------------------------------------------------------------
	// Backwards Compatibility
	//--------------------------------------------------------------------

	/**
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setMessage($body): self
	{
		return $this->body($body);
	}

	/**
	 * Magic method to allow CI3-style methods (like "setReplyTo()") to
	 * forward to their equivalent setters.
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
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
