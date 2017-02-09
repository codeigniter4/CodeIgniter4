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
     * Adds an attachment to the current email that is being built.
     *
     * @param string $filename
     * @param string $disposition like 'inline'. Default is 'attachment'
     * @param string $newname     If you'd like to rename the file for delivery
     * @param string $mime        Custom defined mime type.
     */
    public function attach(string $filename, string $disposition = null, string $newname = null, string $mime = null)
    {
        return;
    }

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
    // Options
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
