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

        if (! isset($this->headers['From']))
        {
            $this->setErrorMessage(lang('mail.noFrom'));
            return false;
        }

        if ($this->ReplyToFlag === false)
        {
//            $this->setReplyTo($this->headers)
        }
    }

    //--------------------------------------------------------------------

    /**
     * Reads in our emails and other data from the message and converting
     * into the format we need it in.
     */
    protected function initialize()
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

        $this->setSubject($this->message->getSubject());
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
     * Sets and formats the email(s) the message should be replied to.
     *
     * @param array $emails
     */
    protected function setReplyTo(array $emails)
    {
        if ($this->validate)
        {
            $this->validateEmail($emails);
        }

        $emails = $this->cleanNames($emails);
        $emails = $this->formatEmails($emails);

        $this->setHeader('Reply-To', implode(', ', $emails));

        $this->ReplyToFlag = true;
    }

    //--------------------------------------------------------------------

    /**
     * Sets and formats the email(s) the message should be CC'd to.
     *
     * @param array $emails
     */
    protected function setCC(array $emails)
    {
        if ($this->validate)
        {
            $this->validateEmail($emails);
        }

        $emails = $this->cleanNames($emails);
        $emails = $this->formatEmails($emails);

        $this->setHeader('Cc', implode(', ', $emails));

        if ($this->protocol == 'smtp')
        {
            $this->CC = $emails;
        }
    }

    //--------------------------------------------------------------------

    /**
     * Sets and formats the email(s) the message should be BCC'd to.
     *
     * @param array $emails
     */
    protected function setBCC(array $emails)
    {
        if ($this->validate)
        {
            $this->validateEmail($emails);
        }

        $emails = $this->cleanNames($emails);
        $emails = $this->formatEmails($emails);

        $this->setHeader('Bcc', implode(', ', $emails));

        if ($this->protocol == 'smtp')
        {
            $this->BCC = $emails;
        }
    }

    //--------------------------------------------------------------------

    /**
     * Sets the email subject header.
     *
     * @param string $subject
     *
     * @return $this
     */
    protected function setSubject(string $subject)
    {
        $subject = $this->prepQEncoding($subject);
        $this->setHeader('Subject', $subject);

        return $this;
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
}
