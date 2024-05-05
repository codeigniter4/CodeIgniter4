<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Email;

use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;
use Config\Mimes;
use ErrorException;

/**
 * CodeIgniter Email Class
 *
 * Permits email to be sent using Mail, Sendmail, or SMTP.
 *
 * @see \CodeIgniter\Email\EmailTest
 */
class Email
{
    /**
     * Properties from the last successful send.
     *
     * @var array|null
     */
    public $archive;

    /**
     * Properties to be added to the next archive.
     *
     * @var array
     */
    protected $tmpArchive = [];

    /**
     * @var string
     */
    public $fromEmail;

    /**
     * @var string
     */
    public $fromName;

    /**
     * Used as the User-Agent and X-Mailer headers' value.
     *
     * @var string
     */
    public $userAgent = 'CodeIgniter';

    /**
     * Path to the Sendmail binary.
     *
     * @var string
     */
    public $mailPath = '/usr/sbin/sendmail';

    /**
     * Which method to use for sending e-mails.
     *
     * @var string 'mail', 'sendmail' or 'smtp'
     */
    public $protocol = 'mail';

    /**
     * STMP Server Hostname
     *
     * @var string
     */
    public $SMTPHost = '';

    /**
     * SMTP Username
     *
     * @var string
     */
    public $SMTPUser = '';

    /**
     * SMTP Password
     *
     * @var string
     */
    public $SMTPPass = '';

    /**
     * SMTP Server port
     *
     * @var int
     */
    public $SMTPPort = 25;

    /**
     * SMTP connection timeout in seconds
     *
     * @var int
     */
    public $SMTPTimeout = 5;

    /**
     * SMTP persistent connection
     *
     * @var bool
     */
    public $SMTPKeepAlive = false;

    /**
     * SMTP Encryption
     *
     * @var string '', 'tls' or 'ssl'. 'tls' will issue a STARTTLS command
     *             to the server. 'ssl' means implicit SSL. Connection on port
     *             465 should set this to ''.
     */
    public $SMTPCrypto = '';

    /**
     * Whether to apply word-wrapping to the message body.
     *
     * @var bool
     */
    public $wordWrap = true;

    /**
     * Number of characters to wrap at.
     *
     * @see Email::$wordWrap
     *
     * @var int
     */
    public $wrapChars = 76;

    /**
     * Message format.
     *
     * @var string 'text' or 'html'
     */
    public $mailType = 'text';

    /**
     * Character set (default: utf-8)
     *
     * @var string
     */
    public $charset = 'UTF-8';

    /**
     * Alternative message (for HTML messages only)
     *
     * @var string
     */
    public $altMessage = '';

    /**
     * Whether to validate e-mail addresses.
     *
     * @var bool
     */
    public $validate = true;

    /**
     * X-Priority header value.
     *
     * @var int 1-5
     */
    public $priority = 3;

    /**
     * Newline character sequence.
     * Use "\r\n" to comply with RFC 822.
     *
     * @see http://www.ietf.org/rfc/rfc822.txt
     *
     * @var string "\r\n" or "\n"
     */
    public $newline = "\r\n";

    /**
     * CRLF character sequence
     *
     * RFC 2045 specifies that for 'quoted-printable' encoding,
     * "\r\n" must be used. However, it appears that some servers
     * (even on the receiving end) don't handle it properly and
     * switching to "\n", while improper, is the only solution
     * that seems to work for all environments.
     *
     * @see http://www.ietf.org/rfc/rfc822.txt
     *
     * @var string
     */
    public $CRLF = "\r\n";

    /**
     * Whether to use Delivery Status Notification.
     *
     * @var bool
     */
    public $DSN = false;

    /**
     * Whether to send multipart alternatives.
     * Yahoo! doesn't seem to like these.
     *
     * @var bool
     */
    public $sendMultipart = true;

    /**
     * Whether to send messages to BCC recipients in batches.
     *
     * @var bool
     */
    public $BCCBatchMode = false;

    /**
     * BCC Batch max number size.
     *
     * @see Email::$BCCBatchMode
     *
     * @var int|string
     */
    public $BCCBatchSize = 200;

    /**
     * Subject header
     *
     * @var string
     */
    protected $subject = '';

    /**
     * Message body
     *
     * @var string
     */
    protected $body = '';

    /**
     * Final message body to be sent.
     *
     * @var string
     */
    protected $finalBody = '';

    /**
     * Final headers to send
     *
     * @var string
     */
    protected $headerStr = '';

    /**
     * SMTP Connection socket placeholder
     *
     * @var resource|null
     */
    protected $SMTPConnect;

    /**
     * Mail encoding
     *
     * @var string '8bit' or '7bit'
     */
    protected $encoding = '8bit';

    /**
     * Whether to perform SMTP authentication
     *
     * @var bool
     */
    protected $SMTPAuth = false;

    /**
     * Whether to send a Reply-To header
     *
     * @var bool
     */
    protected $replyToFlag = false;

    /**
     * Debug messages
     *
     * @see Email::printDebugger()
     *
     * @var array
     */
    protected $debugMessage = [];

    /**
     * Raw debug messages
     *
     * @var list<string>
     */
    private array $debugMessageRaw = [];

    /**
     * Recipients
     *
     * @var array|string
     */
    protected $recipients = [];

    /**
     * CC Recipients
     *
     * @var array
     */
    protected $CCArray = [];

    /**
     * BCC Recipients
     *
     * @var array
     */
    protected $BCCArray = [];

    /**
     * Message headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Attachment data
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Valid $protocol values
     *
     * @see Email::$protocol
     *
     * @var array
     */
    protected $protocols = [
        'mail',
        'sendmail',
        'smtp',
    ];

    /**
     * Character sets valid for 7-bit encoding,
     * excluding language suffix.
     *
     * @var list<string>
     */
    protected $baseCharsets = [
        'us-ascii',
        'iso-2022-',
    ];

    /**
     * Bit depths
     *
     * Valid mail encodings
     *
     * @see Email::$encoding
     *
     * @var array
     */
    protected $bitDepths = [
        '7bit',
        '8bit',
    ];

    /**
     * $priority translations
     *
     * Actual values to send with the X-Priority header
     *
     * @var array
     */
    protected $priorities = [
        1 => '1 (Highest)',
        2 => '2 (High)',
        3 => '3 (Normal)',
        4 => '4 (Low)',
        5 => '5 (Lowest)',
    ];

    /**
     * mbstring.func_overload flag
     *
     * @var bool
     */
    protected static $func_overload;

    /**
     * @param array|\Config\Email|null $config
     */
    public function __construct($config = null)
    {
        $this->initialize($config);
        if (! isset(static::$func_overload)) {
            static::$func_overload = (extension_loaded('mbstring') && ini_get('mbstring.func_overload'));
        }
    }

