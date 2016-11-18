<?php namespace App\Messages;

use CodeIgniter\Mail\Message;
use CodeIgniter\Mail\MessageInterface;

class TestMail extends Message
{
    public function build()
    {
        $this->setSubject("It's me, Margaret");

//        $this->setHTMLBody("<h1>Hello World</h1>");
        $this->setTextBody("Hello World");
    }

}
