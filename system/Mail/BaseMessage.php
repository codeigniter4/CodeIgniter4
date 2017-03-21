<?php namespace CodeIgniter\Mail;

use CodeIgniter\Mail\MailHandlerInterface;

class InvalidEmailAddress extends \Exception{};

abstract class BaseMessage implements MessageInterface
{
    protected $from    = [];
    protected $to      = [];
    protected $replyTo = [];
    protected $cc      = [];
    protected $bcc     = [];
    protected $subject;
    protected $returnPath;

    protected $messageHTML;
    protected $messageText;

    protected $attachments = [];

    /**
     * The from address that should be used
     * if nothing else is set.
     *
     * @var array
     */
    protected $defaultFrom;

    /**
     * Key/value pairs that are
     * sent to the views, if used.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The Handler that will be used to
     * send the message with.
     *
     * @var MailHandlerInterface
     */
    protected $handler;

    //--------------------------------------------------------------------

    /**
     * Takes an array of options whose keys should match
     * any of the class properties. The property values will be set.
     * Any unrecognized elements will be stored in the $data array.
     *
     * Example:
     *
     *  $message = new App\Mail\UserWelcome([
     *      'to' => 'John Doe <john.doe@example.com>',
     *      'from' => 'Jane Doe <jane.doe@example.com>'
     *  ]);
     *
     * @param array|null $options
     */
    public function __construct(array $options=null)
    {
        if (is_array($options))
        {
            $this->setOptions($options);
        }
    }

    //--------------------------------------------------------------------

