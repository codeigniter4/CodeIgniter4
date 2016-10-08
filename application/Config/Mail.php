<?php namespace Config;

class Mail
{
    /**
     * The handler that should be used to send mail with,
     * if nothing is specified in the message itself.
     *
     * @var string
     */
    public $handler = 'smtp';

    /**
     * This array contains a list of all available Mail Handler
     * classes available within for the system to use, along
     * with an alias that each can be recognized by.
     *
     * @var array
     */
    public $availableHandlers = [
        'smtp'  => \CodeIgniter\Mail\Handlers\SMTPHandler::class,
    ];
}
