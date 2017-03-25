#############
Email Library
#############

CodeIgniter's robust Email Class supports the following features:

-  Multiple Protocols: Mail, Sendmail, and SMTP
-  Support for third-party handlers for local Logging, Amazon SES, Mailtrap, etc.
-  TLS and SSL Encryption for SMTP
-  Multiple recipients
-  CC and BCCs
-  HTML or Plaintext email
-  Attachments
-  Word wrapping
-  Priorities
-  BCC Batch Mode, enabling large email lists to be broken into small
   BCC batches.
-  Email Debugging tools

.. contents::
    :local:

***********************
Using the Email Library
***********************

In CodeIgniter, messages are each represented by a simple class file. Theses classes are typically stored
within the **app/Mail** directory.

Building the Message
====================

The message class must extend ``CodeIgniter\Mail\BaseMessage``, and should have a ``build()`` method.
This method is where you can prepare your message by loading and parsing view files to construct the
HTML and Text bodies of the message, and more.

::

    <?php namespace App\Mail;

    class UserWelcomeMessage extends CodeIgniter\Mail\BaseMessage
    {
        public function build()
        {
            $this->setSubject(lang('users.welcomeEmailTitle'));

            $this->setHTMLMessage(view('Emails/UserWelcomeHTML', $this->data));
            $this->setTextMessage(view('Emails/UserWelcomeText', $this->data));
        }
    }

This is all that's really needed to setup an email message and get it ready to use. In this example,
we're setting the subject here through a language string. Next we set the HTML and text bodies for the
message. We expect the message to be personalized each time it's sent, so we use views to hold the body templates
themselves. The class has a ``$data`` variable that can be used to store key/value pairs that we use within
the views for dynamic data replacement.

There are a number of methods available for setting recipients, attachments, and more, and they can all be used
from within the build() method.

Sending a Message
=================

You send a message using the ``email()`` helper function. It takes a Message class as it's only parameter,
and returns that message class, already set up with an instantiated handler, and ready to go. You can then
set any additional parameters you need, like who it's going to, who it's from, add attachments, etc.::

    email(new App\Mail\UserWelcomeMessage())
        ->setTo($user->email, $user->firstName)
        ->send();

Debugging A Message
===================

If you are having problems trying to get your emails to send, you can print out some debugging information
that will display any errors encountered and some basic information about the sending process that might
prove helpful while trying to determine the problem::

    $mail = email(new App\Mail\UserWelcomeMessage())
                ->setTo($user->email, $user->firstName)
                ->send();

    if ($mail->hasErrors())
    {
        die($mail->getDebugger());
    }


Setting Email Preferences
=========================

There are a number of preferences available to tailor how your email messages are sent. These are set
in **application/Config/Mail.php**.

**default from**

The default value that will be used when sending any email can be setup in the ``from`` setting::

    public $from = [
        'name' => 'CodeIgniter',
        'email' => 'codeigniter@example.com'
    ];

**userAgent**

This sets the user agent, or software, that's sending the mails::

    public $userAgent = 'CodeIgniter';

**others**

The remaining settings can all be set specific to each connection group. Most of them have default values
that will be used if nothing has been set, and some are only required for specific types of connections.

=================== ====================== ============================ =======================================================================
Preference          Default Value          Options                      Description
=================== ====================== ============================ =======================================================================
**protocol**        mail                   mail, sendmail, or smtp      The mail sending protocol.
**mailpath**        /usr/sbin/sendmail     None                         The server path to Sendmail.
**SMTPHost**        No Default             None                         SMTP Server Address.
**SMTPUser**        No Default             None                         SMTP Username.
**SMTPPass**        No Default             None                         SMTP Password.
**SMTPPort**        25                     None                         SMTP Port.
**SMTPTimeout**     5                      None                         SMTP Timeout (in seconds).
**SMTPKeepalive**   FALSE                  TRUE or FALSE (boolean)      Enable persistent SMTP connections.
**SMTPCrypto**      No Default             tls or ssl                   SMTP Encryption
**wordwrap**        TRUE                   TRUE or FALSE (boolean)      Enable word-wrap.
**wrapchars**       76                                                  Character count to wrap at.
**charset**         ``$config['charset']``                              Character set (utf-8, iso-8859-1, etc.).
**validate**        TRUE                   TRUE or FALSE (boolean)      Whether to validate the email address.
**priority**        3                      1, 2, 3, 4, 5                Email Priority. 1 = highest. 5 = lowest. 3 = normal.
**crlf**            \\n                    "\\r\\n" or "\\n" or "\\r"   Newline character. (Use "\\r\\n" to comply with RFC 822).
**newline**         \\n                    "\\r\\n" or "\\n" or "\\r"   Newline character. (Use "\\r\\n" to comply with RFC 822).
**bcc_batch_mode**  FALSE                  TRUE or FALSE (boolean)      Enable BCC Batch Mode.
**bcc_batch_size**  200                    None                         Number of emails in each BCC batch.
**DSN**             FALSE                  TRUE or FALSE (boolean)      Enable notify message from server
=================== ====================== ============================ =======================================================================

Note that mail type is not required to be set. It is determined automatically based on whether the HTML or Text bodies have been set on the message.

Settings Groups
---------------

You can setup multiple groups of settings that can be used at any time. This can be useful if you want to send transactional emails
through a third-party service like Postmark or Mandrill, but need to send non-transactional emails over a local SMTP connection.
Groups are defined in the ``$groups`` setting. For each, you need to supply an alias to refer to the group by, and
any settings that are needed for that connection::

    public $groups = [
        'default' => [
            'handler'  => 'default',
            'protocol' => 'mail',
        ],
        'mailtrap' => [
            'handler' => 'default',
            'protocol' => 'smtp',
            'SMTPHost' => 'smtp.mailtrap.io',
            'SMTPUser' => 'xxx',
            'SMTPPass' => 'xxx',
            'SMTPPort' => 2525,
            'SMTPCrypto' => 'tls'
        ]
    ];

In this example we have two groups, **default** and **mailtrap**. We'll use the mailtrap connection on our development
server so that we can test sending mail without bothering anyone with real emails. Both groups use the **default**
mail handler, which can work with *mail*, *sendmail*, or *smtp* to send the message. Each group contains only the
settings it needs that varies from the defaults.

Choosing Group at Runtime
-------------------------

You can choose which group is used at runtime by passing the group's alias as the second parameter to the **email()** helper::

    email(new App\Mail\UserWelcomeMessage(), 'default')
        ->setTo($user->email, $user->firstName)
        ->send();

Provided Handlers
-----------------

CodeIgniter ships with the following handlers that you can use in your settings groups.

=========== ===========================================================================
default     Supports mail, sendmail, and smtp connections.
logger      Simply stores copies of the HTML and Text emails locally, in
            **writable/email**.
=========== ===========================================================================



Overriding Word Wrapping
========================

If you have word wrapping enabled (recommended to comply with RFC 822)
and you have a very long link in your email it can get wrapped too,
causing it to become un-clickable by the person receiving it.
CodeIgniter lets you manually override word wrapping within part of your
message like this::

	The text of your email that
	gets wrapped normally.

	{unwrap}http://example.com/a_long_link_that_should_not_be_wrapped.html{/unwrap}

	More text that will be
	wrapped normally.


Place the item you do not want word-wrapped between: {unwrap} {/unwrap}

