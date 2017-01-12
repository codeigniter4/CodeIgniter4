<?php namespace CodeIgniter\Mail\Handlers;

use CodeIgniter\Mail\BaseHandler;

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
    }

    /**
     * Does the actual delivery of a message.
     *
     * @param bool  $clear_after    If TRUE, will reset the class after sending.
     *
     * @return mixed
     */
    public function send(bool $clear_after=true)
    {

    }

    //--------------------------------------------------------------------
}
