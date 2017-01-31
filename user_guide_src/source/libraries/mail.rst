#############
Email Library
#############

CodeIgniter's robust Email Class supports the following features:

-  Multiple Protocols: Mail, Sendmail, and SMTP
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
-  Third-party Handlers for local Logging, Amazon SES, Mailtrap, and more.

.. contents::
    :local:

***********************
Using the Email Library
***********************

In CodeIgniter, you create a simple message class that represents the email, including
subject, HTML and text messages, recipients, and more. Some of these items can be prepared
beforehand and built into the message, like the body template, who it's from, etc. Other items
must be set at run-time, including the recipients, any attachments, etc.

Building the Message
====================

The message class must extend ``CodeIgniter\Mail\BaseMessage``, and should have a ``build()`` method.
This method is where you can prepare your message by loading and parsing view files to construct the
HTML and Text bodies of the message, and more. These messages are typically saved to ``/application/Mail``,
but it's not a requirement, and they can be located anywhere the autoloader can find it.

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

There are a number of methods available for setting recipients, attachments, etc and they can all be used
from within the build() method.

Sending a Message
=================


