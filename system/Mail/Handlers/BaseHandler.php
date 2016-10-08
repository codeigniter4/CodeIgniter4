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
    use HeaderTrait;

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @var Mail
     */
    protected $config;

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
    public function queue()
    {

    }

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
        $this->message = $message;

        $this->parseMessage($message);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Takes the message, and parses it, setting our correct
     * headers to get the message ready to send.
     *
     * @param \CodeIgniter\Mail\MessageInterface $message
     */
    public function parseMessage(MessageInterface $message)
    {
        die(var_dump((string)$this->getHeader('to')));
    }

    //--------------------------------------------------------------------


}
