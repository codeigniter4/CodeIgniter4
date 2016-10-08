<?php namespace CodeIgniter\Mail;

interface MailHandlerInterface
{
    public function send();

    //--------------------------------------------------------------------

    public function queue();

    //--------------------------------------------------------------------

}
