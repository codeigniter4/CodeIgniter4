<?php namespace CodeIgniter\Mail;

use Mail\MessageInterface;

/**
 * Class Mail
 *
 * Represents a single email message
 *
 * @package CodeIgniter\Mail
 */
abstract class Message implements MessageInterface
{
    /**
     * Array of email/names to send the message to.
     * Example:
     *  [
     *      'name' => $email,
     *      0      => $email
     *  ]
     *
     * @var array
     */
    protected $to = [];

    /**
     * Array of email/names the message is from.
     * Example:
     *  [
     *      'name' => $email,
     *      0      => $email
     *  ]
     *
     * @var array
     */
    protected $from = [];

    /**
     * Array of email/names to CC the message to.
     *
     * @var array
     */
    protected $cc = [];

    /**
     * Array of email/names to BCC the message to.
     *
     * @var array
     */
    protected $bcc = [];

    /**
     * Email address/names used for reply-to
     *
     * @var string
     */
    protected $reply = [];

    /**
     * The subject line.
     *
     * @var string
     */
    protected $subject;

    /**
     *  The content that will be sent as the 'html'
     * portion of the message.
     *
     * @var string
     */
    protected $HTMLContent;

    /**
     * The content that will be sent as the 'text'
     * portion of the message.
     *
     * @var string
     */
    protected $textContent;

    /**
     * Files to be attached.
     *
     * @todo determine the best way to handle this and inline attachments.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Dynamic data passed into the class from
     * outside that is sent to the views.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Allows for the message itself to specify
     * the deliveryService that should be used.
     *
     * @var string
     */
    protected $deliveryService;

    /**
     * An array of custom simple key/value header pairs
     * that are sent IN ADDITION to the primary headers
     * used for standard mail parameters.
     *
     * @var array
     */
    protected $headers = [];

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
     * Sets a custom header value to be sent along with the email.
     *
     * @param string $name
     * @param null   $value
     *
     * @return $this
     */
    public function setHeader(string $name, $value = null)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Allows recipients to be passed in as any of the following:
     *
     *  - string: one@foo.com
     *  - string: one@foo.com,two@foo.com
     *  - array: [one@foo.com, two@foo.com]
     *  - array with names: ['John Smith' => 'one@foo.com']
     *
     * @param string      $emails
     * @param string|null $name
     *
     * @return array
     */
    protected function parseRecipients(string $emails, string $name = null)
    {
        $recipients = [];

        // A comma-separated string of emails only (i.e. foo@example.com,bar@example.com)
        if (is_string($emails) && mb_strpos($emails, ',') !== false)
        {
            $recipients = explode(',', $emails);

            $recipients = array_map('trim', $recipients);
        } // A single email
        elseif (is_string($emails))
        {

        } // An array of emails
        else
        {

        }

        return $recipients;
    }

    //--------------------------------------------------------------------

    /**
     * A generic method to set one or more emails/names to our various
     * address fields. Used by the Mailer class.
     *
     * @param             $emails
     * @param string|null $name
     * @param string      $type
     */
    public function setEmails($emails, string $name = null, string $type)
    {
        if (! in_array($type, ['to', 'from', 'cc', 'bcc', 'reply']))
        {
            throw new \InvalidArgumentException(lang('mail.badEmailsType'));
        }

        $this->{$type} = $this->parseRecipients($emails, $name);
    }

    /**
     * Magic method to allow the Mailer class to update class properties.
     *
     * @param string $key
     * @param        $value
     */
    public function __set(string $key, $value)
    {
        if (isset($this->$key))
        {
            $this->$key = $value;
        }
    }

    //--------------------------------------------------------------------

    /**
     * Magic getter for class properties.
     *
     * @param string $key
     *
     * @return null
     */
    public function __get(string $key)
    {
        if (isset($this->$key))
        {
            return $this->$key;
        }

        return null;
    }

    //--------------------------------------------------------------------
}
