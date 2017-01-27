<?php namespace CodeIgniter\Mail\Handlers;

use CodeIgniter\Mail\BaseHandler;
use CodeIgniter\Mail\MailHandlerInterface;
use CodeIgniter\Mail\MessageInterface;

class LogHandler extends BaseHandler
{
    protected $logPath;

    //--------------------------------------------------------------------

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->logPath = $this->config['logPath'] ?? WRITEPATH;
    }

    /**
     * Does the actual delivery of a message. In this case, though, we simply
     * write the html and text files out to the log folder/emails.
     *
     * The filename format is: yyyymmddhhiiss_email.{format}
     *
     * @param \CodeIgniter\Mail\MessageInterface $message
     * @param bool                               $clear_after If TRUE, will reset the class after sending.
     *
     * @return mixed
     */
    public function send(MessageInterface $message, bool $clear_after=true)
    {
        // If there is more than one email address listed in $to,
        // only use the first one.
        $email = $message->getTo();
        if (is_array($email))
        {
            $email = array_values(array_shift($email))[0];
        }

        // Clean up the to address so we can use it as the filename
        $symbols = ['#', '%', '&', '{', '}', '\\', '/', '<', '>', '*', '?', ' ', '$', '!', '\'', '"', ':', '@', '+', '`', '='];
        $email = str_replace($symbols, '.', strtolower($email) );

        $filename = date('YmdHis_'). $email;

        // Ensure the emails folder exists in the log folder.
        $path = $this->logPath;
        $path = rtrim($path, '/ ') .'/email/';

        if (! is_dir($path))
        {
            mkdir($path, 0777, true);
        }

        helper('filesystem');

        $html = $message->getHTMLMessage();
        $text = $message->getTextMessage();

        // Write our HTML file out
        if (! empty($html) && ! write_file( $path . $filename . '.html', $html ) )
        {
            throw new \RuntimeException( sprintf( lang('mail.errorWritingFile'), $path, $filename) );
        }

        // Write our TEXT file out
        if (! empty($text) && ! write_file( $path . $filename . '.txt', $text ) )
        {
            throw new \RuntimeException( sprintf( lang('mail.errorWritingFile'), $path, $filename) );
        }

        return true;
    }

    //--------------------------------------------------------------------

}
