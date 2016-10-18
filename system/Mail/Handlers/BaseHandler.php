<?php namespace CodeIgniter\Mail\Handlers;

use Config\Mail;
use CodeIgniter\HTTP\HeaderTrait;
use CodeIgniter\Mail\MailHandlerInterface;
use CodeIgniter\Mail\MessageInterface;

/**
 * Class BaseHandler
 *
 * Does the basic, behind-the-scenes stuff that is common to
 * most of the standard Handlers (mail, smtp, sendmail).
 *
 * Relevant RFCS:
 *  - Simple Mail Transfer Protocol     https://tools.ietf.org/html/rfc5321
 *  - Internet Message Format           https://tools.ietf.org/html/rfc5322
 *  - Overview and Framework for        https://tools.ietf.org/html/rfc6530
 *      Internationalized Email
 *
 * @package CodeIgniter\Mail\Handlers
 */
abstract class BaseHandler implements MailHandlerInterface
{
    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * The final, built body of the message.
     *
     * @var string
     */
    protected $body;

    /**
     * Holds the headers that will be sent
     * to the client as a string.
     *
     * @var string
     */
    protected $headerString;

    /**
     * @var Mail
     */
    protected $config;

    /**
     * $priority translations
     *
     * Actual values to send with the X-Priority header
     *
     * @var	string[]
     */
    protected $priorities = [
        1 => '1 (Highest)',
        2 => '2 (High)',
        3 => '3 (Normal)',
        4 => '4 (Low)',
        5 => '5 (Lowest)'
    ];

    /**
     * Bit Depths
     *
     * Valid mail encodings.
     *
     * @var array
     */
    protected $bitDepths = ['7bit', '8bit'];

    /**
     * Base charsets
     *
     * Character sets valid for 7-bit encoding,
     * excluding language suffix.
     *
     * @var array
     */
    protected $baseCharsets = ['us-ascii', 'iso-2022-'];

    //--------------------------------------------------------------------

    public function __construct($config)
    {
        $this->config = $config;
    }

    //--------------------------------------------------------------------

    /**
     * Does the grunt work of actually sending the message.
     * MUST be created by each handler.
     *
     * @return mixed
     */
    abstract public function send();

    //--------------------------------------------------------------------

    /**
     * Takes our message and
     */
    abstract public function queue();

    //--------------------------------------------------------------------

