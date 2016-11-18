<?php namespace Tests\Support\Mail;

use CodeIgniter\Mail\BaseMessage;

class SimpleMessage extends BaseMessage
{
    public function build()
    {
        $this->messageHTML = '<h1>Hello World!</h1>';
        $this->messageText = 'Hello World!';

        $this->subject = 'Howdy';
    }
}