    /**
     * Initialize preferences
     *
     * @param array|\Config\Email|null $config
     *
     * @return Email
     */
    public function initialize($config)
    {
        $this->clear();

        if ($config instanceof \Config\Email) {
            $config = get_object_vars($config);
        }

        foreach (array_keys(get_class_vars(static::class)) as $key) {
            if (property_exists($this, $key) && isset($config[$key])) {
                $method = 'set' . ucfirst($key);

                if (method_exists($this, $method)) {
                    $this->{$method}($config[$key]);
                } else {
                    $this->{$key} = $config[$key];
                }
            }
        }

        $this->charset  = strtoupper($this->charset);
        $this->SMTPAuth = isset($this->SMTPUser[0], $this->SMTPPass[0]);

        return $this;
    }

    /**
     * @param bool $clearAttachments
     *
     * @return Email
     */
    public function clear($clearAttachments = false)
    {
        $this->subject         = '';
        $this->body            = '';
        $this->finalBody       = '';
        $this->headerStr       = '';
        $this->replyToFlag     = false;
        $this->recipients      = [];
        $this->CCArray         = [];
        $this->BCCArray        = [];
        $this->headers         = [];
        $this->debugMessage    = [];
        $this->debugMessageRaw = [];

        $this->setHeader('Date', $this->setDate());

        if ($clearAttachments !== false) {
            $this->attachments = [];
        }

        return $this;
    }

    /**
     * @param string      $from
     * @param string      $name
     * @param string|null $returnPath Return-Path
     *
     * @return Email
     */
    public function setFrom($from, $name = '', $returnPath = null)
    {
        if (preg_match('/\<(.*)\>/', $from, $match)) {
            $from = $match[1];
        }

        if ($this->validate) {
            $this->validateEmail($this->stringToArray($from));

            if ($returnPath) {
                $this->validateEmail($this->stringToArray($returnPath));
            }
        }

        $this->tmpArchive['fromEmail'] = $from;
        $this->tmpArchive['fromName']  = $name;

        if ($name !== '') {
            // only use Q encoding if there are characters that would require it
            if (! preg_match('/[\200-\377]/', $name)) {
                $name = '"' . addcslashes($name, "\0..\37\177'\"\\") . '"';
            } else {
                $name = $this->prepQEncoding($name);
            }
        }

        $this->setHeader('From', $name . ' <' . $from . '>');
        if (! isset($returnPath)) {
            $returnPath = $from;
        }
        $this->setHeader('Return-Path', '<' . $returnPath . '>');
        $this->tmpArchive['returnPath'] = $returnPath;

        return $this;
    }

    /**
     * @param string $replyto
     * @param string $name
     *
     * @return Email
     */
    public function setReplyTo($replyto, $name = '')
    {
        if (preg_match('/\<(.*)\>/', $replyto, $match)) {
            $replyto = $match[1];
        }

        if ($this->validate) {
            $this->validateEmail($this->stringToArray($replyto));
        }

        if ($name !== '') {
            $this->tmpArchive['replyName'] = $name;

            // only use Q encoding if there are characters that would require it
            if (! preg_match('/[\200-\377]/', $name)) {
                $name = '"' . addcslashes($name, "\0..\37\177'\"\\") . '"';
            } else {
                $name = $this->prepQEncoding($name);
            }
        }

        $this->setHeader('Reply-To', $name . ' <' . $replyto . '>');
        $this->replyToFlag           = true;
        $this->tmpArchive['replyTo'] = $replyto;

        return $this;
    }

    /**
     * @param array|string $to
     *
     * @return Email
     */
    public function setTo($to)
    {
        $to = $this->stringToArray($to);
        $to = $this->cleanEmail($to);

        if ($this->validate) {
            $this->validateEmail($to);
        }

        if ($this->getProtocol() !== 'mail') {
            $this->setHeader('To', implode(', ', $to));
        }

        $this->recipients = $to;

        return $this;
    }

    /**
     * @param string $cc
     *
     * @return Email
     */
    public function setCC($cc)
    {
        $cc = $this->cleanEmail($this->stringToArray($cc));

        if ($this->validate) {
            $this->validateEmail($cc);
        }

        $this->setHeader('Cc', implode(', ', $cc));

        if ($this->getProtocol() === 'smtp') {
            $this->CCArray = $cc;
        }

        $this->tmpArchive['CCArray'] = $cc;

        return $this;
    }

    /**
     * @param string $bcc
     * @param string $limit
     *
     * @return Email
     */
    public function setBCC($bcc, $limit = '')
    {
        if ($limit !== '' && is_numeric($limit)) {
            $this->BCCBatchMode = true;
            $this->BCCBatchSize = $limit;
        }

        $bcc = $this->cleanEmail($this->stringToArray($bcc));

        if ($this->validate) {
            $this->validateEmail($bcc);
        }

        if ($this->getProtocol() === 'smtp' || ($this->BCCBatchMode && count($bcc) > $this->BCCBatchSize)) {
            $this->BCCArray = $bcc;
        } else {
            $this->setHeader('Bcc', implode(', ', $bcc));
            $this->tmpArchive['BCCArray'] = $bcc;
        }

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return Email
     */
    public function setSubject($subject)
    {
        $this->tmpArchive['subject'] = $subject;

        $subject = $this->prepQEncoding($subject);
        $this->setHeader('Subject', $subject);

        return $this;
    }

    /**
     * @param string $body
     *
     * @return Email
     */
    public function setMessage($body)
    {
        $this->body = rtrim(str_replace("\r", '', $body));

        return $this;
    }

    /**
     * @param string      $file        Can be local path, URL or buffered content
     * @param string      $disposition 'attachment'
     * @param string|null $newname
     * @param string      $mime
     *
     * @return bool|Email
     */
    public function attach($file, $disposition = '', $newname = null, $mime = '')
    {
        if ($mime === '') {
            if (! str_contains($file, '://') && ! is_file($file)) {
                $this->setErrorMessage(lang('Email.attachmentMissing', [$file]));

                return false;
            }

            if (! $fp = @fopen($file, 'rb')) {
                $this->setErrorMessage(lang('Email.attachmentUnreadable', [$file]));

                return false;
            }

            $fileContent = stream_get_contents($fp);

            $mime = $this->mimeTypes(pathinfo($file, PATHINFO_EXTENSION));

            fclose($fp);
        } else {
            $fileContent = &$file; // buffered file
        }

        // declare names on their own, to make phpcbf happy
        $namesAttached = [$file, $newname];

        $this->attachments[] = [
            'name'        => $namesAttached,
            'disposition' => empty($disposition) ? 'attachment' : $disposition,
            // Can also be 'inline'  Not sure if it matters
            'type'      => $mime,
            'content'   => chunk_split(base64_encode($fileContent)),
            'multipart' => 'mixed',
        ];

        return $this;
    }

    /**
     * Set and return attachment Content-ID
     * Useful for attached inline pictures
     *
     * @param string $filename
     *
     * @return bool|string
     */
    public function setAttachmentCID($filename)
    {
        foreach ($this->attachments as $i => $attachment) {
            // For file path.
            if ($attachment['name'][0] === $filename) {
                $this->attachments[$i]['multipart'] = 'related';

                $this->attachments[$i]['cid'] = uniqid(basename($attachment['name'][0]) . '@', true);

                return $this->attachments[$i]['cid'];
            }

            // For buffer string.
            if ($attachment['name'][1] === $filename) {
                $this->attachments[$i]['multipart'] = 'related';

                $this->attachments[$i]['cid'] = uniqid(basename($attachment['name'][1]) . '@', true);

                return $this->attachments[$i]['cid'];
            }
        }

        return false;
    }

    /**
     * @param string $header
     * @param string $value
     *
     * @return Email
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = str_replace(["\n", "\r"], '', $value);

        return $this;
    }

    /**
     * @param array|string $email
     *
     * @return array
     */
    protected function stringToArray($email)
    {
        if (! is_array($email)) {
            return (str_contains($email, ',')) ? preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY) : (array) trim($email);
        }

        return $email;
    }

