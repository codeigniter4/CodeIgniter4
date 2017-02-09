<?php namespace CodeIgniter\Mail\Handlers;

use CodeIgniter\Mail\BaseHandler;
use CodeIgniter\Mail\MessageInterface;

class MailHandler extends BaseHandler
{
    /**
     * Path to the Sendmail binary.
     *
     * @var    string
     */
    public $mailpath = '/usr/sbin/sendmail';    // Sendmail path

    /**
     * Which method to use for sending e-mails.
     *
     * @var    string    'mail', 'sendmail' or 'smtp'
     */
    public $protocol = 'mail';        // mail/sendmail/smtp

    /**
     * STMP Server host
     *
     * @var    string
     */
    public $SMTPHost = '';

    /**
     * SMTP Username
     *
     * @var    string
     */
    public $SMTPUser = '';

    /**
     * SMTP Password
     *
     * @var    string
     */
    public $SMTPPass = '';

    /**
     * SMTP Server port
     *
     * @var    int
     */
    public $SMTPPort = 25;

    /**
     * SMTP connection timeout in seconds
     *
     * @var    int
     */
    public $SMTPTimeout = 5;

    /**
     * SMTP persistent connection
     *
     * @var    bool
     */
    public $SMTPKeepalive = false;

    /**
     * SMTP Encryption
     *
     * @var    string    empty, 'tls' or 'ssl'
     */
    public $SMTPCrypto = '';

    /**
     * Whether to apply word-wrapping to the message body.
     *
     * @var    bool
     */
    public $wordwrap = true;

    /**
     * Number of characters to wrap at.
     *
     * @see    CI_Email::$wordwrap
     * @var    int
     */
    public $wrapchars = 76;

