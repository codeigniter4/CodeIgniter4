<?php namespace CodeIgniter\Mail;

abstract class BaseHandler implements MailHandlerInterface
{
    /**
     * The message instance.
     *
     * @var MessageInterface
     */
    protected $message;

    /**
     * Used as the User-Agent and X-Mailer headers' value.
     *
     * @var    string
     */
    public $useragent = 'CodeIgniter';

    /**
     * Message format.
     *
     * @var    string    'text' or 'html'
     */
    public $mailtype = 'text';

    /**
     * Character set (default: utf-8)
     *
     * @var    string
     */
    public $charset = 'utf-8';

    /**
     * Whether to validate e-mail addresses.
     *
     * @var    bool
     */
    public $validate = true;

    /**
     * X-Priority header value.
     *
     * @var    int    1-5
     */
    public $priority = 3;            // Default priority (1 - 5)

    /**
     * Newline character sequence.
     * Use "\r\n" to comply with RFC 822.
     *
     * @link    http://www.ietf.org/rfc/rfc822.txt
     * @var    string    "\r\n" or "\n"
     */
    public $newline = "\n";            // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)

    /**
     * CRLF character sequence
     *
     * RFC 2045 specifies that for 'quoted-printable' encoding,
     * "\r\n" must be used. However, it appears that some servers
     * (even on the receiving end) don't handle it properly and
     * switching to "\n", while improper, is the only solution
     * that seems to work for all environments.
     *
     * @link    http://www.ietf.org/rfc/rfc822.txt
     * @var    string
     */
    public $crlf = "\n";

    /**
     * Whether to use Delivery Status Notification.
     *
     * @var    bool
     */
    public $DSN = false;

    /**
     * Whether to send multipart alternatives.
     * Yahoo! doesn't seem to like these.
     *
     * @var    bool
     */
    public $sendMultipart = true;

    /**
     * Whether to send messages to BCC recipients in batches.
     *
     * @var    bool
     */
    public $BCCBatchMode = false;

    /**
     * BCC Batch max number size.
     *
     * @see    CI_Email::$bcc_batch_mode
     * @var    int
     */
    public $BCCBatchSize = 200;

    //--------------------------------------------------------------------

    /**
     * Whether to perform SMTP authentication
     *
     * @var    bool
     */
    protected $SMTPAuth = false;

    /**
     * Whether to send a Reply-To header
     *
     * @var    bool
     */
    protected $ReplyToFlag = false;

    /**
     * Debug messages
     *
     * @see    CI_Email::print_debugger()
     * @var    string
     */
    protected $debugMsg = [];

    /**
     * Recipients
     *
     * @var    string[]
     */
    protected $recipients = [];

    /**
     * CC Recipients
     *
     * @var    string[]
     */
    protected $CC = [];

    /**
     * BCC Recipients
     *
     * @var    string[]
     */
    protected $BCC = [];

    /**
     * Message headers
     *
     * @var    string[]
     */
    protected $headers = [];

    /**
     * Attachment data
     *
     * @var    array
     */
    protected $attachments = [];

    /**
     * The original config array passed in.
     * In case child classes need it.
     *
     * @var array
     */
    protected $config;

    //--------------------------------------------------------------------

    public function __construct(array $config=[])
    {
        $this->reset();

        foreach ($config as $key => $value)
        {
            if (isset($this->$key))
            {
                $this->$key = $value;
            }
        }

        $this->charset = strtoupper($this->charset);
        $this->config  = $config;
    }

