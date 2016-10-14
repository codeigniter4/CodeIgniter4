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
    }

    //--------------------------------------------------------------------

    public function queue()
    {

    }

    //--------------------------------------------------------------------
}