    //--------------------------------------------------------------------

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->SMTPAuth = isset($this->SMTPUser[0], $this->SMTPPass[0]);
        $this->protocol = strtolower($this->protocol);
    }

    /**
     * Does the actual delivery of a message.
     *
     * @param \CodeIgniter\Mail\MessageInterface $message
     * @param bool                               $clear_after If TRUE, will reset the class after sending.
     *
     * @return mixed
     */
    public function send(MessageInterface $message, bool $clear_after = true)
    {
        $this->setMessage($message);

        // First, get and format all of our emails (from, to, cc, etc)
        $this->initialize();
        ddd($this);
    }

    //--------------------------------------------------------------------

    public function initialize()
    {
        // Set the appropriate headers with formatted versions
        // of all of our recipients and senders.
        foreach (['From', 'To', 'ReplyTo', 'CC', 'BCC'] as $group)
        {
            $emails = $this->message->{'get'.$group}();

            if (empty($emails))
            {
                continue;
            }

            if (method_exists($this, 'set'.$group))
            {
                $this->{'set'.$group}($emails);
            }
        }
    }

    //--------------------------------------------------------------------

    /**
     * Sets and formats the email(s) the message should be sent to.
     *
     * @param array $emails
     */
    protected function setTo(array $emails)
    {
        if ($this->validate)
        {
            $this->validateEmail($emails);
        }

        $emails = $this->formatEmails($emails);

        if ($this->protocol != 'mail')
        {
            $this->setHeader('To', implode(', ', $emails));
        }

        $this->recipients = $emails;
    }

    //--------------------------------------------------------------------

    /**
     * Sets and formats the email(s) the message is being sent from.
     *
     * @param array $emails
     */
    protected function setFrom(array $emails)
    {
        if ($this->validate)
        {
            $this->validateEmail($emails);
        }

        $emails = $this->cleanNames($emails);
        $emails = $this->formatEmails($emails);

        $this->setHeader('From', implode(', ', $emails));
    }

    //--------------------------------------------------------------------

    /**
     * Validates an email address.
     *
     * @param array $emails
     *
     * @internal param string $email
     */
    protected function validateEmail(array $emails)
    {
        if (! is_array($emails))
        {
            $this->setErrorMessage(lang('mail.mustBeArray'));
        }

        foreach ($emails as $email)
        {
            if (is_array($email))
            {
                $email = array_shift($email);
            }

            // If the email isn't valid, then log it so
            // we can show the user in debug info.
            if (! $this->isValidEmail($email))
            {
                $this->setErrorMessage(lang('mail.invalidEmail'), $email);
            }
        }
    }

    //--------------------------------------------------------------------

    /**
     * Validates an email address.
     *
     * @param string $email
     *
     * @return bool
     */
    protected function isValidEmail(string $email)
    {
        if (function_exists('idn_to_ascii') && $atpos = strpos($email, '@'))
        {
            $email = self::substr($email, 0, ++$atpos).idn_to_ascii(self::substr($email, $atpos));
        }

        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    //--------------------------------------------------------------------

    /**
     * Takes an array of 'name' => 'email' pairs and
     * formats them into a proper email string:
     *
     *      'name <email>'
     *
     * @param array $emails
     *
     * @return array
     */
    protected function formatEmails(array $emails)
    {
        $formatted = [];

        foreach ($emails as $name => $email)
        {
            $formatted[] = trim("$name <{$email}>");
        }

        return $formatted;
    }

    //--------------------------------------------------------------------

    /**
     * Cleans the Names associated with email addresses to prepare them for display
     * and sanitize them a bit, and standardize for mail delivery.
     *
     * @param array $emails
     *
     * @return array
     */
    protected function cleanNames(array $emails)
    {
        $cleaned = [];

        foreach ($emails as $name => $email)
        {
            if ($name !== '')
            {
                // only use Q encoding if there are characters that would require it
                if (! preg_match('/[\200-\377]/', $name))
                {
                    // add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
                    $name = '"'.addcslashes($name, "\0..\37\177'\"\\").'"';
                } else
                {
                    $name = $this->prepQEncoding($name);
                }

                $cleaned[$name] = $email;
            } else
            {
                $cleaned[] = $email;
            }
        }

        return $cleaned;
    }

    //--------------------------------------------------------------------

    /**
     * Adds an attachment to the current email that is being built.
     *
     * @param string $filename
     * @param string $disposition like 'inline'. Default is 'attachment'
     * @param string $newname     If you'd like to rename the file for delivery
     * @param string $mime        Custom defined mime type.
     */
    public function attach(string $filename, string $disposition = null, string $newname = null, string $mime = null)
    {

    }

    //--------------------------------------------------------------------

    /**
     * Sets a header value for the email. Not every service will provide this.
     *
     * @param string $header
     * @param        $value
     *
     * @return mixed
     * @internal param $field
     */
    public function setHeader(string $header, $value)
    {
        $this->headers[$header] = str_replace(["\n", "\r"], '', $value);
    }

    //--------------------------------------------------------------------

    /**
     * Prep Q Encoding
     *
     * Performs "Q Encoding" on a string for use in email headers.
     * It's related but not identical to quoted-printable, so it has its
     * own method.
     *
     * @param    string
     *
     * @return    string
     */
    protected function prepQEncoding($str)
    {
        $str = str_replace(["\r", "\n"], '', $str);

        if ($this->charset === 'UTF-8')
        {
            // Note: We used to have mb_encode_mimeheader() as the first choice
            //       here, but it turned out to be buggy and unreliable. DO NOT
            //       re-add it! -- Narf
            if (ICONV_ENABLED === true)
            {
                $output = @iconv_mime_encode('', $str,
                    [
                        'scheme'           => 'Q',
                        'line-length'      => 76,
                        'input-charset'    => $this->charset,
                        'output-charset'   => $this->charset,
                        'line-break-chars' => $this->crlf,
                    ]
                );

                // There are reports that iconv_mime_encode() might fail and return FALSE
                if ($output !== false)
                {
                    // iconv_mime_encode() will always put a header field name.
                    // We've passed it an empty one, but it still prepends our
                    // encoded string with ': ', so we need to strip it.
                    return self::substr($output, 2);
                }

                $chars = iconv_strlen($str, 'UTF-8');
            } elseif (MB_ENABLED === true)
            {
                $chars = mb_strlen($str, 'UTF-8');
            }
        }

        // We might already have this set for UTF-8
        isset($chars) OR $chars = self::strlen($str);

        $output = '=?'.$this->charset.'?Q?';
        for ($i = 0, $length = self::strlen($output); $i < $chars; $i++)
        {
            $chr = ($this->charset === 'UTF-8' && ICONV_ENABLED === true)
                ? '='.implode('=', str_split(strtoupper(bin2hex(iconv_substr($str, $i, 1, $this->charset))), 2))
                : '='.strtoupper(bin2hex($str[$i]));

            // RFC 2045 sets a limit of 76 characters per line.
            // We'll append ?= to the end of each line though.
            if ($length+($l = self::strlen($chr)) > 74)
            {
                $output .= '?='.$this->crlf // EOL
                           .' =?'.$this->charset.'?Q?'.$chr; // New line
                $length = 6+self::strlen($this->charset)+$l; // Reset the length for the new line
            } else
            {
                $output .= $chr;
                $length += $l;
            }
        }

        // End the header
        return $output.'?=';
    }

    //--------------------------------------------------------------------

    /**
     * Resets the state to blank, ready for a new email. Useful when
     * sending emails in a loop and you need to make sure that the
     * email is reset.
     *
     * @param bool $clear_attachments
     *
     * @return mixed
     */
    public function reset(bool $clear_attachments = true)
    {

    }

    //--------------------------------------------------------------------

    /**
     * Stores an error message for later debug info, with optional
     * string replacement.
     *
     * @param string $message
     * @param string $val
     */
    protected function setErrorMessage(string $message, string $val = '')
    {
        $this->debugMsg[] = str_replace('%s', $val, $message).'<br/>';
    }

    //--------------------------------------------------------------------

    /**
     * Byte-safe substr()
     *
     * @param    string $str
     * @param    int    $start
     * @param    int    $length
     *
     * @return    string
     */
    protected static function substr($str, $start, $length = null)
    {
        return mb_substr($str, $start, $length, '8bit');
    }
}
