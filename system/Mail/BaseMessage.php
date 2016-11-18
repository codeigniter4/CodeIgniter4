<?php namespace CodeIgniter\Mail;

/**
 * Class BaseMessage
 *
 * @property $from
 * @property $to
 * @property $replyTo
 * @property $cc
 * @property $bcc
 * @property $subject
 * @property $messageHTML
 * @property $messageText
 *
 * @property $data
 *
 * @package CodeIgniter\Mail
 */
abstract class BaseMessage
{
    protected $from;
    protected $to;
    protected $replyTo;
    protected $cc;
    protected $bcc;
    protected $subject;

    protected $messageHTML;
    protected $messageText;

    /**
     * Key/value pairs that are
     * sent to the views, if used.
     *
     * @var array
     */
    protected $data = [];

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
                $this->$key = $value;
                continue;
            }

            $this->data[$key] = $value;
        }
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

    public function __set(string $name, $value = null)
    {
        if (property_exists($this, $name))
        {
            $this->$name = $value;
            return;
        }

        $this->data[$name] = $value;
    }

    //--------------------------------------------------------------------

    public function __get(string $name)
    {
        return property_exists($this, $name)
            ? $this->$name
            : $this->data[$name]
              ?? null;
    }

    //--------------------------------------------------------------------
}
