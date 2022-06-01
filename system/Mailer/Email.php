<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Mailer;

use BadMethodCallException;
use CodeIgniter\HTTP\Message;
use CodeIgniter\I18n\Time;
use DateTimeInterface;

/**
 * Email Class
 *
 * Represents a single email message.
 * This class extends HTTP\Message and uses headers for most storage and retrieval.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc5322
 * @see https://datatracker.ietf.org/doc/html/rfc2047
 */
class Email extends Message
{
    /**
     * Array of priorities and their encoded value.
     */
    private const PRIORITIES = [
        1 => '1 (Highest)',
        2 => '2 (High)',
        3 => '3 (Normal)',
        4 => '4 (Low)',
        5 => '5 (Lowest)',
    ];

    /**
     * Array of Headers to collapse into a CSV.
     */
    private const CSV_HEADERS = ['To', 'Cc', 'Bcc'];

    /**
     * Attachments to this email
     *
     * @var Attachment[]
     */
    protected $attachments = [];

    /**
     * This email's unique Message ID.
     *
     * @var string|null
     */
    private $messageId;

    /**
     * Boundaries for header & body divisions.
     *
     * @var array<string,string>
     */
    private $boundaries = [];

    /**
     * Stores any initial values.
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
    // Class Operations
    //--------------------------------------------------------------------

    /**
     * Bulk stores values by matching input keys to their setter method.
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
        ] as $setter) {
            $getter = 'get' . ucfirst($setter);

            if (array_key_exists($setter, $data)) {
                if ($overwrite || $this->{$getter}() === null) {
                    $this->{$setter}($data[$setter]);
                }
            }
        }

        return $this;
    }

    /**
     * Compiles headers into a spool-ready string.
     *
     * @param string        $newline Character(s) to use when combining the sequence
     * @param string[]|null $exclude An array of header names to exclude
     * @param string[]|null $include An array of header names to include
     */
    public function getHeaderString(string $newline = "\r\n", ?array $exclude = null, ?array $include = null): string
    {
        $string = '';

        // Use $headers directly instead of headers() so as not to trigger populateHeaders()
        foreach ($this->headers as $name => $value) {
            // Check exclusion
            if (isset($exclude) && in_array($name, $exclude, true)) {
                continue;
            }
            // Check inclusion
            if (isset($include) && ! in_array($name, $include, true)) {
                continue;
            }

            // Check for a collapsible
            if (in_array($name, self::CSV_HEADERS, true) && is_array($value)) {
                $string .= implode(',', array_map('__toString', $value));
            } elseif (is_array($value)) {
                foreach ($value as $header) {
                    $string .= $header . $newline;
                }
            } else {
                $string .= (string)$value->getValue() . $newline; // Trailing newline is intentional
            }
        }

        return $string;
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
     * @return $this
     */
    public function body(string $body)
    {
        return parent::setBody(rtrim(str_replace("\r", '', $body)));
    }

    /**
     * @return $this
     */
    public function subject(string $subject)
    {
        return $this->setHeader('Subject', $subject);
    }

    /**
     * @return $this
     */
    public function from(string $address, ?string $name=null)
    {
        $addr = $name !== null
            ? new Address($address, $name)
            : Address::create($address);

        return $this->setHeader('From', $addr);
    }

    /**
     * @param Address|string ...$addresses
     *
     * @return $this
     */
    public function to(...$addresses)
    {
        // If an array of address was pushed in
        // break it out so we get only the actual emails
        if($addresses[0] && is_array($addresses[0])) {
            $addresses = $addresses[0];
        }

        return $this->setHeader('To', Address::createArray($addresses));
    }

    /**
     * @param Address|string ...$addresses
     *
     * @return $this
     */
    public function cc(...$addresses)
    {
        // If an array of address was pushed in
        // break it out so we get only the actual emails
        if($addresses[0] && is_array($addresses[0])) {
            $addresses = $addresses[0];
        }

        return $this->setHeader('Cc', Address::createArray($addresses));
    }

    /**
     * @param Address|string ...$addresses
     *
     * @return $this
     */
    public function bcc(...$addresses)
    {
        // If an array of address was pushed in
        // break it out so we get only the actual emails
        if($addresses[0] && is_array($addresses[0])) {
            $addresses = $addresses[0];
        }

        return $this->setHeader('Bcc', Address::createArray($addresses));
    }

    /**
     * @return $this
     */
    public function replyTo(string $address, ?string $name=null)
    {
        $addr = $name !== null
            ? new Address($address, $name)
            : Address::create($address);

        return $this->setHeader('Reply-To', $addr);
    }

    /**
     * Return-Path must be only an email enclosed in angle brackets, e.g. "<admin@codeigniter.com>"
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

    public function getSubject(): ?string
    {
        return $this->hasHeader('Subject') ? $this->header('Subject')->getValue() : null;
    }

    public function getFrom(): ?Address
    {
        return $this->hasHeader('From') ? Address::create($this->header('From')->getValue()) : null;
    }

    /**
     * @return Address[]|null
     */
    public function getTo(): ?array
    {
        return $this->hasHeader('To') ? Address::createArray($this->header('To')->getValue()) : null;
    }

    /**
     * @return Address[]|null
     */
    public function getCc(): ?array
    {
        return $this->hasHeader('Cc') ? Address::createArray($this->header('Cc')->getValue()) : null;
    }

    /**
     * @return Address[]|null
     */
    public function getBcc(): ?array
    {
        return $this->hasHeader('Bcc') ? Address::createArray($this->header('Bcc')->getValue()) : null;
    }

    public function getReplyTo(): ?Address
    {
        return $this->hasHeader('Reply-To') ? Address::create($this->header('Reply-To')->getValue()) : null;
    }

    public function getReturnPath(): ?Address
    {
        if (! $this->hasHeader('Return-Path')) {
            return null;
        }

        // Get just the email portion of the stored address
        $address = $this->header('Return-Path')->getValue();
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
        if ($this->hasHeader('X-Priority')) {
            return array_search($this->header('X-Priority')->getValue(), self::PRIORITIES, true) ?: null;
        }

        return null;
    }

    public function getDate(): ?Time
    {
        return $this->hasHeader('Date') ? Time::parse($this->header('Date')->getValue()) : null;
    }

    /**
     * Gets or creates the unique Message ID.
     * Requires Return-Path to be set.
     */
    public function getMessageId(): ?string
    {
        if ($this->messageId === null && $returnPath = $this->getReturnPath()) {
            // Use a unique ID with the same domain as the Return-Path email
            $this->messageId = '<' . uniqid('', true) . strstr($returnPath->getEmail(), '@') . '>';
        }

        return $this->messageId;
    }

    /**
     * Tracks boundary keys so they can be reused.
     */
    public function getBoundary(string $name): string
    {
        if (! isset($this->boundaries[$name])) {
            $this->boundaries[$name] = uniqid($name . '_', true);
        }

        return $this->boundaries[$name];
    }

    //--------------------------------------------------------------------
    // Attachments
    //--------------------------------------------------------------------

    /**
     * Adds an Attachment.
     *
     * @return $this
     */
    public function attach(Attachment $attachment): self
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Returns all or filtered Attachments.
     *
     * @param bool|null $contentFilter (Optional) Whether to include/exclude attachments with Content ID
     *
     * @return Attachment[]
     */
    public function getAttachments(?bool $contentFilter = null): array
    {
        if (null === $contentFilter) {
            return $this->attachments;
        }

        $attachments = [];

        foreach ($this->attachments as $attachment) {
            if ($contentFilter && $attachment->hasContentId()) {
                $attachments[] = $attachment;
            }
            if (! $contentFilter && ! $attachment->hasContentId()) {
                $attachments[] = $attachment;
            }
        }

        return $attachments;
    }

    /**
     * Removes and returns all Attachments.
     *
     * @return Attachment[]
     */
    public function detach(): array
    {
        $attachments = $this->getAttachments();

        $this->attachments = [];

        return $attachments;
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
     * Sets a header and it's value.
     *
     * Extends the default 
     *
     * @param array|string|null $value
     *
     * @return $this
     */
    public function setHeader(string $name, $value): self
    {

    }

    /**
     * Magic method to allow CI3-style methods (like "setReplyTo()") to
     * forward to their equivalent setters.
     *
     * @throws BadMethodCallException
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (strpos($name, 'set') === 0 && method_exists($this, $method = lcfirst(substr($name, 3)))) {
            return $this->{$method}(...$arguments);
        }

        throw new BadMethodCallException("Method {$name} does not exist");
    }
}
