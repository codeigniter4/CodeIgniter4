<?php namespace CodeIgniter\Mail;

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
     * @var \CodeIgniter\Mail\MessageInterface
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
     * @param MessageInterface $message
     * @param                  $config
     */
    public function __construct($config, MessageInterface $message = null)
    {
        $this->message = $message;
        $this->config  = $config;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the handler that should be used for this message.
     *
     * @param string $handler
     *
     * @return $this
     */
    public function setHandler(string $handler)
    {
        if (! array_key_exists($handler, $this->config->availableHandlers))
        {
            throw new \InvalidArgumentException(sprintf(lang('mail.handlerNotFound'), $handler));
        }

        $this->message->handler = $handler;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the message class to use.
     *
     * @param MessageInterface $message
     *
     * @return $this
     */
    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;

        // Set the default values from the config file first.
        $this->setMessageDefaults();

        // then allow the message to override the values.
        $this->message->build();

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the current Mail Message that we're using.
     *
     * @return \CodeIgniter\Mail\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    //--------------------------------------------------------------------
    // Senders
    //--------------------------------------------------------------------

    /**
     * Fires up a new Mailer instance, and ships our message off to it.
     *
     * @param bool $keepData
     */
    public function send(bool $keepData = false)
    {
        $handler = $this->message->handlerName ?? $this->config->handler;

        // Make sure we have a valid handler
        if (! array_key_exists($handler, $this->config->availableHandlers))
        {
            throw new \InvalidArgumentException(sprintf(lang('mail.invalidHandler'), $handler));
        }

        // Make it!
        $handler = new $this->config->availableHandlers[$handler]($this->config);

        $handler->setMessage($this->message)
                ->send();
    }

    //--------------------------------------------------------------------

    /**
     * Sends a message to the current Queue, instead of actually sending
     * the message. This can allow sending the email asynchronously, thus
     * dramatically improving app performance when used with multiple recipients.
     *
     * @param bool $keepData
     */
    public function queue(bool $keepData = false)
    {
        // @todo Implment email queueing once CI has queues.
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
     * @param string $subject
     * @param array  $pairs     key/value pairs to replace placeholders in subject.
     *
     * @return $this
     */
    public function setSubject(string $subject, array $pairs = null)
    {
        if (! empty($pairs))
        {
            $replace = [];

            foreach ($pairs as $key => $val)
            {
                $replace['{'.$key.'}'] = $val;
            }

            $subject = strtr($subject, $replace);
            unset($replace);
        }

        $this->message->setSubject($subject);

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

    /**
     * Sets up any default values we have from the config file.
     */
    public function setMessageDefaults()
    {
        // From
        $this->setFrom($this->config->defaultFrom['email'], $this->config->defaultFrom['name']);
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Attachment Methods
    //--------------------------------------------------------------------


    //--------------------------------------------------------------------
    // Magic Time
    //--------------------------------------------------------------------

    /**
     * Provides access to methods with the Message itself, mainly to
     * access the common setPriority, etc. methods.
     *
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public function __call(string $name, array $params = [])
    {
        if (! $this->message instanceof MessageInterface)
        {
            throw new \RuntimeException(lang('mail.missingMessage'));
        }

        if (method_exists($this->message, $name))
        {
            $this->message->{$name}(...$params);

            return $this;
        }
    }

}
