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

    /**
     * Stores the headers as a single string.
     *
     * @var string
     */
    protected $headerString;

    /**
     * The final body, once generated.
     *
     * @var string
     */
    protected $finalBody;

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
            return $this;
        }

        if ($this->ReplyToFlag === false)
        {
            $this->setReplyTo($this->headers['From']);
        }

        if (empty($this->recipients) && ! isset($this->headers['To'])
            && empty($this->BCC) && ! isset($this->headers['Bcc'])
            && ! isset($this->headers['Cc']))
        {
            $this->setErrorMessage(lang('mail.noRecipients'));
            return $this;
        }

        $this->buildHeaders();

        if ($this->buildMessage() === false)
        {
            return $this;
        }

        $result = $this->spoolEmail();

        if ($result && $clear_after)
        {
            $this->reset();
        }

        return $result;
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
     * @param array|string $emails
     */
    protected function setReplyTo($emails)
    {
        if (is_string($emails))
        {
            $emails = [$emails];
        }

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
     * Validate email for shell
     *
     * Applies stricter, shell-safe validation to email addresses.
     * Introduced to prevent RCE via sendmail's -f option.
     *
     * @see	https://github.com/bcit-ci/CodeIgniter/issues/4963
     * @see	https://gist.github.com/Zenexer/40d02da5e07f151adeaeeaa11af9ab36
     * @license	https://creativecommons.org/publicdomain/zero/1.0/	CC0 1.0, Public Domain
     *
     * Credits for the base concept go to Paul Buonopane <paul@namepros.com>
     *
     * @param	string	$email
     * @return	bool
     */
    protected function validateEmailForShell(&$email)
    {
        if (function_exists('idn_to_ascii') && $atpos = strpos($email, '@'))
        {
            $email = mb_substr($email, 0, ++$atpos).idn_to_ascii(mb_substr($email, $atpos));
        }

        return (filter_var($email, FILTER_VALIDATE_EMAIL) === $email && preg_match('#\A[a-z0-9._+-]+@[a-z0-9.-]{1,253}\z#i', $email));
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
     * Clean Extended Email address: Joe Smith <joe@smith.com>
     *
     * @param string $email
     *
     * @return string
     */
    protected function cleanEmail(string $email): string
    {
        if ( ! is_array($email))
        {
            return preg_match('/\<(.*)\>/', $email, $match) ? $match[1] : $email;
        }

        $cleanEmail = array();

        foreach ($email as $addy)
        {
            $cleanEmail[] = preg_match('/\<(.*)\>/', $addy, $match) ? $match[1] : $addy;
        }

        return $cleanEmail;
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
     * Build the final headers.
     */
    protected function buildHeaders()
    {
        $this->setHeader('User-Agent', $this->useragent);
        $this->setHeader('X-Sender', $this->headers['From']);
        $this->setHeader('X-Mailer', $this->useragent);
        $this->setHeader('X-Priority', $this->priorities[$this->priority]);
        $this->setHeader('Message-ID', $this->getMessageID());
        $this->setHeader('Mime-Version', '1.0');
    }

    //--------------------------------------------------------------------

    /**
     * Get the Message ID
     *
     * @return string
     */
    protected function getMessageID()
    {
        $from = $this->headers['Return-Path'] ?? $this->headers['From'];

        $from = str_replace(array('>', '<'), '', $from);
        return '<'.uniqid('').strstr($from, '@').'>';
    }

    //--------------------------------------------------------------------

    protected function buildMessage()
    {
        if ($this->wordwrap === true)
        {
            $this->message->setTextMessage($this->wordWrap($this->message->getTextMessage()));
        }

        $this->writeHeaders();

        $header = ($this->protocol === 'mail')
            ? $this->newline
            : '';

        $body = '';

        switch ($this->message->messageType())
        {
            case 'plain':
                $body = $this->buildPlainMessage($header);
                break;
            case 'html':
                $body = $this->buildHTMLMessage($header);
                break;
            case 'plain-attach':
                $body = $this->buildPlainAttachMessage($header);
                break;
            case 'html-attach':
                $body = $this->buildHTMLAttachMessage($header);
                break;
        }

        $this->finalBody = $this->protocol === 'mail'
            ? $body
            : $header.$this->newline.$this->newline.$body;

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Build the final body for a Plain (text-only) message.
     *
     * @param string $header
     *
     * @return string
     */
    protected function buildPlainMessage(string &$header): string
    {
        $header .= 'Content-Type: text/plain; charset='.$this->charset.$this->newline
            .'Content-Transfer-Encoding: '.$this->getEncoding(true);

        if ($this->protocol === 'mail')
        {
            $this->headerString .= $header;
            return $this->message->getTextMessage();
        }

        return $header.$this->newline.$this->newline.$this->message->getTextMessage();
    }

    //--------------------------------------------------------------------

    /**
     * Build the final body for an HTML message.
     *
     * @param string $header
     *
     * @return string
     */
    protected function buildHTMLMessage(string &$header): string
    {
        $body = '';

        if ($this->sendMultipart === false)
        {
            $header .= 'Content-Type: text/html; charset='.$this->charset.$this->newline
                .'Content-Transfer-Encoding: quoted-printable';
        }
        else
        {
            $boundary = uniqid('B_ALT_');
            $header = 'Content-Type: multipart/alternative; boundary="'.$boundary.'"';

            $body = $this->getMimeMessage().$this->newline.$this->newline
                .'--'.$boundary.$this->newline
                .'Content-Type: text/plain; charset='.$this->charset.$this->newline
                .'Content-Transfer-Encoding: '.$this->getEncoding(true).$this->newline.$this->newline
                .$this->message->getTextMessage().$this->newline.$this->newline
                .'--'.$boundary.$this->newline
                .'Content-Type: text/html; charset='.$this->charset.$this->newline
                .'Content-Transfer-Encoding: quoted-printable'.$this->newline.$this->newline;
        }

        $body = $body.$this->prepQuotedPrintable($this->message->getHTMLMessage()).$this->newline.$this->newline;

        if ($this->protocol === 'mail')
        {
            $this->headerString .= $header;
        }
        else
        {
            $body = $header.$this->newline.$this->newline.$body;
        }

        if ($this->sendMultipart !== false)
        {
            $body .= '--'.$boundary.'--';
        }

        return $body;
    }

    //--------------------------------------------------------------------

    /**
     * Build the final body for a plain email with attachments.
     *
     * @param string $header
     *
     * @return string
     */
    protected function buildPlainAttachMessage(string &$header): string
    {
        $boundary = uniqid('B_ATAC_');
        $header .= 'Content-Type: multipart/mixed; boundary="'.$boundary.'"';

        if ($this->protocol === 'mail')
        {
            $this->headerString .= $header;
        }

        $body = $this->getMimeMessage().$this->newline
            .$this->newline
            .'--'.$boundary.$this->newline
            .'Content-Type: text/plain; charset='.$this->charset.$this->newline
            .'Content-Transfer-Encoding: '.$this->getEncoding(true).$this->newline
            .$this->newline
            .$this->message->getTextMessage().$this->newline.$this->newline;

        return $this->appendAttachments($body, $boundary);
    }

    //--------------------------------------------------------------------

    /**
     * Build the final body for an HTML message with attachments.
     *
     * @param string $header
     *
     * @return string
     */
    protected function buildHTMLAttachMessage(string &$header): string
    {
        $altBoundary  = uniqid('B_ALT_');
        $lastBoundary = null;

        $body = '';

        if ($this->attachmentsHaveMultipart('mixed'))
        {
            $atcBoundary = uniqid('B_ATC_');
            $header .= 'Content-Type: multipart/mixed; boundary="'.$atcBoundary.'"';
            $lastBoundary = $atcBoundary;
        }

        if ($this->attachmentsHaveMultipart('related'))
        {
            $relBoundary = uniqid('B_REL_');
            $relBoundaryHeader = 'Content-Type: multipart/related; boundary="'.$relBoundary.'"';

            if (isset($lastBoundary))
            {
                $body .= '--'.$lastBoundary.$this->newline.$relBoundaryHeader;
            }
            else
            {
                $header .= $relBoundaryHeader;
            }

            $lastBoundary = $relBoundary;
        }

        if ($this->protocol === 'mail')
        {
            $this->headerString .= $header;
        }

        if (mb_strlen($body))
        {
            $body .= $this->newline.$this->newline;
        }

        $body .= $this->getMimeMessage().$this->newline.$this->newline
            .'--'.$lastBoundary.$this->newline

            .'Content-Type: multipart/alternative; boundary="'.$altBoundary.'"'.$this->newline.$this->newline
            .'--'.$altBoundary.$this->newline

            .'Content-Type: text/plain; charset='.$this->charset.$this->newline
            .'Content-Transfer-Encoding: '.$this->getEncoding(true).$this->newline.$this->newline
            .$this->message->getTextMessage().$this->newline.$this->newline
            .'--'.$altBoundary.$this->newline

            .'Content-Type: text/html; charset='.$this->charset.$this->newline
            .'Content-Transfer-Encoding: quoted-printable'.$this->newline.$this->newline

            .$this->prepQuotedPrintable($this->message->getHTMLMessage()).$this->newline.$this->newline
            .'--'.$altBoundary.'--'.$this->newline.$this->newline;

        if (! empty($relBoundary))
        {
            $body .= $this->newline.$this->newline;
            $body = $this->appendAttachments($body, $relBoundary, 'related');
        }

        // multipart/mixed attachments
        if (! empty($atcBoundary))
        {
            $body .= $this->newline.$this->newline;
            $body = $this->appendAttachments($body, $atcBoundary, 'mixed');
        }

        return $body;
    }

    //--------------------------------------------------------------------

    /**
     * Checks whether we have any attachments of the specified type.
     *
     * @param string $type
     *
     * @return bool
     */
    protected function attachmentsHaveMultipart(string $type): bool
    {
        foreach ($this->attachments as $attachment)
        {
            if ($attachment['multipart'] === $type)
            {
                return true;
            }
        }

        return false;
    }

    //--------------------------------------------------------------------

    /**
     * Prepares attachment string.
     *
     * @param string      $body       Message body to append to
     * @param string      $boundary   Multipart boundary
     * @param string|null $multipart  When provided, only attachments of this type will be processed
     *
     * @return string
     */
    protected function appendAttachments(string $body, string $boundary, string $multipart = null)
    {
        for ($i = 0, $c = count($this->attachments); $i < $c; $i++)
        {
            if (isset($multipart) && $this->attachments[$i]['multipart'] != $multipart)
            {
                continue;
            }

            $name = isset($this->attachments[$i]['name'][1])
                ? $this->attachments[$i]['name'][1]
                : basename($this->attachments[$i]['name'][0]);

            $body .= '--'.$boundary.$this->newline
                .'Content-Type: '.$this->attachments[$i]['type'].'; name="'.$name.'"'.$this->newline
                .'Content-Disposition: '.$this->attachments[$i]['disposition'].';'.$this->newline
                .'Content-Transfer-Encoding: base64'.$this->newline
                .(empty($this->attachments[$i]['cid']) ? '' : 'Content-ID: <'.$this->attachments[$i]['cid'].'>'.$this->newline)
                .$this->newline
                .$this->attachments[$i]['content'].$this->newline;
        }

        // $name won't be set if no attachments were appended,
        // and therefore a boundary wouldn't be necessary
        if (! empty($name))
        {
            $body .= '--'.$boundary.'--';
        }

        return $body;
    }
    /**
     * Get the mail encoding.
     *
     * @param bool $return
     *
     * @return string
     */
    protected function getEncoding($return = true)
    {
        if (! in_array($this->encoding, $this->bitDepths))
        {
            $this->encoding = '8bit';
        }

        foreach ($this->baseCharsets as $charset)
        {
            if (strpos($charset, $this->charset) === 0)
            {
                $this->encoding = '7bit';
            }
        }

        if ($return === true)
        {
            return $this->encoding;
        }
    }
    /**
     * Handle word-wrapping.
     *
     * @param string $str
     * @param null   $charLimit
     *
     * @return string
     */
    protected function wordWrap(string $str, $charLimit = null): string
    {
        // Set the character limit, if not already present
        if (empty($charLimit))
        {
            $charLimit = empty($this->wrapchars)
                ? 76
                : $this->wrapchars;
        }

        // Standardize newlines
        if (strpos($str, "\r") !== false)
        {
            $str = str_replace(["\r\n", "\r"], "\n", $str);
        }

        // reduce multiple spaces at end of line
        $str = preg_replace('| +\n|', "\n", $str);

        // If the current word is surround by {unwrap} tags we'll
        // strip the entire chunk and replace it with a marker.
        $unwrap = [];
        if (preg_match_all('|\{unwrap\}(.+?)\{/unwrap\}|s', $str, $matches))
        {
            for ($i = 0, $c = count($matches[0]); $i < $c; $i++)
            {
                $unwrap[] = $matches[1][$i];
                $str = str_replace($matches[0][$i], '{{unwrapped'.$i.'}}', $str);
            }
        }

        // We'll use PHP's native function to do the initial wordwrap.
        // We set the cut flag to false so that any individual words that are
        // too long get left alone. In the next step we'll deal with them.
        $str = wordwrap($str, $charLimit, "\n", false);

        // Split the string into individual lines of tet and cycle through them.
        $output = '';
        foreach (explode("\n", $str) as $line)
        {
            // Is the line within the allowed character count?
            // If so we'll join it to the output and continue.
            if (mb_strlen($line) <= $charLimit)
            {
                $output .= $line.$this->newline;
                continue;
            }

            $temp = '';
            do
            {
                // If the over-length word is a URL we son't wrap it
                if (preg_match('!\[url.+\]|://|www\.!', $line))
                {
                    break;
                }

                // Trim the word down
                $temp .= mb_substr($line, 0, $charLimit-1);
                $line = mb_substr($line, $charLimit-1);
            }
            while (mb_strlen($line) > $charLimit);

            // If temp contains data it means we had to split up an over-length
            // word into smaller chunks so we'll add it back to our current line
            if ($temp !== '')
            {
                $output .= $temp.$this->newline;
            }
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

    public function writeHeaders()
    {
        if ($this->protocol === 'mail')
        {
            // Get Subject out of the header and into the message itself.
            if (isset($this->headers['Subject']))
            {
                $this->message->setSubject($this->headers['Subject']);
                unset($this->headers['Subject']);
            }
        }

        reset($this->headers);
        $this->headerString = '';

        foreach ($this->headers as $key => $val)
        {
            $val = trim($val);

            if ($val !== '')
            {
                $this->headerString .= $key.': '.$val.$this->newline;
            }
        }

        if ($this->protocol === 'mail')
        {
            $this->headerString = rtrim($this->headerString);
        }
    }

    //--------------------------------------------------------------------

    /**
     * Mime message
     *
     * @return string
     */
    protected function getMimeMessage(): string
    {
        return 'This is a multi-part message in MIME format.'.$this->newline.'Your email application may not support this format.';
    }

    //--------------------------------------------------------------------

    /**
     * Spool mail to the mail server.
     *
     * @return bool
     */
    protected function spoolEmail(): bool
    {
        $this->unwrapSpecials();

        $method = 'sendWith'.ucfirst(strtolower($this->protocol));

        if (! $this->$method())
        {
            $this->setErrorMessage(lang('email.sendFailure'.$this->protocol == 'mail' ? 'phpmail' : $this->protocol));

            return false;
        }

        $this->setErrorMessage(lang('email.sent'. $this->protocol));

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Unwrap special elements.
     */
    protected function unwrapSpecials()
    {
        $this->finalBody = preg_replace_callback('/\{unwrap\}(.*?)\{\/unwrap\}/si', array($this, 'removeNLCallback'), $this->finalBody);
    }

    //--------------------------------------------------------------------

    /**
     * Strip line-breaks via callback
     *
     * @param array $matches
     *
     * @return string
     */
    protected function removeNLCallback(array $matches): string
    {
        if (strpos($matches[1], "\r") !== FALSE OR strpos($matches[1], "\n") !== FALSE)
        {
            $matches[1] = str_replace(array("\r\n", "\r", "\n"), '', $matches[1]);
        }

        return $matches[1];
    }

    //--------------------------------------------------------------------

    /**
     * Send using mail()
     *
     * return bool
     */
    protected function sendWithMail(): bool
    {
        if (is_array($this->recipients))
        {
            $this->recipients = implode(', ', $this->recipients);
        }

        // validateEmailForShell below accepts by reference
        // so this needs to be assigned to a variable.
        $from = $this->cleanEmail($this->message->getReturnPath());

        if (! $this->validateEmailForShell($from))
        {
            return mail(
                $this->recipients,
                $this->message->getSubject(),
                $this->finalBody,
                $this->headerString
            );
        }

        // most documentation of sendmail using the "-f" flag lacks a space after it, however
        // we've encountered servers that seem to require it to be in place.
        return mail(
            $this->recipients,
            $this->message->getSubject(),
            $this->finalBody,
            $this->headerString,
            '-f '.$from
        );
    }

    //--------------------------------------------------------------------

    public function sendWithSendmail()
    {
        // _validate_email_for_shell() below accepts by reference,
        // so this needs to be assigned to a variable
        $from = $this->cleanEmail($this->headers['From']);
        if ($this->validateEmailForShell($from))
        {
            $from = '-f '.$from;
        }
        else
        {
            $from = '';
        }

        // is popen() enabled?
        if ( ! function_usable('popen')	OR false === ($fp = @popen($this->mailpath.' -oi '.$from.' -t', 'w')))
        {
            // server probably has popen disabled, so nothing we can do to get a verbose error.
            return false;
        }

        fputs($fp, $this->headerString);
        fputs($fp, $this->finalBody);

        $status = pclose($fp);

        if ($status !== 0)
        {
            $this->setErrorMessage('lang:email_exit_status', $status);
            $this->setErrorMessage('lang:email_no_socket');
            return false;
        }

        return true;
    }

    //--------------------------------------------------------------------

    public function sendWithSmtp()
    {
        if ($this->config->SMTPHost === '')
        {
            $this->setErrorMessage('lang:email_no_hostname');
            return FALSE;
        }

        if ( ! $this->SMTPConnect() OR ! $this->SMTPAuthenticate())
        {
            return FALSE;
        }

        if ( ! $this->sendCommand('from', $this->cleanEmail($this->headers['From'])))
        {
            $this->SMTPEnd();
            return FALSE;
        }

        foreach ($this->recipients as $val)
        {
            if ( ! $this->sendCommand('to', $val))
            {
                $this->SMTPEnd();
                return FALSE;
            }
        }

        if (count($this->message->getCC()) > 0)
        {
            foreach ($this->message->getCC() as $val)
            {
                if ($val !== '' && ! $this->sendCommand('to', $val))
                {
                    $this->SMTPEnd();
                    return FALSE;
                }
            }
        }

        if (count($this->message->getBCC()) > 0)
        {
            foreach ($this->message->getBCC() as $val)
            {
                if ($val !== '' && ! $this->sendCommand('to', $val))
                {
                    $this->SMTPEnd();
                    return FALSE;
                }
            }
        }

        if ( ! $this->sendCommand('data'))
        {
            $this->SMTPEnd();
            return FALSE;
        }

        // perform dot transformation on any lines that begin with a dot
        $this->sendData($this->headerString.preg_replace('/^\./m', '..$1', $this->finalBody));

        $this->sendData('.');

        $reply = $this->getSMTPData();
        $this->setErrorMessage($reply);

        $this->SMTPEnd();

        if (strpos($reply, '250') !== 0)
        {
            $this->setErrorMessage('lang:email_smtp_error', $reply);
            return false;
        }

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * SMTP End
     *
     * Shortcut to send RSET or QUIT depending on keepalive
     */
    protected function SMTPEnd()
    {
        $this->SMTPKeepalive
            ? $this->sendCommand('reset')
            : $this->sendCommand('quit');
    }

    //--------------------------------------------------------------------

    protected function SMTPConnect()
    {
        if (is_resource($this->SMTPConnect)
        {
            return TRUE;
        }

        $ssl = ($this->SMTPCrypto === 'ssl') ? 'ssl://' : '';

        $this->SMTPConnect = fsockopen($ssl.$this->SMTPHost,
            $this->SMTPPort,
            $errno,
            $errstr,
            $this->SMTPTimeout);

        if ( ! is_resource($this->SMTPConnect))
        {
            $this->setErrorMessage(lang('email.smtpError'. $errno.' '.$errstr));
            return FALSE;
        }

        stream_set_timeout($this->SMTPConnect, $this->SMTPTimeout);
        $this->setErrorMessage($this->getSMTPData());

        if ($this->SMTPCrypto === 'tls')
        {
            $this->sendCommand('hello');
            $this->sendCommand('starttls');

            $crypto = stream_socket_enable_crypto($this->SMTPConnect, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            if ($crypto !== true)
            {
                $this->setErrorMessage(lang('email.smtp_error'. $this->_get_smtp_data()));
                return false;
            }
        }

        return $this->sendCommand('hello');
    }

    //--------------------------------------------------------------------

    /**
     * Send SMTP Command
     *
     * @param string $cmd
     * @param string $data
     *
     * @return bool
     */
    protected function sendCommand(string $cmd, string $data): bool
    {
        switch ($cmd)
        {
            case 'hello' :

                if ($this->SMTPAuth OR $this->getEncoding() === '8bit')
                {
                    $this->sendData('EHLO '.$this->getHostname());
                }
                else
                {
                    $this->sendData('HELO '.$this->getHostname());
                }

                $resp = 250;
                break;
            case 'starttls'	:

                $this->sendData('STARTTLS');
                $resp = 220;
                break;
            case 'from' :

                $this->sendData('MAIL FROM:<'.$data.'>');
                $resp = 250;
                break;
            case 'to' :

                if ($this->dsn)
                {
                    $this->sendData('RCPT TO:<'.$data.'> NOTIFY=SUCCESS,DELAY,FAILURE ORCPT=rfc822;'.$data);
                }
                else
                {
                    $this->sendData('RCPT TO:<'.$data.'>');
                }

                $resp = 250;
                break;
            case 'data'	:

                $this->sendData('DATA');
                $resp = 354;
                break;
            case 'reset':

                $this->sendData('RSET');
                $resp = 250;
                break;
            case 'quit'	:

                $this->sendData('QUIT');
                $resp = 221;
                break;
        }

        $reply = $this->getSMTPData();

        $this->debugMsg[] = '<pre>'.$cmd.': '.$reply.'</pre>';

        if ((int) mb_substr($reply, 0, 3) !== $resp)
        {
            $this->setErrorMessage(lang('email.smtpError'. $reply));
            return false;
        }

        if ($cmd === 'quit')
        {
            fclose($this->SMTPConnect);
        }

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Get Hostname
     *
     * There are only two legal types of hostname - either a fully
     * qualified domain name (eg: "mail.example.com") or an IP literal
     * (eg: "[1.2.3.4]").
     *
     * @link	https://tools.ietf.org/html/rfc5321#section-2.3.5
     * @link	http://cbl.abuseat.org/namingproblems.html
     * @return	string
     */
    protected function getHostname()
    {
        if (isset($_SERVER['SERVER_NAME']))
        {
            return $_SERVER['SERVER_NAME'];
        }

        return isset($_SERVER['SERVER_ADDR']) ? '['.$_SERVER['SERVER_ADDR'].']' : '[127.0.0.1]';
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
        $this->finalBody    = '';
        $this->headerString = '';
        $this->recipients   = [];
        $this->headers      = [];
        $this->debugMsg     = [];

        $this->setHeader('Date', $this->setDate());

        if ($clear_attachments !== false)
        {
            $this->attachments = [];
        }

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Set RFC 822 Date
     *
     * @return string
     */
    protected function setDate(): string
    {
        $timezone = date('Z');
        $operator = ($timezone[0] === '-') ? '-' : '+';
        $timezone = abs($timezone);
        $timezone = floor($timezone/3600) * 100 + ($timezone % 3600) / 60;

        return sprintf('%s %s%04d', date('D, j M Y H:i:s'), $operator, $timezone);
    }

    //--------------------------------------------------------------------
}
