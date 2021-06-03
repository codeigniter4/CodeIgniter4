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
use CodeIgniter\Mailer\MailerInterface;
use Config\Mailer;

/**
 * Mail Handler
 *
 * Handles sending email using the PHP mail() function.
 */
abstract class BaseHandler implements MailerInterface
{
	/**
	 * Protocol for this handler.
	 * Set by child classes.
	 *
	 * @var string
	 */
	protected $protocol;

	/**
	 * The Mailer config.
	 *
	 * @var Mailer|null
	 */
	protected $config;

	/**
	 * The config-specific Encode instance.
	 *
	 * @var Encode
	 */
	protected $encode;

	/**
	 * The config-specific Format instance.
	 *
	 * @var Format
	 */
	protected $format;

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
	 * Stores the Mailer config and preps encoding and formatting.
	 *
	 * @param Mailer $config
	 */
	public function __construct(Mailer $config)
	{
		helper(['text']);

		$this->config = clone $config;
		$this->encode = new Encode($this->config);
		$this->format = new Format($this->config);

		// Enforce a few config validations
		$this->config->format    = in_array($this->config->format, ['html', 'text']) ? $this->config->format : 'text';
		$this->config->wordWrap  = is_bool($this->config->wordWrap) ? $this->config->wordWrap : true;
		$this->config->wrapChars = is_int($this->config->wrapChars) ? $this->config->wrapChars : 76;
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

/* WIP
		if ($result)
		{
			$this->setArchiveValues();

			if ($autoClear)
			{
				$this->clear();
			}

			Events::trigger('email_sent', $email);
		}
*/
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
		$email->setHeader('Message-ID', $email->getMessageID());
		$email->setHeader('Mime-Version', '1.0');

		// Content headers take a bit more calculating
		$this->buildContentHeaders($email);

		return $this;
	}

	/**
	 * Caculates and sets the Headers for Content-Type and Content-Transfer-Encoding.
	 *
	 * @param Email
	 *
	 * @return $this
	 */
	protected function buildContentHeaders(Email $email): self
	{
		// If there are no attachments this is much easier
		if ($email->getAttachments() === [])
		{
			// text
			if ($this->config->mailFormat == 'text')
			{
				$email->setHeader('Content-Type', 'text/plain; charset=' . $this->encode->charset);
				$email->setHeader('Content-Transfer-Encoding', 'text/plain; charset=' . $this->encode->charset);

				return $this;
			}

			/**
			 * Version 3 edition supported forced multipart messages for html format despite
			 * it not being in the configuration or documentation. Should this feature need
			 * to be added back then this is the appropriate place.
			 */

			// html
			$email->setHeader('Content-Type', 'text/html; charset=' . $this->encode->charset);
			$email->setHeader('Content-Transfer-Encoding', 'quoted-printable');

			return $this;
		}

		// text + attachments
		if ($this->config->mailFormat == 'text')
		{
			$email->setHeader('Content-Type', 'multipart/mixed; boundary=' . $email->getBoundary('B_ATC'));

			return $this;
		}

		// html + attachments
		if ($email->getAttachments(false))
		{
			$email->setHeader('Content-Type', 'multipart/mixed; boundary=' . $email->getBoundary('B_ATC'));
		}
		if ($email->getAttachments(true))
		{
			$email->setHeader('Content-Type', 'multipart/related; boundary=' . $email->getBoundary('B_REL'));
		}
	}

	/**
	 * Returns the string version of the Headers from the email.
	 * Split out for handlers that need to skip the subject.
	 *
	 * @param Email
	 *
	 * @return string
	 */
	protected function getHeaderString(Email $email): string
	{
		return $email->getHeaderString($this->config->newline);
	}

