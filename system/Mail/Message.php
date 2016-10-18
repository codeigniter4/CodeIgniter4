<?php namespace CodeIgniter\Mail;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\Mail\MessageInterface;
use CodeIgniter\HTTP\HeaderTrait;

/**
 * Class Mail
 *
 * Represents a single email message
 *
 * @package CodeIgniter\Mail
 */
abstract class Message implements MessageInterface
{
    // Most mail information is stored in the headers
    use HeaderTrait;

    /**
     *  The content that will be sent as the 'html'
     * portion of the message.
     *
     * @var string
     */
    public $HTMLContent;

    /**
     * The content that will be sent as the 'text'
     * portion of the message.
     *
     * @var string
     */
    public $textContent;

    /**
     * Files to be attached.
     *
     * @todo determine the best way to handle this and inline attachments.
     *
     * @var array
     */
    public $attachments = [];

    /**
     * Dynamic data passed into the class from
     * outside that is sent to the views.
     *
     * @var array
     */
    public $data = [];

    /**
     * Allows for the message itself to specify
     * the deliveryService that should be used.
     *
     * @var string
     */
    public $handlerName;

    /**
     * Holds a "clean" version of the subject
     * before it's been Q Encoded.
     *
     * @var string
     */
    public $subject;

    /**
     * CRLF character sequence
     *
     * RFC 2045 specifies that for 'quoted-printable' encoding,
     * "\r\n" must be used. However, it appears that some servers
     * (even on the receiving end) don't handle it properly and
     * switching to "\n", while improper, is the only solution
     * that seems to work for all environments.
     *
     * @link	http://www.ietf.org/rfc/rfc822.txt
     * @var	string
     */
    public $CRLF = "\n";

    /**
     * Character set (default: utf-8)
     *
     * @var	string
     */
    public $charset = 'utf-8';

    /**
     * X-Priority header value.
     *
     * @var	int	1-5
     */
    public $priority	= 3;    // Default priority (1 - 5)

    /**
     * Whether to apply word-wrapping to the message body.
     *
     * @var bool
     */
    public $wordwrap = true;

    /**
     * Number of characters to wrap at.
     *
     * @var int
     */
    public $wrapChars = 76;

    /**
     * Newline character sequence.
     * Use "\r\n" to comply with RFC 822.
     *
     * @link	http://www.ietf.org/rfc/rfc822.txt
     * @var	string	"\r\n" or "\n"
     */
    public $newline = "\n"; // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)

    /**
     * Mail encoding
     *
     * @var string
     */
    public $encoding = '8bit';

    /**
     * Whether to send multipart alternatives.
     * Yahoo! doesn't seem to like these.
     *
     * @var bool
     */
    public $sendMultipart = true;

    //--------------------------------------------------------------------

    /**
     * Mail constructor.
     *
     * Accepts an array of data from outside that can be used as dynamic
     * data for the views, etc.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (! empty($data))
        {
            $this->data = $data;
        }
    }

    //--------------------------------------------------------------------

    /**
     * The method called by the MailServices that allows this email
     * message to be built. Within this method, the developer will typically
     * set the HTMLContent and/or textContent variables, as well
     * as overriding any default to/from/reply-to/etc.
     *
     * @return mixed
     */
    abstract public function build();

    //--------------------------------------------------------------------

