<?php namespace CodeIgniter\Mail;

class SimpleMessage extends Message
{
    public function build()
    {
        $this->HTMLContent = '<h1>Hello World</h1>';
        $this->textContent = 'Hello World';
    }

}
