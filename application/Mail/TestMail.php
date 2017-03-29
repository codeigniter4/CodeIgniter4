<?php namespace App\Mail;

use CodeIgniter\Mail\BaseMessage;

class TestMail extends BaseMessage
{
    public function build()
    {
        $this->setSubject("It's me, Margaret");

        $this->setHTMLMessage("<h1>Hello World</h1>");
        $this->setTextMessage("Hello World");
    }

}
