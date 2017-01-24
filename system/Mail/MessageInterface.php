<?php namespace CodeIgniter\Mail;

interface MessageInterface
{
    /**
     * Takes an array of options whose keys should match
     * any of the class properties. The property values will be set.
     * Any unrecognized elements will be stored in the $data array
     * to act as view data.
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
    public function __construct(array $options=null);

    //--------------------------------------------------------------------

    /**
     * Takes an array of options whose keys should match
     * any of the class properties. The property values will be set.
     * Any unrecognized elements will be stored in the $data array.
     *
     * @param array $options
     */
    public function setOptions(array $options);

    //--------------------------------------------------------------------

    /**
     * Called by the mailers prior to sending the message. Gives the message
     * a chance to do any custom setup, like loading views for the content, etc.
     *
     * @return mixed
     */
     public function build();

    //--------------------------------------------------------------------

    /**
     * Returns the name / address of the person/people the email is from.
     * Return array MUST be formatted as name => email.
     *
     * @return array
     */
    public function getFrom(): array;

    //--------------------------------------------------------------------

    /**
     * Sets the name and email address of one person this is from.
     * If this method is called multiple times, it adds multiple people
     * to the from value, it does not overwrite the previous one.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setFrom(string $email, string $name=null);

    //--------------------------------------------------------------------

    /**
     * Allows multiple From name/email pairs to be set at once.
     * Arrays MUST be in name => email format.
     *
     * @param array $emails
     *
     * @return self
     */
    public function setFromMany(array $emails);

    //--------------------------------------------------------------------

    /**
     * Returns an array of all people the message is to. The array MUST
     * be formatted in name => email format.
     *
     * @return array
     */
    public function getTo(): array;

    //--------------------------------------------------------------------

    /**
     * Adds a single person to the list of recipients.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setTo(string $email, string $name=null);

    //--------------------------------------------------------------------

    /**
     * Allows multiple recipients to be added at once. The array MUST
     * be formatted in name => email pairs.
     *
     * @param array $emails
     *
     * @return mixed
     */
    public function setToMany(array $emails);

    //--------------------------------------------------------------------

    /**
     * Returns all recipients listed in the Reply-To field.
     *
     * @return array
     */
    public function getReplyTo(): array;

    //--------------------------------------------------------------------

    /**
     * Adds a new recipient to the Reply-To header for this message.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setReplyTo(string $email, string $name=null);

    //--------------------------------------------------------------------

    /**
     * Sets multiple Reply-To addresses at once. The array MUST be
     * formatted as name => email pairs.
     *
     * @param array $emails
     *
     * @return self
     */
    public function setReplyToMany(array $emails);

    //--------------------------------------------------------------------

    /**
     * Gets a list of all names and addresses that should be CC'd
     * on this message.
     *
     * @return array
     */
    public function getCC(): array;

    //--------------------------------------------------------------------

    /**
     * Sets a single email/name to CC the message to.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setCC(string $email, string $name=null);

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
    public function setCCMany(array $emails);

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
    public function getBCC(): array;

    //--------------------------------------------------------------------

    /**
     * Adds another email address/name to the list of people to BCC.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return self
     */
    public function setBCC(string $email, string $name=null);

    //--------------------------------------------------------------------

    /**
     * Takes an array email address/names that should be set as
     * the BCC addresses.
     *
     * @param array $emails
     *
     * @return self
     */
    public function setBCCMany(array $emails);

    //--------------------------------------------------------------------

    /**
     * Retrieves the subject line of the message.
     *
     * @return string
     */
    public function getSubject(): string;

    //--------------------------------------------------------------------

    /**
     * Sets the subject line of the message.
     *
     * @param string $subject
     *
     * @return self
     */
    public function setSubject(string $subject);

    //--------------------------------------------------------------------

    /**
     * Retrieves the contents of the HTML portion of the message.
     *
     * @return string
     */
    public function getHTMLMessage(): string;

    //--------------------------------------------------------------------

    /**
     * Sets the contents of the HTML portion of the email message.
     *
     * @param string $html
     *
     * @return self
     */
    public function setHTMLMessage(string $html);

    //--------------------------------------------------------------------

    /**
     * Gets the current contents of the Text message.
     *
     * @return string
     */
    public function getTextMessage(): string;

    //--------------------------------------------------------------------

    /**
     * Sets the Text content of the email.
     *
     * @param string $text
     *
     * @return self
     */
    public function setTextMessage(string $text);

    //--------------------------------------------------------------------

    /**
     * Gets any viewdata that has been set.
     *
     * @return array
     */
    public function getData(): array;

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
    public function setData(array $data);

    //--------------------------------------------------------------------
}
