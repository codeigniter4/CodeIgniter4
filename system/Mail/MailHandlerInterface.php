<?php namespace CodeIgniter\Mail;

interface MailHandlerInterface
{
    /**
     * Does the actual delivery of a message.
     *
     * @param \CodeIgniter\Mail\MessageInterface $message
     * @param bool                               $clear_after If TRUE, will reset the class after sending.
     *
     * @return mixed
     */
    public function send(MessageInterface $message, bool $clear_after=true);

    //--------------------------------------------------------------------

    /**
     * Adds an attachment to the current email that is being built.
     *
     * @param string    $filename
     * @param string    $disposition    like 'inline'. Default is 'attachment'
     * @param string    $newname        If you'd like to rename the file for delivery
     * @param string    $mime           Custom defined mime type.
     */
    public function attach(string $filename, string $disposition=null, string $newname=null, string $mime=null);

    //--------------------------------------------------------------------

    /**
     * Sets a header value for the email. Not every service will provide this.
     *
     * @param $field
     * @param $value
     * @return mixed
     */
    public function setHeader(string $field, $value);

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Options
    //--------------------------------------------------------------------

    /**
     * Sets the format to send the email in. Either 'html' or 'text'.
     * The handlers should attempt to determine the correct type based
     * on whether the status of $messageHTML and $messageText.
     *
     * @param $format
     * @return mixed
     */
    public function format(string $format);

    //--------------------------------------------------------------------

    /**
     * Resets the state to blank, ready for a new email. Useful when
     * sending emails in a loop and you need to make sure that the
     * email is reset.
     *
     * @param bool $clear_attachments
     * @return mixed
     */
    public function reset(bool $clear_attachments=true);

    //--------------------------------------------------------------------
}