    /**
     * Sets the Mail Message class that represents the message details.
     *
     * @param \CodeIgniter\Mail\BaseMessage $message
     *
     * @return mixed
     */
    public function setMessage(BaseMessage $message)
    {
        $this->message = $message;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the current message instance.
     *
     * @return \CodeIgniter\Mail\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    //--------------------------------------------------------------------

    /**
     * Does the actual delivery of a message.
     *
     * @param \CodeIgniter\Mail\MessageInterface $message
     * @param bool                               $clear_after If TRUE, will reset the class after sending.
     *
     * @return mixed
     */
    public abstract function send(MessageInterface $message, bool $clear_after = true);

    //--------------------------------------------------------------------

    /**
     * Sets a header value for the email. Not every service will provide this.
     *
     * @param $field
     * @param $value
     *
     * @return mixed
     */
    public function setHeader(string $field, $value)
    {
        $this->headers[$field] = $value;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Returns all headers that have been set.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Utility Methods
    //--------------------------------------------------------------------

    /**
     * Get Debug Message
     *
     * @param	array	$include	List of raw data chunks to include in the output
     *					Valid options are: 'headers', 'subject', 'body'
     * @return	string
     */
    public function getDebugger($include = ['headers', 'subject', 'body'])
    {
        $msg = '';

        if (count($this->debugMsg) > 0)
        {
            foreach ($this->debugMsg as $val)
            {
                $msg .= $val;
            }
        }

        // Determine which parts of our raw data needs to be printed
        $raw_data = '';
        is_array($include) OR $include = array($include);

        if (in_array('headers', $include, TRUE))
        {
            $raw_data = htmlspecialchars($this->_header_str)."\n";
        }

        if (in_array('subject', $include, TRUE))
        {
            $raw_data .= htmlspecialchars($this->subject)."\n";
        }

        if (in_array('body', $include, TRUE))
        {
            $raw_data .= htmlspecialchars($this->finalbody);
        }

        return $msg.($raw_data === '' ? '' : '<pre>'.$raw_data.'</pre>');
    }

    /**
     * Prep Quoted Printable
     *
     * Prepares string for Quoted-Printable Content-Transfer-Encoding
     * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
     *
     * @param	string
     * @return	string
     */
    protected function _prep_quoted_printable($str)
    {
        // ASCII code numbers for "safe" characters that can always be
        // used literally, without encoding, as described in RFC 2049.
        // http://www.ietf.org/rfc/rfc2049.txt
        static $ascii_safe_chars = array(
            // ' (  )   +   ,   -   .   /   :   =   ?
            39, 40, 41, 43, 44, 45, 46, 47, 58, 61, 63,
            // numbers
            48, 49, 50, 51, 52, 53, 54, 55, 56, 57,
            // upper-case letters
            65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90,
            // lower-case letters
            97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122
        );

        // We are intentionally wrapping so mail servers will encode characters
        // properly and MUAs will behave, so {unwrap} must go!
        $str = str_replace(array('{unwrap}', '{/unwrap}'), '', $str);

        // RFC 2045 specifies CRLF as "\r\n".
        // However, many developers choose to override that and violate
        // the RFC rules due to (apparently) a bug in MS Exchange,
        // which only works with "\n".
        if ($this->crlf === "\r\n")
        {
            return quoted_printable_encode($str);
        }

        // Reduce multiple spaces & remove nulls
        $str = preg_replace(array('| +|', '/\x00+/'), array(' ', ''), $str);

        // Standardize newlines
        if (strpos($str, "\r") !== FALSE)
        {
            $str = str_replace(array("\r\n", "\r"), "\n", $str);
        }

        $escape = '=';
        $output = '';

        foreach (explode("\n", $str) as $line)
        {
            $length = mb_strlen($line, '8bit');
            $temp = '';

            // Loop through each character in the line to add soft-wrap
            // characters at the end of a line " =\r\n" and add the newly
            // processed line(s) to the output (see comment on $crlf class property)
            for ($i = 0; $i < $length; $i++)
            {
                // Grab the next character
                $char = $line[$i];
                $ascii = ord($char);

                // Convert spaces and tabs but only if it's the end of the line
                if ($ascii === 32 OR $ascii === 9)
                {
                    if ($i === ($length - 1))
                    {
                        $char = $escape.sprintf('%02s', dechex($ascii));
                    }
                }
                // DO NOT move this below the $ascii_safe_chars line!
                //
                // = (equals) signs are allowed by RFC2049, but must be encoded
                // as they are the encoding delimiter!
                elseif ($ascii === 61)
                {
                    $char = $escape.strtoupper(sprintf('%02s', dechex($ascii)));  // =3D
                }
                elseif ( ! in_array($ascii, $ascii_safe_chars, TRUE))
                {
                    $char = $escape.strtoupper(sprintf('%02s', dechex($ascii)));
                }

                // If we're at the character limit, add the line to the output,
                // reset our temp variable, and keep on chuggin'
                if ((mb_strlen($temp, '8bit') + mb_strlen($char, '8bit')) >= 76)
                {
                    $output .= $temp.$escape.$this->crlf;
                    $temp = '';
                }

                // Add the character to our temporary line
                $temp .= $char;
            }

            // Add our completed line to the output
            $output .= $temp.$this->crlf;
        }

        // get rid of extra CRLF tacked onto the end
        return self::substr($output, 0, mb_strlen($this->crlf, '8bit') * -1);
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
        $this->to           = null;
        $this->from         = null;
        $this->reply_to     = null;
        $this->cc           = null;
        $this->bcc          = null;
        $this->subject      = null;
        $this->html_message = null;
        $this->text_message = null;
        $this->headers      = [];

        return $this;
    }

    //--------------------------------------------------------------------
}