    /**
     * Takes an array of options whose keys should match
     * any of the class properties. The property values will be set.
     * Any unrecognized elements will be stored in the $data array.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value)
        {
            if (property_exists($this, $key))
            {
                if (is_array($this->$key))
                {
                    $value = is_array($value) ? $value : [$value];
                }

                $this->$key = $value;

                continue;
            }

            $this->data[$key] = $value;
        }
    }

    //--------------------------------------------------------------------

    /**
     * Sets the active Handler instance that will be used to send.
     *
     * @param \CodeIgniter\Mail\MailHandlerInterface $handler
     *
     * @return $this
     */
    public function setHandler(MailHandlerInterface $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Called by the mailers prior to sending the message. Gives the message
     * a chance to do any custom setup, like loading views for the content, etc.
     *
     * @return mixed
     */
    abstract public function build();

    //--------------------------------------------------------------------

    /**
     * Works with the handler to actually send the message.
     *
     * @return bool
     */
    public function send()
    {
        if (! $this->handler instanceof MailHandlerInterface)
        {
            throw new \BadMethodCallException(lang('mail.invalidHandler'));
        }

        // run the build step so it can parse any view templates, etc.
        // and, generally, get the message ready to go.
        $this->build();

        // Ensure we have enough data to actually write a message for.
        if (! $this->isValid())
        {
            throw new \RuntimeException(lang('mail.emptyMessage'));
        }

        // Ensure we have a 'from' address or use the defaults from the config file.
        if (empty($this->from))
        {
            $this->from = $this->defaultFrom;
        }

        return $this->handler->send($this);
    }

    //--------------------------------------------------------------------

    /**
     * Returns the name / address of the person/people the email is from.
     * Return array MUST be formatted as name => email.
     *
     * @return array
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    //--------------------------------------------------------------------

    /**
     * Returns the 'ReturnPath' portion, which is automatically set
     * when setting the "from" value.
     *
     * @return string
     */
    public function getReturnPath(): string
    {
        return ! empty($this->returnPath)
            ? $this->returnPath
            : '';
    }


    /**
     * Sets the name and email address of one person this is from.
     * If this method is called multiple times, it adds multiple people
     * to the from value, it does not overwrite the previous one.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @param string      $returnPath
     *
     * @return \CodeIgniter\Mail\BaseMessage
     */
    public function setFrom(string $email, string $name=null, string $returnPath = null)
    {
        $recipient = is_null($name)
            ? [$email]
            : [$name => $email];

        $this->setRecipients($recipient, 'from');

        if (empty($returnPath))
        {
            $returnPath = $email;
        }

        $this->returnPath = '<'.$returnPath.'>';

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Allows multiple From name/email pairs to be set at once.
     * Arrays MUST be in name => email format.
     *
     * @param array $emails
     *
     * @return self
     */
    public function setFromMany(array $emails)
    {
        $this->setRecipients($emails, 'from');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the default from value that will be used if nothing else is provided.
     * This typically comes from Config\Mail.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return $this
     */
    public function setDefaultFrom(string $email, string $name=null)
    {
        $recipient = is_null($name)
            ? [$email]
            : [$name => $email];

        $this->setRecipients($recipient, 'defaultFrom');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Returns an array of all people the message is to. The array MUST
     * be formatted in name => email format.
     *
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    //--------------------------------------------------------------------

    /**
     * Adds a single person to the list of recipients.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setTo(string $email, string $name=null)
    {
        $recipient = is_null($name)
            ? [$email]
            : [$name => $email];

        $this->setRecipients($recipient, 'to');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Allows multiple recipients to be added at once. The array MUST
     * be formatted in name => email pairs.
     *
     * @param array $emails
     *
     * @return mixed
     */
    public function setToMany(array $emails)
    {
        $this->setRecipients($emails, 'to');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Returns all recipients listed in the Reply-To field.
     *
     * @return array
     */
    public function getReplyTo(): array
    {
        return $this->replyTo;
    }

    //--------------------------------------------------------------------

    /**
     * Adds a new recipient to the Reply-To header for this message.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setReplyTo(string $email, string $name=null)
    {
        $recipient = is_null($name)
            ? [$email]
            : [$name => $email];

        $this->setRecipients($recipient, 'replyTo');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets multiple Reply-To addresses at once. The array MUST be
     * formatted as name => email pairs.
     *
     * @param array $emails
     *
     * @return self
     */
    public function setReplyToMany(array $emails)
    {
        $this->setRecipients($emails, 'replyTo');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Gets a list of all names and addresses that should be CC'd
     * on this message.
     *
     * @return array
     */
    public function getCC(): array
    {
        return $this->cc;
    }

    //--------------------------------------------------------------------

    /**
     * Sets a single email/name to CC the message to.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setCC(string $email, string $name=null)
    {
        $recipient = is_null($name)
            ? [$email]
            : [$name => $email];

        $this->setRecipients($recipient, 'cc');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets multiple CC address/name pairs at once. The array MUST
     * be in name => email format:
     *
     * [
     *      'John Doe' => 'john.doe@example.com'
     * ]
     *
     * @param array $emails
     *
     * @return self
     */
    public function setCCMany(array $emails)
    {
        $this->setRecipients($emails, 'cc');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Returns an array of all people that should be BCC'd on this message.
     * The array is in name => email format:
     *
     * $bccs = [
     *      'John Doe' => 'john.doe@example.com'
     * ];
     *
     * @return array
     */
    public function getBCC(): array
    {
        return $this->bcc;
    }

    //--------------------------------------------------------------------

    /**
     * Adds another email address/name to the list of people to BCC.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setBCC(string $email, string $name=null)
    {
        $recipient = is_null($name)
            ? [$email]
            : [$name => $email];

        $this->setRecipients($recipient, 'bcc');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Takes an array email address/names that should be set as
     * the BCC addresses.
     *
     * @param array $emails
     *
     * @return self
     */
    public function setBCCMany(array $emails)
    {
        $this->setRecipients($emails, 'bcc');

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Retrieves the subject line of the message.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject ?? '';
    }

    //--------------------------------------------------------------------

    /**
     * Sets the subject line of the message.
     *
     * @param string $subject
     *
     * @return self
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Retrieves the contents of the HTML portion of the message.
     *
     * @return string
     */
    public function getHTMLMessage(): string
    {
        return $this->messageHTML ?? '';
    }

    //--------------------------------------------------------------------

    /**
     * Sets the contents of the HTML portion of the email message.
     *
     * @param string $html
     *
     * @return self
     */
    public function setHTMLMessage(string $html)
    {
        $this->messageHTML = $html;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Gets the current contents of the Text message.
     *
     * @return string
     */
    public function getTextMessage(): string
    {
        return $this->messageText ?? '';
    }

    //--------------------------------------------------------------------

    /**
     * Sets the Text content of the email.
     *
     * @param string $text
     *
     * @return self
     */
    public function setTextMessage(string $text)
    {
        $this->messageText = strip_tags($text);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Simple determination about the message type based on which
     * elements actually have content.
     *
     * @return string
     */
    public function messageType()
    {
        $type = ! empty($this->messageHTML)
            ? 'html'
            : 'plain';

        if (count($this->attachments) > 0)
        {
            $type .= '-attach';
        }

        return $type;
    }

    /**
     * Gets any viewdata that has been set.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    //--------------------------------------------------------------------

    /**
     * Sets an array of key/value pairs that are used like dynamic view
     * data to replace placeholders in the HTML and Text Messages when
     * they are parsed.
     *
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Determines if this message has the bare minimum information needed
     * to send a message, i.e. to, from, subject and some message.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->to)
            || empty($this->from)
            || empty($this->subject)
            || (empty($this->messageHTML) && empty($this->messageText));
    }

    //--------------------------------------------------------------------

    /**
     * Used by all of the setCC, setTo, etc methods that deal with email
     * addresses to validate and store the actual values. If any email
     * addresses are found that are not valid emails, will throw an exception
     * with a list of the ones that are not.
     *
     * @param array  $recipients
     * @param string $type
     *
     * @throws \CodeIgniter\Mail\InvalidEmailAddress
     */
    protected function setRecipients(array $recipients, string $type)
    {
        $invalids = [];

        foreach ($recipients as $name => $email)
        {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $invalids[] = $email;
                continue;
            }

            if (is_string($name) && ! empty($name))
            {
                $this->$type[$name] = $email;
                continue;
            }

            $this->$type[] = $email;
        }

        if (count($invalids))
        {
            throw new InvalidEmailAddress('The following email addresses are invalid: '. implode(', ', $invalids));
        }
    }

    //--------------------------------------------------------------------
}