    /**
     * @param string $str
     *
     * @return Email
     */
    public function setAltMessage($str)
    {
        $this->altMessage = (string) $str;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return Email
     */
    public function setMailType($type = 'text')
    {
        $this->mailType = ($type === 'html') ? 'html' : 'text';

        return $this;
    }

    /**
     * @param bool $wordWrap
     *
     * @return Email
     */
    public function setWordWrap($wordWrap = true)
    {
        $this->wordWrap = (bool) $wordWrap;

        return $this;
    }

    /**
     * @param string $protocol
     *
     * @return Email
     */
    public function setProtocol($protocol = 'mail')
    {
        $this->protocol = in_array($protocol, $this->protocols, true) ? strtolower($protocol) : 'mail';

        return $this;
    }

    /**
     * @param int $n
     *
     * @return Email
     */
    public function setPriority($n = 3)
    {
        $this->priority = preg_match('/^[1-5]$/', (string) $n) ? (int) $n : 3;

        return $this;
    }

    /**
     * @param string $newline
     *
     * @return Email
     */
    public function setNewline($newline = "\n")
    {
        $this->newline = in_array($newline, ["\n", "\r\n", "\r"], true) ? $newline : "\n";

        return $this;
    }

    /**
     * @param string $CRLF
     *
     * @return Email
     */
    public function setCRLF($CRLF = "\n")
    {
        $this->CRLF = ($CRLF !== "\n" && $CRLF !== "\r\n" && $CRLF !== "\r") ? "\n" : $CRLF;

        return $this;
    }

    /**
     * @return string
     */
    protected function getMessageID()
    {
        $from = str_replace(['>', '<'], '', $this->headers['Return-Path']);

        return '<' . uniqid('', true) . strstr($from, '@') . '>';
    }

    /**
     * @return string
     */
    protected function getProtocol()
    {
        $this->protocol = strtolower($this->protocol);

        if (! in_array($this->protocol, $this->protocols, true)) {
            $this->protocol = 'mail';
        }

        return $this->protocol;
    }

    /**
     * @return string
     */
    protected function getEncoding()
    {
        if (! in_array($this->encoding, $this->bitDepths, true)) {
            $this->encoding = '8bit';
        }

        foreach ($this->baseCharsets as $charset) {
            if (str_starts_with($this->charset, $charset)) {
                $this->encoding = '7bit';

                break;
            }
        }

        return $this->encoding;
    }

    /**
     * @return string
     */
    protected function getContentType()
    {
        if ($this->mailType === 'html') {
            return empty($this->attachments) ? 'html' : 'html-attach';
        }

        if ($this->mailType === 'text' && ! empty($this->attachments)) {
            return 'plain-attach';
        }

        return 'plain';
    }

    /**
     * Set RFC 822 Date
     *
     * @return string
     */
    protected function setDate()
    {
        $timezone = date('Z');
        $operator = ($timezone[0] === '-') ? '-' : '+';
        $timezone = abs((int) $timezone);
        $timezone = floor($timezone / 3600) * 100 + ($timezone % 3600) / 60;

        return sprintf('%s %s%04d', date('D, j M Y H:i:s'), $operator, $timezone);
    }

    /**
     * @return string
     */
    protected function getMimeMessage()
    {
        return 'This is a multi-part message in MIME format.' . $this->newline . 'Your email application may not support this format.';
    }

    /**
     * @param array|string $email
     *
     * @return bool
     */
    public function validateEmail($email)
    {
        if (! is_array($email)) {
            $this->setErrorMessage(lang('Email.mustBeArray'));

            return false;
        }

        foreach ($email as $val) {
            if (! $this->isValidEmail($val)) {
                $this->setErrorMessage(lang('Email.invalidAddress', [$val]));

                return false;
            }
        }

        return true;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function isValidEmail($email)
    {
        if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && $atpos = strpos($email, '@')) {
            $email = static::substr($email, 0, ++$atpos)
                . idn_to_ascii(static::substr($email, $atpos), 0, INTL_IDNA_VARIANT_UTS46);
        }

        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param array|string $email
     *
     * @return array|string
     */
    public function cleanEmail($email)
    {
        if (! is_array($email)) {
            return preg_match('/\<(.*)\>/', $email, $match) ? $match[1] : $email;
        }

        $cleanEmail = [];

        foreach ($email as $addy) {
            $cleanEmail[] = preg_match('/\<(.*)\>/', $addy, $match) ? $match[1] : $addy;
        }

        return $cleanEmail;
    }

    /**
     * Build alternative plain text message
     *
     * Provides the raw message for use in plain-text headers of
     * HTML-formatted emails.
     *
     * If the user hasn't specified his own alternative message
     * it creates one by stripping the HTML
     *
     * @return string
     */
    protected function getAltMessage()
    {
        if (! empty($this->altMessage)) {
            return ($this->wordWrap) ? $this->wordWrap($this->altMessage, 76) : $this->altMessage;
        }

        $body = preg_match('/\<body.*?\>(.*)\<\/body\>/si', $this->body, $match) ? $match[1] : $this->body;
        $body = str_replace("\t", '', preg_replace('#<!--(.*)--\>#', '', trim(strip_tags($body))));

        for ($i = 20; $i >= 3; $i--) {
            $body = str_replace(str_repeat("\n", $i), "\n\n", $body);
        }

        $body = preg_replace('| +|', ' ', $body);

        return ($this->wordWrap) ? $this->wordWrap($body, 76) : $body;
    }

    /**
     * @param string   $str
     * @param int|null $charlim Line-length limit
     *
     * @return string
     */
    public function wordWrap($str, $charlim = null)
    {
        if (empty($charlim)) {
            $charlim = empty($this->wrapChars) ? 76 : $this->wrapChars;
        }

        if (str_contains($str, "\r")) {
            $str = str_replace(["\r\n", "\r"], "\n", $str);
        }

        $str = preg_replace('| +\n|', "\n", $str);

        $unwrap = [];

        if (preg_match_all('|\{unwrap\}(.+?)\{/unwrap\}|s', $str, $matches)) {
            for ($i = 0, $c = count($matches[0]); $i < $c; $i++) {
                $unwrap[] = $matches[1][$i];
                $str      = str_replace($matches[0][$i], '{{unwrapped' . $i . '}}', $str);
            }
        }

        // Use PHP's native function to do the initial wordwrap.
        // We set the cut flag to FALSE so that any individual words that are
        // too long get left alone. In the next step we'll deal with them.
        $str = wordwrap($str, $charlim, "\n", false);

        // Split the string into individual lines of text and cycle through them
        $output = '';

        foreach (explode("\n", $str) as $line) {
            if (static::strlen($line) <= $charlim) {
                $output .= $line . $this->newline;

                continue;
            }

            $temp = '';

            do {
                if (preg_match('!\[url.+\]|://|www\.!', $line)) {
                    break;
                }

                $temp .= static::substr($line, 0, $charlim - 1);
                $line = static::substr($line, $charlim - 1);
            } while (static::strlen($line) > $charlim);

            if ($temp !== '') {
                $output .= $temp . $this->newline;
            }

            $output .= $line . $this->newline;
        }

        foreach ($unwrap as $key => $val) {
            $output = str_replace('{{unwrapped' . $key . '}}', $val, $output);
        }

        return $output;
    }

    /**
     * Build final headers
     *
     * @return void
     */
    protected function buildHeaders()
    {
        $this->setHeader('User-Agent', $this->userAgent);
        $this->setHeader('X-Sender', $this->cleanEmail($this->headers['From']));
        $this->setHeader('X-Mailer', $this->userAgent);
        $this->setHeader('X-Priority', $this->priorities[$this->priority]);
        $this->setHeader('Message-ID', $this->getMessageID());
        $this->setHeader('Mime-Version', '1.0');
    }

    /**
     * Write Headers as a string
     *
     * @return void
     */
    protected function writeHeaders()
    {
        if ($this->protocol === 'mail' && isset($this->headers['Subject'])) {
            $this->subject = $this->headers['Subject'];
            unset($this->headers['Subject']);
        }

        reset($this->headers);
        $this->headerStr = '';

        foreach ($this->headers as $key => $val) {
            $val = trim($val);

            if ($val !== '') {
                $this->headerStr .= $key . ': ' . $val . $this->newline;
            }
        }

        if ($this->getProtocol() === 'mail') {
            $this->headerStr = rtrim($this->headerStr);
        }
    }

    /**
     * Build Final Body and attachments
     *
     * @return void
     */
    protected function buildMessage()
    {
        if ($this->wordWrap === true && $this->mailType !== 'html') {
            $this->body = $this->wordWrap($this->body);
        }

        $this->writeHeaders();
        $hdr  = ($this->getProtocol() === 'mail') ? $this->newline : '';
        $body = '';

        switch ($this->getContentType()) {
            case 'plain':
                $hdr .= 'Content-Type: text/plain; charset='
                    . $this->charset
                    . $this->newline
                    . 'Content-Transfer-Encoding: '
                    . $this->getEncoding();

                if ($this->getProtocol() === 'mail') {
                    $this->headerStr .= $hdr;
                    $this->finalBody = $this->body;
                } else {
                    $this->finalBody = $hdr . $this->newline . $this->newline . $this->body;
                }

                return;

            case 'html':
                $boundary = uniqid('B_ALT_', true);

                if ($this->sendMultipart === false) {
                    $hdr .= 'Content-Type: text/html; charset='
                        . $this->charset . $this->newline
                        . 'Content-Transfer-Encoding: quoted-printable';
                } else {
                    $hdr  .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
                    $body .= $this->getMimeMessage() . $this->newline . $this->newline
                        . '--' . $boundary . $this->newline
                        . 'Content-Type: text/plain; charset=' . $this->charset . $this->newline
                        . 'Content-Transfer-Encoding: ' . $this->getEncoding() . $this->newline . $this->newline
                        . $this->getAltMessage() . $this->newline . $this->newline
                        . '--' . $boundary . $this->newline
                        . 'Content-Type: text/html; charset=' . $this->charset . $this->newline
                        . 'Content-Transfer-Encoding: quoted-printable' . $this->newline . $this->newline;
                }

                $this->finalBody = $body . $this->prepQuotedPrintable($this->body) . $this->newline . $this->newline;

                if ($this->getProtocol() === 'mail') {
                    $this->headerStr .= $hdr;
                } else {
                    $this->finalBody = $hdr . $this->newline . $this->newline . $this->finalBody;
                }

                if ($this->sendMultipart !== false) {
                    $this->finalBody .= '--' . $boundary . '--';
                }

                return;

            case 'plain-attach':
                $boundary = uniqid('B_ATC_', true);
                $hdr .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

                if ($this->getProtocol() === 'mail') {
                    $this->headerStr .= $hdr;
                }

                $body .= $this->getMimeMessage() . $this->newline
                    . $this->newline
                    . '--' . $boundary . $this->newline
                    . 'Content-Type: text/plain; charset=' . $this->charset . $this->newline
                    . 'Content-Transfer-Encoding: ' . $this->getEncoding() . $this->newline
                    . $this->newline
                    . $this->body . $this->newline . $this->newline;

                $this->appendAttachments($body, $boundary);
                break;

            case 'html-attach':
                $altBoundary  = uniqid('B_ALT_', true);
                $lastBoundary = null;

                if ($this->attachmentsHaveMultipart('mixed')) {
                    $atcBoundary = uniqid('B_ATC_', true);
                    $hdr .= 'Content-Type: multipart/mixed; boundary="' . $atcBoundary . '"';
                    $lastBoundary = $atcBoundary;
                }

                if ($this->attachmentsHaveMultipart('related')) {
                    $relBoundary = uniqid('B_REL_', true);

                    $relBoundaryHeader = 'Content-Type: multipart/related; boundary="' . $relBoundary . '"';

                    if (isset($lastBoundary)) {
                        $body .= '--' . $lastBoundary . $this->newline . $relBoundaryHeader;
                    } else {
                        $hdr .= $relBoundaryHeader;
                    }

                    $lastBoundary = $relBoundary;
                }

                if ($this->getProtocol() === 'mail') {
                    $this->headerStr .= $hdr;
                }

                static::strlen($body) && $body .= $this->newline . $this->newline;

                $body .= $this->getMimeMessage() . $this->newline . $this->newline
                    . '--' . $lastBoundary . $this->newline
                    . 'Content-Type: multipart/alternative; boundary="' . $altBoundary . '"' . $this->newline . $this->newline
                    . '--' . $altBoundary . $this->newline
                    . 'Content-Type: text/plain; charset=' . $this->charset . $this->newline
                    . 'Content-Transfer-Encoding: ' . $this->getEncoding() . $this->newline . $this->newline
                    . $this->getAltMessage() . $this->newline . $this->newline
                    . '--' . $altBoundary . $this->newline
                    . 'Content-Type: text/html; charset=' . $this->charset . $this->newline
                    . 'Content-Transfer-Encoding: quoted-printable' . $this->newline . $this->newline
                    . $this->prepQuotedPrintable($this->body) . $this->newline . $this->newline
                    . '--' . $altBoundary . '--' . $this->newline . $this->newline;

                if (isset($relBoundary)) {
                    $body .= $this->newline . $this->newline;
                    $this->appendAttachments($body, $relBoundary, 'related');
                }

                // multipart/mixed attachments
                if (isset($atcBoundary)) {
                    $body .= $this->newline . $this->newline;
                    $this->appendAttachments($body, $atcBoundary, 'mixed');
                }

                break;
        }

        $this->finalBody = ($this->getProtocol() === 'mail') ? $body : $hdr . $this->newline . $this->newline . $body;
    }

    /**
     * @param mixed $type
     *
     * @return bool
     */
    protected function attachmentsHaveMultipart($type)
    {
        foreach ($this->attachments as &$attachment) {
            if ($attachment['multipart'] === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string      $body      Message body to append to
     * @param string      $boundary  Multipart boundary
     * @param string|null $multipart When provided, only attachments of this type will be processed
     *
     * @return void
     */
    protected function appendAttachments(&$body, $boundary, $multipart = null)
    {
        foreach ($this->attachments as $attachment) {
            if (isset($multipart) && $attachment['multipart'] !== $multipart) {
                continue;
            }

            $name = $attachment['name'][1] ?? basename($attachment['name'][0]);
            $body .= '--' . $boundary . $this->newline
                . 'Content-Type: ' . $attachment['type'] . '; name="' . $name . '"' . $this->newline
                . 'Content-Disposition: ' . $attachment['disposition'] . ';' . $this->newline
                . 'Content-Transfer-Encoding: base64' . $this->newline
                . (empty($attachment['cid']) ? '' : 'Content-ID: <' . $attachment['cid'] . '>' . $this->newline)
                . $this->newline
                . $attachment['content'] . $this->newline;
        }

        // $name won't be set if no attachments were appended,
        // and therefore a boundary wouldn't be necessary
        if (! empty($name)) {
            $body .= '--' . $boundary . '--';
        }
    }

    /**
     * Prepares string for Quoted-Printable Content-Transfer-Encoding
     * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
     *
     * @param string $str
     *
     * @return string
     */
    protected function prepQuotedPrintable($str)
    {
        // ASCII code numbers for "safe" characters that can always be
        // used literally, without encoding, as described in RFC 2049.
        // http://www.ietf.org/rfc/rfc2049.txt
        static $asciiSafeChars = [
            // ' (  )   +   ,   -   .   /   :   =   ?
            39,
            40,
            41,
            43,
            44,
            45,
            46,
            47,
            58,
            61,
            63,
            // numbers
            48,
            49,
            50,
            51,
            52,
            53,
            54,
            55,
            56,
            57,
            // upper-case letters
            65,
            66,
            67,
            68,
            69,
            70,
            71,
            72,
            73,
            74,
            75,
            76,
            77,
            78,
            79,
            80,
            81,
            82,
            83,
            84,
            85,
            86,
            87,
            88,
            89,
            90,
            // lower-case letters
            97,
            98,
            99,
            100,
            101,
            102,
            103,
            104,
            105,
            106,
            107,
            108,
            109,
            110,
            111,
            112,
            113,
            114,
            115,
            116,
            117,
            118,
            119,
            120,
            121,
            122,
        ];

        // We are intentionally wrapping so mail servers will encode characters
        // properly and MUAs will behave, so {unwrap} must go!
        $str = str_replace(['{unwrap}', '{/unwrap}'], '', $str);

        // RFC 2045 specifies CRLF as "\r\n".
        // However, many developers choose to override that and violate
        // the RFC rules due to (apparently) a bug in MS Exchange,
        // which only works with "\n".
        if ($this->CRLF === "\r\n") {
            return quoted_printable_encode($str);
        }

        // Reduce multiple spaces & remove nulls
        $str = preg_replace(['| +|', '/\x00+/'], [' ', ''], $str);

        // Standardize newlines
        if (str_contains($str, "\r")) {
            $str = str_replace(["\r\n", "\r"], "\n", $str);
        }

        $escape = '=';
        $output = '';

        foreach (explode("\n", $str) as $line) {
            $length = static::strlen($line);
            $temp   = '';

            // Loop through each character in the line to add soft-wrap
            // characters at the end of a line " =\r\n" and add the newly
            // processed line(s) to the output (see comment on $crlf class property)
            for ($i = 0; $i < $length; $i++) {
                // Grab the next character
                $char  = $line[$i];
                $ascii = ord($char);

                // Convert spaces and tabs but only if it's the end of the line
                if ($ascii === 32 || $ascii === 9) {
                    if ($i === ($length - 1)) {
                        $char = $escape . sprintf('%02s', dechex($ascii));
                    }
                }
                // DO NOT move this below the $ascii_safe_chars line!
                //
                // = (equals) signs are allowed by RFC2049, but must be encoded
                // as they are the encoding delimiter!
                elseif ($ascii === 61) {
                    $char = $escape . strtoupper(sprintf('%02s', dechex($ascii)));  // =3D
                } elseif (! in_array($ascii, $asciiSafeChars, true)) {
                    $char = $escape . strtoupper(sprintf('%02s', dechex($ascii)));
                }

                // If we're at the character limit, add the line to the output,
                // reset our temp variable, and keep on chuggin'
                if ((static::strlen($temp) + static::strlen($char)) >= 76) {
                    $output .= $temp . $escape . $this->CRLF;
                    $temp = '';
                }

                // Add the character to our temporary line
                $temp .= $char;
            }

            // Add our completed line to the output
            $output .= $temp . $this->CRLF;
        }

        // get rid of extra CRLF tacked onto the end
        return static::substr($output, 0, static::strlen($this->CRLF) * -1);
    }

    /**
     * Performs "Q Encoding" on a string for use in email headers.
     * It's related but not identical to quoted-printable, so it has its
     * own method.
     *
     * @param string $str
     *
     * @return string
     */
    protected function prepQEncoding($str)
    {
        $str = str_replace(["\r", "\n"], '', $str);

        if ($this->charset === 'UTF-8') {
            // Note: We used to have mb_encode_mimeheader() as the first choice
            // here, but it turned out to be buggy and unreliable. DO NOT
            // re-add it! -- Narf
            if (extension_loaded('iconv')) {
                $output = @iconv_mime_encode('', $str, [
                    'scheme'           => 'Q',
                    'line-length'      => 76,
                    'input-charset'    => $this->charset,
                    'output-charset'   => $this->charset,
                    'line-break-chars' => $this->CRLF,
                ]);

                // There are reports that iconv_mime_encode() might fail and return FALSE
                if ($output !== false) {
                    // iconv_mime_encode() will always put a header field name.
                    // We've passed it an empty one, but it still prepends our
                    // encoded string with ': ', so we need to strip it.
                    return static::substr($output, 2);
                }

                $chars = iconv_strlen($str, 'UTF-8');
            } elseif (extension_loaded('mbstring')) {
                $chars = mb_strlen($str, 'UTF-8');
            }
        }

        // We might already have this set for UTF-8
        if (! isset($chars)) {
            $chars = static::strlen($str);
        }

        $output = '=?' . $this->charset . '?Q?';

        for ($i = 0, $length = static::strlen($output); $i < $chars; $i++) {
            $chr = ($this->charset === 'UTF-8' && extension_loaded('iconv')) ? '=' . implode('=', str_split(strtoupper(bin2hex(iconv_substr($str, $i, 1, $this->charset))), 2)) : '=' . strtoupper(bin2hex($str[$i]));

            // RFC 2045 sets a limit of 76 characters per line.
            // We'll append ?= to the end of each line though.
            if ($length + ($l = static::strlen($chr)) > 74) {
                $output .= '?=' . $this->CRLF // EOL
                    . ' =?' . $this->charset . '?Q?' . $chr; // New line

                $length = 6 + static::strlen($this->charset) + $l; // Reset the length for the new line
            } else {
                $output .= $chr;
                $length += $l;
            }
        }

        // End the header
        return $output . '?=';
    }

    /**
     * @param bool $autoClear
     *
     * @return bool
     */
    public function send($autoClear = true)
    {
        if (! isset($this->headers['From']) && ! empty($this->fromEmail)) {
            $this->setFrom($this->fromEmail, $this->fromName);
        }

        if (! isset($this->headers['From'])) {
            $this->setErrorMessage(lang('Email.noFrom'));

            return false;
        }

        if ($this->replyToFlag === false) {
            $this->setReplyTo($this->headers['From']);
        }

        if (
            empty($this->recipients) && ! isset($this->headers['To'])
            && empty($this->BCCArray) && ! isset($this->headers['Bcc'])
            && ! isset($this->headers['Cc'])
        ) {
            $this->setErrorMessage(lang('Email.noRecipients'));

            return false;
        }

        $this->buildHeaders();

        if ($this->BCCBatchMode && count($this->BCCArray) > $this->BCCBatchSize) {
            $this->batchBCCSend();

            if ($autoClear) {
                $this->clear();
            }

            return true;
        }

        $this->buildMessage();
        $result = $this->spoolEmail();

        if ($result) {
            $this->setArchiveValues();

            if ($autoClear) {
                $this->clear();
            }

            Events::trigger('email', $this->archive);
        }

        return $result;
    }

    /**
     * Batch Bcc Send. Sends groups of BCCs in batches
     *
     * @return void
     */
    public function batchBCCSend()
    {
        $float = $this->BCCBatchSize - 1;
        $set   = '';
        $chunk = [];

        for ($i = 0, $c = count($this->BCCArray); $i < $c; $i++) {
            if (isset($this->BCCArray[$i])) {
                $set .= ', ' . $this->BCCArray[$i];
            }

            if ($i === $float) {
                $chunk[] = static::substr($set, 1);
                $float += $this->BCCBatchSize;
                $set = '';
            }

            if ($i === $c - 1) {
                $chunk[] = static::substr($set, 1);
            }
        }

        for ($i = 0, $c = count($chunk); $i < $c; $i++) {
            unset($this->headers['Bcc']);
            $bcc = $this->cleanEmail($this->stringToArray($chunk[$i]));

            if ($this->protocol !== 'smtp') {
                $this->setHeader('Bcc', implode(', ', $bcc));
            } else {
                $this->BCCArray = $bcc;
            }

            $this->buildMessage();
            $this->spoolEmail();
        }

        // Update the archive
        $this->setArchiveValues();
        Events::trigger('email', $this->archive);
    }

    /**
     * Unwrap special elements
     *
     * @return void
     */
    protected function unwrapSpecials()
    {
        $this->finalBody = preg_replace_callback(
            '/\{unwrap\}(.*?)\{\/unwrap\}/si',
            $this->removeNLCallback(...),
            $this->finalBody
        );
    }

    /**
     * Strip line-breaks via callback
     *
     * @used-by unwrapSpecials()
     *
     * @param list<string> $matches
     *
     * @return string
     */
    protected function removeNLCallback($matches)
    {
        if (str_contains($matches[1], "\r") || str_contains($matches[1], "\n")) {
            $matches[1] = str_replace(["\r\n", "\r", "\n"], '', $matches[1]);
        }

        return $matches[1];
    }

    /**
     * Spool mail to the mail server
     *
     * @return bool
     */
    protected function spoolEmail()
    {
        $this->unwrapSpecials();
        $protocol = $this->getProtocol();
        $method   = 'sendWith' . ucfirst($protocol);

        try {
            $success = $this->{$method}();
        } catch (ErrorException $e) {
            $success = false;
            log_message('error', 'Email: ' . $method . ' throwed ' . $e);
        }

        if (! $success) {
            $message = lang('Email.sendFailure' . ($protocol === 'mail' ? 'PHPMail' : ucfirst($protocol)));

            log_message('error', 'Email: ' . $message);
            log_message('error', $this->printDebuggerRaw());

            $this->setErrorMessage($message);

            return false;
        }

        $this->setErrorMessage(lang('Email.sent', [$protocol]));

        return true;
    }

    /**
     * Validate email for shell
     *
     * Applies stricter, shell-safe validation to email addresses.
     * Introduced to prevent RCE via sendmail's -f option.
     *
     * @see     https://github.com/codeigniter4/CodeIgniter/issues/4963
     * @see     https://gist.github.com/Zenexer/40d02da5e07f151adeaeeaa11af9ab36
     *
     * @license https://creativecommons.org/publicdomain/zero/1.0/    CC0 1.0, Public Domain
     *
     * Credits for the base concept go to Paul Buonopane <paul@namepros.com>
     *
     * @param string $email
     *
     * @return bool
     */
    protected function validateEmailForShell(&$email)
    {
        if (function_exists('idn_to_ascii') && $atpos = strpos($email, '@')) {
            $email = static::substr($email, 0, ++$atpos)
                . idn_to_ascii(static::substr($email, $atpos), 0, INTL_IDNA_VARIANT_UTS46);
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) === $email && preg_match('#\A[a-z0-9._+-]+@[a-z0-9.-]{1,253}\z#i', $email);
    }

    /**
     * Send using mail()
     *
     * @return bool
     */
    protected function sendWithMail()
    {
        $recipients = is_array($this->recipients) ? implode(', ', $this->recipients) : $this->recipients;

        // _validate_email_for_shell() below accepts by reference,
        // so this needs to be assigned to a variable
        $from = $this->cleanEmail($this->headers['Return-Path']);

        if (! $this->validateEmailForShell($from)) {
            return mail($recipients, $this->subject, $this->finalBody, $this->headerStr);
        }

        // most documentation of sendmail using the "-f" flag lacks a space after it, however
        // we've encountered servers that seem to require it to be in place.
        return mail($recipients, $this->subject, $this->finalBody, $this->headerStr, '-f ' . $from);
    }

    /**
     * Send using Sendmail
     *
     * @return bool
     */
    protected function sendWithSendmail()
    {
        // _validate_email_for_shell() below accepts by reference,
        // so this needs to be assigned to a variable
        $from = $this->cleanEmail($this->headers['From']);

        $from = $this->validateEmailForShell($from) ? '-f ' . $from : '';

        if (! function_usable('popen') || false === ($fp = @popen($this->mailPath . ' -oi ' . $from . ' -t', 'w'))) {
            return false;
        }

        fwrite($fp, $this->headerStr);
        fwrite($fp, $this->finalBody);
        $status = pclose($fp);

        if ($status !== 0) {
            $this->setErrorMessage(lang('Email.exitStatus', [$status]));
            $this->setErrorMessage(lang('Email.noSocket'));

            return false;
        }

        return true;
    }

    /**
     * Send using SMTP
     *
     * @return bool
     */
    protected function sendWithSmtp()
    {
        if ($this->SMTPHost === '') {
            $this->setErrorMessage(lang('Email.noHostname'));

            return false;
        }

        if (! $this->SMTPConnect() || ! $this->SMTPAuthenticate()) {
            return false;
        }

        if (! $this->sendCommand('from', $this->cleanEmail($this->headers['From']))) {
            $this->SMTPEnd();

            return false;
        }

        foreach ($this->recipients as $val) {
            if (! $this->sendCommand('to', $val)) {
                $this->SMTPEnd();

                return false;
            }
        }

        foreach ($this->CCArray as $val) {
            if ($val !== '' && ! $this->sendCommand('to', $val)) {
                $this->SMTPEnd();

                return false;
            }
        }

        foreach ($this->BCCArray as $val) {
            if ($val !== '' && ! $this->sendCommand('to', $val)) {
                $this->SMTPEnd();

                return false;
            }
        }

        if (! $this->sendCommand('data')) {
            $this->SMTPEnd();

            return false;
        }

        // perform dot transformation on any lines that begin with a dot
        $this->sendData($this->headerStr . preg_replace('/^\./m', '..$1', $this->finalBody));
        $this->sendData($this->newline . '.');
        $reply = $this->getSMTPData();
        $this->setErrorMessage($reply);
        $this->SMTPEnd();

        if (! str_starts_with($reply, '250')) {
            $this->setErrorMessage(lang('Email.SMTPError', [$reply]));

            return false;
        }

        return true;
    }

    /**
     * Shortcut to send RSET or QUIT depending on keep-alive
     *
     * @return void
     */
    protected function SMTPEnd()
    {
        $this->sendCommand($this->SMTPKeepAlive ? 'reset' : 'quit');
    }

    /**
     * @return bool|string
     */
    protected function SMTPConnect()
    {
        if (is_resource($this->SMTPConnect)) {
            return true;
        }

        $ssl = '';

        // Connection to port 465 should use implicit TLS (without STARTTLS)
        // as per RFC 8314.
        if ($this->SMTPPort === 465) {
            $ssl = 'tls://';
        }
        // But if $SMTPCrypto is set to `ssl`, SSL can be used.
        if ($this->SMTPCrypto === 'ssl') {
            $ssl = 'ssl://';
        }

        $this->SMTPConnect = fsockopen(
            $ssl . $this->SMTPHost,
            $this->SMTPPort,
            $errno,
            $errstr,
            $this->SMTPTimeout
        );

        if (! is_resource($this->SMTPConnect)) {
            $this->setErrorMessage(lang('Email.SMTPError', [$errno . ' ' . $errstr]));

            return false;
        }

        stream_set_timeout($this->SMTPConnect, $this->SMTPTimeout);
        $this->setErrorMessage($this->getSMTPData());

        if ($this->SMTPCrypto === 'tls') {
            $this->sendCommand('hello');
            $this->sendCommand('starttls');
            $crypto = stream_socket_enable_crypto(
                $this->SMTPConnect,
                true,
                STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT
                | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT
                | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
            );

            if ($crypto !== true) {
                $this->setErrorMessage(lang('Email.SMTPError', [$this->getSMTPData()]));

                return false;
            }
        }

        return $this->sendCommand('hello');
    }

    /**
     * @param string $cmd
     * @param string $data
     *
     * @return bool
     */
    protected function sendCommand($cmd, $data = '')
    {
        switch ($cmd) {
            case 'hello':
                if ($this->SMTPAuth || $this->getEncoding() === '8bit') {
                    $this->sendData('EHLO ' . $this->getHostname());
                } else {
                    $this->sendData('HELO ' . $this->getHostname());
                }

                $resp = 250;
                break;

            case 'starttls':
                $this->sendData('STARTTLS');
                $resp = 220;
                break;

            case 'from':
                $this->sendData('MAIL FROM:<' . $data . '>');
                $resp = 250;
                break;

            case 'to':
                if ($this->DSN) {
                    $this->sendData('RCPT TO:<' . $data . '> NOTIFY=SUCCESS,DELAY,FAILURE ORCPT=rfc822;' . $data);
                } else {
                    $this->sendData('RCPT TO:<' . $data . '>');
                }
                $resp = 250;
                break;

            case 'data':
                $this->sendData('DATA');
                $resp = 354;
                break;

            case 'reset':
                $this->sendData('RSET');
                $resp = 250;
                break;

            case 'quit':
                $this->sendData('QUIT');
                $resp = 221;
                break;

            default:
                $resp = null;
        }

        $reply = $this->getSMTPData();

        $this->debugMessage[]    = '<pre>' . $cmd . ': ' . $reply . '</pre>';
        $this->debugMessageRaw[] = $cmd . ': ' . $reply;

        if ($resp === null || ((int) static::substr($reply, 0, 3) !== $resp)) {
            $this->setErrorMessage(lang('Email.SMTPError', [$reply]));

            return false;
        }

        if ($cmd === 'quit') {
            fclose($this->SMTPConnect);
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function SMTPAuthenticate()
    {
        if (! $this->SMTPAuth) {
            return true;
        }

        if ($this->SMTPUser === '' && $this->SMTPPass === '') {
            $this->setErrorMessage(lang('Email.noSMTPAuth'));

            return false;
        }

        $this->sendData('AUTH LOGIN');
        $reply = $this->getSMTPData();

        if (str_starts_with($reply, '503')) {    // Already authenticated
            return true;
        }

        if (! str_starts_with($reply, '334')) {
            $this->setErrorMessage(lang('Email.failedSMTPLogin', [$reply]));

            return false;
        }

        $this->sendData(base64_encode($this->SMTPUser));
        $reply = $this->getSMTPData();

        if (! str_starts_with($reply, '334')) {
            $this->setErrorMessage(lang('Email.SMTPAuthUsername', [$reply]));

            return false;
        }

        $this->sendData(base64_encode($this->SMTPPass));
        $reply = $this->getSMTPData();

        if (! str_starts_with($reply, '235')) {
            $this->setErrorMessage(lang('Email.SMTPAuthPassword', [$reply]));

            return false;
        }

        if ($this->SMTPKeepAlive) {
            $this->SMTPAuth = false;
        }

        return true;
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    protected function sendData($data)
    {
        $data .= $this->newline;

        $result = null;

        for ($written = $timestamp = 0, $length = static::strlen($data); $written < $length; $written += $result) {
            if (($result = fwrite($this->SMTPConnect, static::substr($data, $written))) === false) {
                break;
            }

            // See https://bugs.php.net/bug.php?id=39598 and http://php.net/manual/en/function.fwrite.php#96951
            if ($result === 0) {
                if ($timestamp === 0) {
                    $timestamp = Time::now()->getTimestamp();
                } elseif ($timestamp < (Time::now()->getTimestamp() - $this->SMTPTimeout)) {
                    $result = false;

                    break;
                }

                usleep(250000);

                continue;
            }

            $timestamp = 0;
        }

        if (! is_int($result)) {
            $this->setErrorMessage(lang('Email.SMTPDataFailure', [$data]));

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getSMTPData()
    {
        $data = '';

        while ($str = fgets($this->SMTPConnect, 512)) {
            $data .= $str;

            if ($str[3] === ' ') {
                break;
            }
        }

        return $data;
    }

    /**
     * There are only two legal types of hostname - either a fully
     * qualified domain name (eg: "mail.example.com") or an IP literal
     * (eg: "[1.2.3.4]").
     *
     * @see https://tools.ietf.org/html/rfc5321#section-2.3.5
     * @see http://cbl.abuseat.org/namingproblems.html
     *
     * @return string
     */
    protected function getHostname()
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        if (isset($_SERVER['SERVER_ADDR'])) {
            return '[' . $_SERVER['SERVER_ADDR'] . ']';
        }

        $hostname = gethostname();
        if ($hostname !== false) {
            return $hostname;
        }

        return '[127.0.0.1]';
    }

    /**
     * @param array|string $include List of raw data chunks to include in the output
     *                              Valid options are: 'headers', 'subject', 'body'
     *
     * @return string
     */
    public function printDebugger($include = ['headers', 'subject', 'body'])
    {
        $msg = implode('', $this->debugMessage);

        // Determine which parts of our raw data needs to be printed
        $rawData = '';

        if (! is_array($include)) {
            $include = [$include];
        }

        if (in_array('headers', $include, true)) {
            $rawData = htmlspecialchars($this->headerStr) . "\n";
        }
        if (in_array('subject', $include, true)) {
            $rawData .= htmlspecialchars($this->subject) . "\n";
        }
        if (in_array('body', $include, true)) {
            $rawData .= htmlspecialchars($this->finalBody);
        }

        return $msg . ($rawData === '' ? '' : '<pre>' . $rawData . '</pre>');
    }

    /**
     * Returns raw debug messages
     */
    private function printDebuggerRaw(): string
    {
        return implode("\n", $this->debugMessageRaw);
    }

    /**
     * @param string $msg
     *
     * @return void
     */
    protected function setErrorMessage($msg)
    {
        $this->debugMessage[]    = $msg . '<br>';
        $this->debugMessageRaw[] = $msg;
    }

    /**
     * Mime Types
     *
     * @param string $ext
     *
     * @return string
     */
    protected function mimeTypes($ext = '')
    {
        $mime = Mimes::guessTypeFromExtension(strtolower($ext));

        return ! empty($mime) ? $mime : 'application/x-unknown-content-type';
    }

    public function __destruct()
    {
        if (is_resource($this->SMTPConnect)) {
            try {
                $this->sendCommand('quit');
            } catch (ErrorException $e) {
                $protocol = $this->getProtocol();
                $method   = 'sendWith' . ucfirst($protocol);
                log_message('error', 'Email: ' . $method . ' throwed ' . $e);
            }
        }
    }

    /**
     * Byte-safe strlen()
     *
     * @param string $str
     *
     * @return int
     */
    protected static function strlen($str)
    {
        return (static::$func_overload) ? mb_strlen($str, '8bit') : strlen($str);
    }

    /**
     * Byte-safe substr()
     *
     * @param string   $str
     * @param int      $start
     * @param int|null $length
     *
     * @return string
     */
    protected static function substr($str, $start, $length = null)
    {
        if (static::$func_overload) {
            return mb_substr($str, $start, $length, '8bit');
        }

        return isset($length) ? substr($str, $start, $length) : substr($str, $start);
    }

    /**
     * Determines the values that should be stored in $archive.
     *
     * @return array The updated archive values
     */
    protected function setArchiveValues(): array
    {
        // Get property values and add anything prepped in tmpArchive
        $this->archive = array_merge(get_object_vars($this), $this->tmpArchive);
        unset($this->archive['archive']);

        // Clear tmpArchive for next run
        $this->tmpArchive = [];

        return $this->archive;
    }
}
