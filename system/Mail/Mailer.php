<?php namespace CodeIgniter\Mail;

use Mail\MessageInterface;

/**
 * Class Mailer
 *
 * Accepts a Mail class, allows customization of the
 * message parameters, and fires up the correct Handler
 * to send or queue the message.
 *
 * Example (combined with helper):
 *
 *  mail(new SomeMailClass())
 *      ->setData($params)
 *      ->setTo(johnsmith@example.com)
 *      ->attach($filepath)
 *      ->send();
 *
 * @package CodeIgniter\Mail
 */
class Mailer
{
    /**
     * The Mail\Message class that represents
     * the email to be sent.
     *
     * @var \Mail\MessageInterface
     */
    protected $message;

    /**
     * The interface we should use
     *
     * @var \CodeIgniter\Mail\MailHandlerInterface
     */
    protected $handler;

    /**
     * An instance of our config class.
     *
     * @var \Config\Mail
     */
    protected $config;

    //--------------------------------------------------------------------

    /**
     * Mailer constructor.
     *
     * Takes an instance of a Mail\Message class, and the configuration
     * file, and takes care of
     *
     * @param \Mail\MessageInterface $message
     * @param                        $config
     */
    public function __construct($config, MessageInterface $message = null)
    {
        $this->message = $message;
        $this->config  = $config;
    }

    //--------------------------------------------------------------------

    public function setHandler(string $handler)
    {
        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the message class to use.
     *
     * @param \Mail\MessageInterface $message
     *
     * @return $this
     */
    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;

        return $this;
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Senders
    //--------------------------------------------------------------------

    public function send(bool $keepData = false)
    {

    }

    //--------------------------------------------------------------------

    public function queue(bool $keepData = false)
    {

    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Basic Envelope Info
    //--------------------------------------------------------------------

    /**
     * Sets one or more addresses as the To value for this message.
     *
     * @param string|array $emails
     * @param string|null  $name
     *
     * @return self
     */
    public function setTo($emails, string $name = null)
    {
        $this->message->setEmails($emails, $name, 'to');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets one or more addresses as the From value for this message.
     *
     * @param             $emails
     * @param string|null $name
     * @param string|null $return
     *
     * @return self
     */
    public function setFrom($emails, string $name = null, string $return = null)
    {
        $this->message->setEmails($emails, $name, 'from');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets one or more addresses as the Reply-To email for this message.
     *
     * @param string      $emails
     * @param string|null $name
     *
     * @return $this
     */
    public function setReplyTo($emails, string $name = null)
    {
        $this->message->setEmails($emails, $name, 'reply');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets one or more addresses as the CC value for this message.
     *
     * @param             $emails
     * @param string|null $name
     *
     * @return $this
     */
    public function setCC($emails, string $name = null)
    {
        $this->message->setEmails($emails, $name, 'cc');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets one or more addresses as the BCC value for this message.
     *
     * @param             $emails
     * @param string|null $name
     *
     * @return $this
     */
    public function setBCC($emails, string $name = null)
    {
        $this->message->setEmails($emails, $name, 'bcc');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the subject line for this message.
     *
     * @todo Does this need to be sanitized to meet RFC?
     * @todo Some form of variable replacement would be nice here for name replacement, etc?
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->message->subject = $subject;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the dynamic data that will be made available
     * to the views responsible for the content.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->message->data = $data;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets a custom header value to be sent along with the email.
     *
     * @param string $name
     * @param null   $value
     *
     * @return $this
     */
    public function setHeader(string $name, $value = null)
    {
        $this->message->setHeader($name, $value);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Initializes the email variables to their initial state.
     *
     * @param bool $resetAttachments
     *
     * @return $this
     */
    public function reset(bool $resetAttachments = false)
    {
        // @todo Complete me.

        return $this;
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Attachment Methods
    //--------------------------------------------------------------------



}