    /**
     * Sets the subject line for the email.
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        $subject = $this->prepQEncoding($subject);

        $this->setHeader('Subject', $subject);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the HTML portion of the message, which will typically be the
     * message that's sent nowadays. If HTML content exists, but no TextContent
     * exists, the TextContent will be created by stripping the HTML.
     *
     * @param string $body
     *
     * @return $this
     */
    public function setHTMLBody(string $body)
    {
        $this->HTMLContent = rtrim(str_replace("\r", '', $body));

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the content used as the text or alternate portion of the body.
     * This is intended to be used with setHTMLContent setting the primary message.
     *
     * @param string $body
     *
     * @return $this
     */
    public function setTextBody(string $body)
    {
        $this->textContent = $body;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * A generic method to set one or more emails/names to our various
     * address fields. Used by the Mailer class.
     *
     * @param             $emails
     * @param string|null $name
     * @param string      $type
     *
     * @return $this
     */
    public function setEmails($emails, string $name = null, string $type)
    {
        if (! in_array(strtolower($type), ['to', 'from', 'cc', 'bcc', 'reply']))
        {
            throw new \InvalidArgumentException(lang('mail.badEmailsType'));
        }

        $this->setHeader(ucfirst($type), $this->parseRecipients($emails, $name));

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Determines whether the message should be wrapped or not. This should
     * typically be TRUE to ensure broadest compatibility across servers.
     *
     * @param bool $wrap
     *
     * @return $this
     */
    public function setWordWrap(bool $wrap = true)
    {
        $this->wordwrap = $wrap;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Set Priority
     *
     * @param	int
     * @return	$this
     */
    public function setPriority($n = 3)
    {
        $this->priority = preg_match('/^[1-5]$/', $n) ? (int) $n : 3;

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Set Newline Character
     *
     * @param	string
     * @return	$this
     */
    public function setNewline($newline = "\n")
    {
        $this->newline = in_array($newline, ["\n", "\r\n", "\r"]) ? $newline : "\n";

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Set CRLF
     *
     * @param	string
     *
     * @return	$this
     */
    public function setCRLF($crlf = "\n")
    {
        $this->CRLF = ($crlf !== "\n" && $crlf !== "\r\n" && $crlf !== "\r") ? "\n" : $crlf;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Allows recipients to be passed in as any of the following:
     *
     *  - string: one@foo.com
     *  - string: one@foo.com,two@foo.com
     *  - array: [one@foo.com, two@foo.com]
     *  - array with names: ['John Smith' => 'one@foo.com']
     *
     * @param string}array $emails
     * @param string|null  $name
     *
     * @return array
     */
    protected function parseRecipients($emails, string $name = null): array
    {
        $recipients = [];

        // A comma-separated string of emails only (i.e. foo@example.com,bar@example.com)
        if (is_string($emails) && mb_strpos($emails, ',') !== false)
        {
            $recipients = explode(',', $emails);
            $recipients = array_map('trim', $recipients);
        }
        // A single email
        elseif (is_string($emails))
        {
            $recipients[] = empty($name)
                ? [trim($emails)]
                : [trim($name) => $this->cleanEmail($emails)];
        }
        // An array of emails
        elseif (is_array($emails))
        {
            foreach ($emails as $name => $email)
            {
                if (is_numeric($name))
                {
                    $recipients[] = $this->cleanEmail($email);
                }
                else
                {
                    $recipients[] = [trim($name) => $this->cleanEmail($email)];
                }
            }
        }

        return $this->prepareRecipients($recipients);
    }

    //--------------------------------------------------------------------

    /**
     * Formats the email names and ensures they've been encoded correctly.
     *
     * Expects:
     *  [
     *      'John Doe' => 'john.doe@example.com',
     *      'Jane Doe' => 'jane.doe@example.com'
     *  ]
     *
     * Returns:
     *  [
     *      'John Doe <john.doe@example.com>',
     *      'Jane Doe <jane.doe@example.com>'
     *  ]
     *
     * @param array $recipients
     *
     * @return array
     */
    protected function prepareRecipients(array $recipients): array
    {
        $prepared = [];

        foreach ($recipients as $name => $email)
        {
            if (! is_numeric($name))
            {
                // only use Q encoding if there are characters that would require it
                if (! preg_match('/[\200-\377]/', $name))
                {
                    // add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
                    $name = '"'.addcslashes($name, "\0..\37\177'\"\\").'"';
                }
                else
                {
                    $name = $this->prepQEncoding($name);
                }
            }
            else
            {
                $name = '';
            }

            if (is_array($email) && count($email) === 1)
            {
                $email = array_pop($email);
            }

            if (empty($name))
            {
                $prepared[] = $email;
            }
            else
            {
                $prepared[] = trim("{$name} <{$email}>");
            }
        }

        return $prepared;
    }

    //--------------------------------------------------------------------

    /**
     * Ensures we have a valid email by grabbing it from an
     * extended email, if exists, i.e. Joe Smith <joe@smith.com>
     *
     * @param string $email
     *
     * @return string
     */
    public function cleanEmail(string $email): string
    {
        if (! is_array($email))
        {
            return preg_match('/\<(.*)\>/', $email, $match)
                ? $match[1]
                : trim($email);
        }

        $cleaned = [];

        foreach ($email as $address)
        {
            $cleaned[] = preg_match('/\<(.*)\>/', $address, $match)
                ? $match[1]
                : trim($address);
        }

        return $cleaned;
    }

    //--------------------------------------------------------------------

    /**
     * Prep Quoted Printable
     *
     * Prepares string for Quoted-Printable Content-Transfer-Encoding
     * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
     *
     * @param	string
     * @return	string
     */
    public function prepQuotedPrintable(string $str): string
    {
        // ASCII code numbers for "safe" characters that can always be
        // used literally, without encoding, as described in RFC 2049.
        // http://www.ietf.org/rfc/rfc2049.txt
        static $ascii_safe_chars = [
            // ' (  )   +   ,   -   .   /   :   =   ?
            39, 40, 41, 43, 44, 45, 46, 47, 58, 61, 63,
            // numbers
            48, 49, 50, 51, 52, 53, 54, 55, 56, 57,
            // upper-case letters
            65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90,
            // lower-case letters
            97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122
        ];

        // We are intentionally wrapping so mail servers will encode characters
        // properly and MUAs will behave, so {unwrap} must go!
        $str = str_replace(['{unwrap}', '{/unwrap}'], '', $str);

        // RFC 2045 specifies CRLF as "\r\n".
        // However, many developers choose to override that and violate
        // the RFC rules due to (apparently) a bug in MS Exchange,
        // which only works with "\n".
        if ($this->CRLF === "\r\n")
        {
            return quoted_printable_encode($str);
        }

        // Reduce multiple spaces & remove nulls
        $str = preg_replace(['| +|', '/\x00+/'], [' ', ''], $str);

        // Standardize newlines
        if (strpos($str, "\r") !== false)
        {
            $str = str_replace(["\r\n", "\r"], "\n", $str);
        }

        $escape = '=';
        $output = '';

        foreach (explode("\n", $str) as $line)
        {
            $length = strlen($line);
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
                if ($ascii === 32 || $ascii === 9)
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
                if ((strlen($temp) + strlen($char)) >= 76)
                {
                    $output .= $temp.$escape.$this->CRLF;
                    $temp = '';
                }

                // Add the character to our temporary line
                $temp .= $char;
            }

            // Add our completed line to the output
            $output .= $temp.$this->CRLF;
        }

        // get rid of extra CRLF tacked onto the end
        return substr($output, 0, strlen($this->CRLF) * -1);
    }

    //--------------------------------------------------------------------

    /**
     * Prep Q Encoding
     *
     * Performs "Q Encoding" on a string for use in email headers.
     * It's related but not identical to quoted-printable, so it has its
     * own method.
     *
     * @param	string
     * @return	string
     */
    protected function prepQEncoding($str)
    {
        $str = str_replace(["\r", "\n"], '', $str);

        if ($this->charset === 'utf-8')
        {
            // Note: We used to have mb_encode_mimeheader() as the first choice
            //       here, but it turned out to be buggy and unreliable. DO NOT
            //       re-add it! -- Narf
            if (ICONV_ENABLED === true)
            {
                $output = @iconv_mime_encode('', $str,
                    [
                        'scheme' => 'Q',
                        'line-length' => 76,
                        'input-charset' => $this->charset,
                        'output-charset' => $this->charset,
                        'line-break-chars' => $this->CRLF
                    ]
                );

                // There are reports that iconv_mime_encode() might fail and return FALSE
                if ($output !== false)
                {
                    // iconv_mime_encode() will always put a header field name.
                    // We've passed it an empty one, but it still prepends our
                    // encoded string with ': ', so we need to strip it.
                    return substr($output, 2);
                }

                $chars = iconv_strlen($str, 'UTF-8');
            }
            elseif (MB_ENABLED === true)
            {
                $chars = mb_strlen($str, 'UTF-8');
            }
        }

        // We might already have this set for UTF-8
        isset($chars) || $chars = strlen($str);

        $output = '=?'.$this->charset.'?Q?';
        for ($i = 0, $length = strlen($output); $i < $chars; $i++)
        {
            $chr = ($this->charset === 'utf-8' && ICONV_ENABLED === true)
                ? '='.implode('=', str_split(strtoupper(bin2hex(iconv_substr($str, $i, 1, $this->charset))), 2))
                : '='.strtoupper(bin2hex($str[$i]));

            // RFC 2045 sets a limit of 76 characters per line.
            // We'll append ?= to the end of each line though.
            if ($length + ($l = strlen($chr)) > 74)
            {
                $output .= '?='.$this->crlf // EOL
                           .' =?'.$this->charset.'?Q?'.$chr; // New line
                $length = 6 + mb_strlen($this->charset) + $l; // Reset the length for the new line
            }
            else
            {
                $output .= $chr;
                $length += $l;
            }
        }

        // End the header
        return $output.'?=';
    }

    //--------------------------------------------------------------------

}
