<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Mailer\Handlers;

use CodeIgniter\Events\Events;
use CodeIgniter\Mailer\Email;
use CodeIgniter\Mailer\Exceptions\MailerException;
use Config\Mailer;

/**
 * Mail Handler
 *
 * Handles sending email using the PHP mail() function.
 */
abstract class BaseHandler implements MailerInterface
{
	/**
	 * The Mailer config.
	 *
	 * @var Mailer|null
	 */
	protected $config;

	/**
	 * The config-specific Encoder.
	 *
	 * @var Encoder
	 */
	protected $encoder;

	/**
	 * Protocol for this handler.
	 * Set by child classes.
	 *
	 * @var string
	 */
	protected $protocol;

	//--------------------------------------------------------------------

	/**
	 * Whether this handler is supported on this system.
	 *
	 * @return boolean
	 */
	abstract public function isSupported(): bool;

	/**
	 * Spools an Email to the server.
	 *
	 * @param Email $email
	 *
	 * @return boolean
	 */
	abstract protected function spool(Email $email);

	//--------------------------------------------------------------------

	/**
	 * Stores the Mailer config.
	 *
	 * @param Mailer $config
	 */
	public function __construct(Mailer $config)
	{
		$this->config  = clone $config;
		$this->encoder = new Encoder($this->config->encoder);
	}

	/**
	 * Gets the Mailer config.
	 *
	 * @return Mailer
	 */
	public function getConfig(Mailer $config): Mailer
	{
		return $this->config;
	}

	/**
	 * Gets the protocol.
	 *
	 * @return string
	 */
	public function getProtocol(): string
	{
		return $this->protocol;
	}

	//--------------------------------------------------------------------

	/**
	 * Sends an Email.
	 *
	 * @param Email $email
	 */
	public function send(Email $email)
	{
		$this->buildHeaders($email);
		$this->buildMessage($email);

		// Check for a batch request
		if ($this->config->batchMode)
		{
			// Temporarily disable batch mode to prevent recursion
			$this->config->batchMode = false;
			$bccs = $email->getBcc();

			try
			{
				foreach (array_chunk($bccs, $this->config->batchSize) as $bcc)
				{
					$batch = clone $email;
					$batch->bcc($bcc);

					$this->send($batch);
				}
			}
			// Restore batch mode
			finally
			{
				$this->config->batchMode = true;
			}

			return;
		}

		$result = $this->spool($email);



		if ($result)
		{
			$this->setArchiveValues();

			if ($autoClear)
			{
				$this->clear();
			}

			Events::trigger('email_sent', $email);
		}

		return $result;

	}

	/**
	 * Builds the remaining Email headers (including config defaults)
	 * and validates all required headers are present.
	 *
	 * @return Mailer
	 *
	 * @throws MailerException
	 */
	protected function buildHeaders(Email $email): self
	{
		// Apply any missing defaults from the config
		$email->set($this->config->defaults, false);

		// Must have a sender
		if (is_null($email->getFrom()))
		{
			throw MailerException::forNoFrom();
		}
		// Must have at least one recipient
		if (empty($email->getTo()) && empty($email->getCc()) && empty($email->getBcc()))
		{
			throw MailerException::forNoRecipients();
		}

		// If no "reply to" header was set then use the From value
		if (is_null($email->getReplyTo()) && $from = $email->getFrom())
		{
			$email->setReplyTo($from);
		}
		// If no priority header was set then use "normal"
		if (is_null($email->getPriority()))
		{
			$email->setPriority(3);
		}

		// Set transport headers directly
		$email->setHeader('User-Agent', $this->config->userAgent);
		$email->setHeader('X-Mailer', $this->config->userAgent);
		$email->setHeader('X-Sender', $email->getFrom()->getEmail());
		$email->setHeader('Message-ID', $this->getMessageID());
		$email->setHeader('Mime-Version', '1.0');

		return $this;
	}
}