    /**
     * Sets the message that should be sent.
     *
     * @param \CodeIgniter\Mail\MessageInterface $message
     *
     * @return $this
     */
    public function setMessage(MessageInterface $message)
    {
        // Just assign the message here. It's already been "built"
        // by the main Mailer class.
        $this->message = $message;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Gets the unique message ID.
     *
     * @return string
     */
    public function getMessageID(): string
    {
        $from = str_replace(['>', '<'], '', $this->message->getHeaderLine('Return-Path'));

        return '<'.uniqid('').strstr($from, '@').'>';
    }

    //--------------------------------------------------------------------

    /**
     * Performs Handler-agnostic checks and preparations that are common
     * to most every handler. Called by the Handlers in the send() and
     * queue() methods.
     */
    protected function prepareSend()
    {
        // Set default From if we don't have one already
        if (! $this->message->hasHeader('From'))
        {
            $this->setDefaultFrom();
        }

        // Still no from? Bad developer. No cookie.
        if (! $this->message->hasHeader('From'))
        {
            throw new \BadMethodCallException(lang('mail.noFrom'));
        }

        // Ensure we have some form of Reply-To set
        if (! $this->message->hasHeader('Reply-To'))
        {
            $this->message->setHeader('Reply-To', $this->message->getHeaderLine('From'));
        }

        // No recipients? Bad developer. Again.
        if (! $this->message->hasHeader('To') && ! $this->message->hasHeader('CC') && ! $this->message->hasHeader('BCC'))
        {
            throw new \BadMethodCallException(lang('mail.noRecipients'));
        }

        // Ensure the mail-specific X-* headers are built
        $this->buildHeaders();

        // Let the message build itself and customize
        // anything it needs to. Must be called prior
        // to buildMessage().
        $this->message->build();

        // Now build the message and format it for the mail clients.
        if ($this->buildMessage() === false)
        {
            throw new \RuntimeException(lang('mail.cannotBuildMessage'));
        }
    }

    //--------------------------------------------------------------------

    /**
     * Attempts to set a default value to the From header based
     * on values set in the Mail config file, $defaulFrom.
     */
    protected function setDefaultFrom()
    {
        if (! isset($this->config->defaultFrom) || ! is_array($this->config->defaultFrom))
        {
            throw new \BadMethodCallException(lang('mail.badDefaultFrom'));
        }

        if (empty($this->config->defaultFrom['address']))
        {
            throw new \BadMethodCallException(lang('mail.badDefaultFrom'));
        }

        $this->message->setEmails($this->config->defaultFrom['address'], $this->config->defaultFrom['name'], 'from');
    }

    //--------------------------------------------------------------------

    /**
     * Builds the final headers.
     */
    protected function buildHeaders()
    {
        $this->message->setHeader('X-Sender', $this->message->getHeaderLine('From'));
        $this->message->setHeader('X-Mailer', $this->config->userAgent);
        $this->message->setHeader('X-Priority', $this->priorities[$this->message->priority]);
        $this->message->setHeader('Message-ID', $this->getMessageID());
        $this->message->setHeader('Mime-Version', '1.0');
    }

    //--------------------------------------------------------------------

    /**
     * Creates the final body of the message in the format the clients expect to see it.
     * Content type is determine automatically based on whether HTMLContent or TextContent
     * of the message is set.
     *
     * @return bool
     */
    protected function buildMessage(): bool
    {
        if ($this->message->wordwrap === true && ! empty($this->message->textContent))
        {
            $this->message->textContent = $this->wordWrap($this->message->textContent);
        }

        // Take our current headers and fill out to $this->headerString
        $this->writeHeaders();

        // Build the message body basics, depending on the content type.
        switch ($this->getContentType())
        {
            case 'plain':
                $this->body = $this->buildPlainMessage();
                return (bool)$this->body;
            case 'html':
                $this->body = $this->buildHTMLMessage();
                return (bool)$this->body;
            case 'plain-attach':
                $this->body = $this->buildPlainAttachMessage();
                break;
            case 'html-attach':
                $this->body = $this->buildHTMLAttachMessage();
                break;
        }

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Writes the headers our to this->headerString so they can
     * be sent to the client.
     */
    protected function writeHeaders()
    {
        $this->headerString = '';

        foreach ($this->message->getHeaders() as $header)
        {
            $this->headerString .= (string)$header.$this->message->newline;
        }

    }

    //--------------------------------------------------------------------

    /**
     * Builds and formats a plain text message.
     *
     * @return string
     */
    protected function buildPlainMessage(): string
    {
        $header = 'Content-Type: text/plain; charset='.$this->message->charset.$this->message->newline
            .'Content-Transfer-Encoding: '. $this->getEncoding();

        return $header
               .$this->message->newline
               .$this->message->newline
               .$this->message->textContent;
    }

    //--------------------------------------------------------------------

    /**
     * Builds and formats an HTML message.
     *
     * @return string
     */
    protected function buildHTMLMessage(): string
    {
        $body = $this->message->HTMLContent;

        if ($this->message->sendMultipart === false)
        {
            $header = 'Content-Type: text/html; charset='.$this->message->charset.$this->message->newline
                .'Content-Transfer-Encoding: quoted-printable';
        }
        else
        {
            $boundary = uniqid('B_ALT_');
            $header   = 'Content-Type: multipart/alternative; boundary="'.$boundary.'"';

            $body .= $this->getMimeMessage().$this->message->newline
                .'--'.$boundary.$this->message->newline

                .$this->buildPlainMessage()
                .$this->message->newline.$this->message->newline
                .'--'.$boundary.$this->message->newline

                .'Content-Type: text/html; charset='.$this->message->charset.$this->message->newline
                .'Content-Transfer-Encoding: quoted-printable'.$this->message->newline.$this->message->newline;
        }

        $body .= $this->message->prepQuotedPrintable($this->message->HTMLContent)
                 .$this->message->newline.$this->message->newline;

        $body = $header.$this->message->newline.$this->message->newline.$body;

        if ($this->message->sendMultipart !== false)
        {
            $body .= '--'.$boundary.'--';
        }

        return $body;
    }

    //--------------------------------------------------------------------

    protected function buildPlainAttachMessage(): string
    {

    }

    //--------------------------------------------------------------------

    protected function buildHTMLAttachMessage(): string
    {

    }

    //--------------------------------------------------------------------

    /**
     * Mime message
     *
     * @return string
     */
    public function getMimeMessage(): string
    {
        return 'This is a multi-part message in MIME format.'
               .$this->message->newline
               .'Your email application may not support this format.';
    }

    //--------------------------------------------------------------------

    /**
     * Get the Mail Encoding
     *
     * @param bool $return
     *
     * @return string
     */
    protected function getEncoding(bool $return = true): string
    {
        in_array($this->message->encoding, $this->bitDepths) || $this->message->encoding = '8bit';

        foreach ($this->baseCharsets as $charset)
        {
            if (strpos($charset, $this->message->charset) === 0)
            {
                $this->message->encoding = '7bit';
            }
        }

        if ($return === true)
        {
            return $this->message->encoding;
        }
    }

    //--------------------------------------------------------------------

    /**
     * Determines the message's content type based on the status of
     * the HTMLContent and textContent Message parameters.
     *
     * @return string
     */
    protected function getContentType(): string
    {
        if (! empty($this->message->HTMLContent))
        {
            return empty($this->message->attachments)
                ? 'html'
                : 'html-attach';
        }

        if (! empty($this->message->textContent) && ! empty($this->message->attachments))
        {
            return 'plain-attach';
        }

        return 'plain';
    }

    //--------------------------------------------------------------------

    /**
     * Wraps the text to fit the specified width.
     *
     * @param string   $str
     * @param int|null $charLimit
     *
     * @return mixed|string
     */
    protected function wordWrap(string $str, int $charLimit = null)
    {
        // Set the character limit if not already present.
        if (empty($charLimit))
        {
            $charLimit = $this->message->wrapChars ?? 76;
        }

        // Standardize newlines
        if (strpos($str, "\r") !== false)
        {
            $str = str_replace(["\r\n", "\r"], "\n", $str);
        }

        // Reduce multiple spaces at end of line
        $str = preg_replace('| +\n|', "\n", $str);

        // If the current word is surround by {unwrap} tags we'll
        // strip the entire chunk and replace it with a marker.
        $unwrap = [];
        if (preg_match_all('|\{unwrap\}(.+?)\{/unwrap\}|s', $str, $matches))
        {
            for ($i = 0, $c = count($matches[0]); $i < $c; $i++)
            {
                $unwrap[] = $matches[1][$i];
                $str      = str_replace($matches[0][$i], '{{unwrapped'.$i.'}}', $str);
            }
        }

        // Use PHP's native function to do the initial wordwrap.
        // We set the cut flag to false so that any individual words that are
        // too long get left alone. In the next step we'll deal with them.
        $str = wordwrap($str, $charLimit, "\n", false);

        // Split the string into individual lines of text and cycle through them.
        $output = '';
        foreach (explode("\n", $str) as $line)
        {
            // Is the line within the allowed character count?
            // If so we'll join it to the output and continue
            if (mb_strlen($line) <= $charLimit)
            {
                $output .= $line.$this->message->newline;
                continue;
            }

            $temp = '';
            do
            {
                // If the over-length word is a URL we won't wrap it
                if (preg_match('!\[url.+\]|://|www\.!', $line))
                {
                    break;
                }

                // Trim the word down
                $temp .= mb_substr($line, 0, $charLimit-1);
                $line  = mb_substr($line, $charLimit-1);
            }
            while (mb_strlen($line) > $charLimit);

            // If $temp contains data it means we had to split up an over-length
            // word into smaller chunks so we'll add it back to our current line
            if ($temp !== '')
            {
                $output .= $temp.$this->message->newline;
            }

            $output .= $line.$this->message->newline;
        }

        // Put our markers back
        if (count($unwrap) > 0)
        {
            foreach ($unwrap as $key => $val)
            {
                $output = str_replace('{{unwrapped'.$key.'}}', $val, $output);
            }
        }

        return $output;
    }

    //--------------------------------------------------------------------

}