	/**
	 * Returns the string version of the message, including all headers and attachments.
	 *
	 * @param Email
	 *
	 * @return string
	 */
	protected function getMessageString(Email $email): string
	{
		$message = '';

		// If there are no attachments this is much easier
		if ($email->getAttachments() === [])
		{
			// text
			if ($this->config->format == 'text')
			{
				$message .= $email->getHeaderString($this->config->newline, null, ['Content-Type', 'Content-Transfer-Encoding']);
				$message .= $this->config->newline . $this->config->newline;
				$message .= $this->config->wordWrap ? word_wrap($email->getBody()) : $email->getBody();

				return $message;
			}

			// html
			$message .= $email->getHeaderString($this->config->newline, null, ['Content-Type', 'Content-Transfer-Encoding']);
			$message .= $this->config->newline . $this->config->newline;
			$message .= $this->encode->quotedPrintable($email->getBody()) . $this->config->newline . $this->config->newline;

			return $message;
		}

		// text + attachment
		if ($this->config->format == 'text')
		{
			$message .= $email->getHeaderString($this->config->newline, null, ['Content-Type']);
			$message .= $this->config->newline . $this->config->newline;

			$message .= lang('Mailer.mimeMessage', [$this->config->newline])
					. $this->config->newline . $this->config->newline
					. '--' . $email->getBoundary('B_ATC') . $this->config->newline
					. 'Content-Type: text/plain; charset=' . $this->encode->charset . $this->config->newline
					. 'Content-Transfer-Encoding: ' . $this->encode->encoding . $this->config->newline
					. $this->config->newline
					. $email->getBody() . $this->config->newline . $this->config->newline;

			$this->appendAttachments($body, $boundary);

			return $message;
		}

		// html + attachments

/* WIP
		$alt_boundary  = uniqid('B_ALT_', true);
		$last_boundary = null;

		if ($this->attachmentsHaveMultipart('mixed'))
		{
			$atc_boundary  = uniqid('B_ATC_', true);
			$hdr          .= 'Content-Type: multipart/mixed; boundary="' . $atc_boundary . '"';
			$last_boundary = $atc_boundary;
		}

		if ($this->attachmentsHaveMultipart('related'))
		{
			$rel_boundary        = uniqid('B_REL_', true);
			$rel_boundary_header = 'Content-Type: multipart/related; boundary="' . $rel_boundary . '"';

			if (isset($last_boundary))
			{
				$body .= '--' . $last_boundary . $this->newline . $rel_boundary_header;
			}
			else
			{
				$hdr .= $rel_boundary_header;
			}

			$last_boundary = $rel_boundary;
		}

		if ($this->getProtocol() === 'mail')
		{
			$this->headerStr .= $hdr;
		}

		static::strlen($body) && $body .= $this->newline . $this->newline;
		$body                          .= $this->getMimeMessage() . $this->newline . $this->newline
				. '--' . $last_boundary . $this->newline
				. 'Content-Type: multipart/alternative; boundary="' . $alt_boundary . '"' . $this->newline . $this->newline
				. '--' . $alt_boundary . $this->newline
				. 'Content-Type: text/plain; charset=' . $this->charset . $this->newline
				. 'Content-Transfer-Encoding: ' . $this->getEncoding() . $this->newline . $this->newline
				. $this->getAltMessage() . $this->newline . $this->newline
				. '--' . $alt_boundary . $this->newline
				. 'Content-Type: text/html; charset=' . $this->charset . $this->newline
				. 'Content-Transfer-Encoding: quoted-printable' . $this->newline . $this->newline
				. $this->prepQuotedPrintable($this->body) . $this->newline . $this->newline
				. '--' . $alt_boundary . '--' . $this->newline . $this->newline;

		if (! empty($rel_boundary))
		{
			$body .= $this->newline . $this->newline;
			$this->appendAttachments($body, $rel_boundary, 'related');
		}

		// multipart/mixed attachments
		if (! empty($atc_boundary))
		{
			$body .= $this->newline . $this->newline;
			$this->appendAttachments($body, $atc_boundary, 'mixed');
		}

		break;
*/

		return $message;
	}
}
