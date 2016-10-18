<?php namespace CodeIgniter\Mail\Handlers;

use CodeIgniter\Mail\MailHandlerInterface;

/**
 * Class MailHandler
 *
 * @package CodeIgniter\Mail\Handlers
 */
class MailHandler extends BaseHandler implements MailHandlerInterface
{
    public function send()
    {
        $this->prepareSend();

        $recipients = $this->message->getHeaderLine('To');

        var_dump($recipients);
        var_dump($this->message->subject);
        var_dump($this->body);
        var_dump($this->headerString);
        var_dump($this->message->cleanEmail($this->message->getHeaderLine('Return-Path')));

        if (! mail(
            $recipients,
            $this->message->subject,
            $this->body,
            $this->headerString,
            '-f '.$this->message->cleanEmail($this->message->getHeaderLine('Return-Path')))
        )
        {
            die('false');
        }

        die('true');
    }

    //--------------------------------------------------------------------

    public function queue()
    {

    }

    //--------------------------------------------------------------------

    /**
     * Handles mail() specific adjustments to the headers.
     */
    protected function writeHeaders()
    {
        $this->subject = $this->message->getHeaderLine('Subject');

        $this->message->removeHeader('Subject');

        parent::writeHeaders();

//        $this->headerString = rtrim($this->headerString);
    }

    //--------------------------------------------------------------------

    /**
     * Builds and formats a plain text message.
     *
     * @return string
     */
    protected function buildPlainMessage(): string
    {
        $header = 'Content-Type: text/plain; charset='.$this->message->charset.$this->message->newline
                  .'Content-Transfer-Encoding: '. $this->getEncoding();

        $this->headerString .= $header;

        return $this->message->textContent;
    }

    //--------------------------------------------------------------------
}
