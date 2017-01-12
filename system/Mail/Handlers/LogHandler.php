<?php namespace CodeIgniter\Mail\Handlers;

use CodeIgniter\Mail\BaseHandler;
use CodeIgniter\Mail\MailHandlerInterface;

class LogHandler extends BaseHandler
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

        helper('filesystem');

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

}
