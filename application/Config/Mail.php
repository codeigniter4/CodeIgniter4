<?php namespace Config;

class Mail
{
    /**
     * The handler that should be used to send mail with,
     * if nothing is specified in the message itself.
     *
     * @var string
     */
    public $handler = 'mail';

    /**
     * The name and email that emails will be sent from if no
     * other from has been specified.
     *
     * @var array
     */
    public $defaultFrom = ['name' => 'John Smith', 'address' => 'john.smith@example.com'];

    /**
     * Used as the User-Agent and X-Mailer header values.
     *
     * @var string
     */
    public $userAgent = 'CodeIgniter';

    /**
     * This array contains a list of all available Mail Handler
     * classes available within for the system to use, along
     * with an alias that each can be recognized by.
     *
     * @var array
     */
    public $availableHandlers = [
        'smtp'  => \CodeIgniter\Mail\Handlers\SMTPHandler::class,
        'mail'  => \CodeIgniter\Mail\Handlers\MailHandler::class
    ];
}
