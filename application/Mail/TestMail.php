<?php namespace App\Mail;

use CodeIgniter\Mail\Message;
use CodeIgniter\Mail\MessageInterface;

class TestMail extends Message implements MessageInterface
{
    public function build()
    {
        $this->setSubject("It's me, Margaret");

        $this->setHTMLBody("<h1>Hello World</h1>");
        $this->setTextBody("Hello World");
    }

}
