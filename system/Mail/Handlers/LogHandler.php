<?php namespace CodeIgniter\Mail\Handlers;

use CodeIgniter\Mail\MailHandlerInterface;

class LogHandler implements MailHandlerInterface
{
    protected $format;
    protected $headers = [];
    protected $subject;

    protected $messageHTML;
    protected $messageText;

    //--------------------------------------------------------------------

    /**
     * Does the actual delivery of a message. In this case, though, we simply
     * write the html and text files out to the log folder/emails.
     *
     * The filename format is: yyyymmddhhiiss_email.{format}
     *
     * @param bool  $clear_after    If TRUE, will reset the class after sending.
     *
     * @return mixed
     */
    public function send(bool $clear_after=true)
    {
        // Ensure we have enough data
        if (empty($this->to) || empty($this->subject) ||
            (empty($this->messageHTML) && empty($this->messageText))
        )
        {
            throw new \RuntimeException( lang('mail.invalid_log_data') );
        }

        $symbols = ['#', '%', '&', '{', '}', '\\', '/', '<', '>', '*', '?', ' ', '$', '!', '\'', '"', ':', '@', '+', '`', '='];

        $email = str_replace($symbols, '.', strtolower($this->to) );

        $filename = date('YmdHis_'). $email;

        // Ensure the emails folder exists in the log folder.
        $path = config_item('log_path');
        $path = ! empty( $path ) ? $path : APPPATH .'logs/';
        $path = rtrim($path, '/ ') .'/email/';

        if (! is_dir($path))
        {
            mkdir($path, 0777, true);
        }

        helper('file');

        // Write our HTML file out
        if (! empty($this->messageHTML) && ! write_file( $path . $filename . '.html', $this->messageHTML ) )
        {
            throw new \RuntimeException( sprintf( lang('mail.error_html_log'), $path, $filename) );
        }

        // Write our TEXT file out
        if (! empty($this->messageText) && ! write_file( $path . $filename . '.txt', $this->messageText ) )
        {
            throw new \RuntimeException( sprintf( lang('mail.error_text_log'), $path, $filename) );
        }

        return true;
    }

    //--------------------------------------------------------------------

    /**
     * Adds an attachment to the current email that is being built.
     *
     * @param string    $filename
     * @param string    $disposition    like 'inline'. Default is 'attachment'
     * @param string    $newname        If you'd like to rename the file for delivery
     * @param string    $mime           Custom defined mime type.
     */
    public function attach(string $filename, string $disposition=null, string $newname=null, string $mime=null)
    {
        return;
    }

    //--------------------------------------------------------------------

    /**
     * Sets a header value for the email. Not every service will provide this.
     *
     * @param $field
     * @param $value
     * @return mixed
     */
    public function setHeader(string $field, $value)
    {
        $this->headers[$field] = $value;

        return $this;
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Options
    //--------------------------------------------------------------------

    /**
     * Sets the email address to send the email to.
     *
     * @param $email
     * @return mixed
     */
    public function to(string $email)
    {
        $this->to = $email;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets who the email is coming from.
     *
     * @param $email
     * @param null $name
     * @return mixed
     */
    public function from(string $email, string $name=null)
    {
        if (! empty($name))
        {
            $this->from = [$email, $name];
        }
        else
        {
            $this->from = $email;
        }

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets a single additional email address to 'cc'.
     *
     * @param $email
     * @return mixed
     */
    public function CC(string $email)
    {
        $this->cc = $email;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets a single email address to 'bcc' to.
     *
     * @param $email
     * @return mixed
     */
    public function BCC(string $email)
    {
        $this->bcc = $email;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the reply to address.
     *
     * @param $email
     * @return mixed
     */
    public function replyTo(string $email, string $name=null)
    {
        if (! empty($name))
        {
            $this->reply_to = [$email, $name];
        }
        else
        {
            $this->reply_to = $email;
        }

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the subject line of the email.
     *
     * @param $subject
     * @return mixed
     */
    public function subject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the HTML portion of the email address. Optional.
     *
     * @param $message
     * @return mixed
     */
    public function messageHTML(string $message)
    {
        $this->html_message = $message;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the text portion of the email address. Optional.
     *
     * @param $message
     * @return mixed
     */
    public function messageText(string $message)
    {
        $this->text_message = $message;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the format to send the email in. Either 'html' or 'text'.
     *
     * @param $format
     * @return mixed
     */
    public function format(string $format)
    {
        $this->format = $format;

        return $this;
    }

    //--------------------------------------------------------------------
    /**
     * Resets the state to blank, ready for a new email. Useful when
     * sending emails in a loop and you need to make sure that the
     * email is reset.
     *
     * @param bool $clear_attachments
     * @return mixed
     */
    public function reset(bool $clear_attachments=true)
    {
        $this->to = null;
        $this->from = null;
        $this->reply_to = null;
        $this->cc = null;
        $this->bcc = null;
        $this->subject = null;
        $this->html_message = null;
        $this->text_message = null;
        $this->headers = [];

        return $this;
    }

    //--------------------------------------------------------------------
}
