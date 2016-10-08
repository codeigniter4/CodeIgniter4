<?php namespace App\Mail;

use CodeIgniter\Mail\Message;
use CodeIgniter\Mail\MessageInterface;

class TestMail extends Message implements MessageInterface
{
    public function build()
    {
        $this->subject = "It's me, Margaret";

        $this->HTMLContent = "<h1>Hello World</h1>";
        $this->textContent = "Hello World";
    }

}
