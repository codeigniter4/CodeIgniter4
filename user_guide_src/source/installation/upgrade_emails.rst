Upgrade Emails
##############

.. contents::
    :local:
    :depth: 1


Documentations
==============

- `Email Documentation Codeigniter 3.X <http://codeigniter.com/userguide3/libraries/email.html>`_
- :doc:`Email Documentation Codeigniter 4.X </libraries/email>`


What has been changed
=====================
- Only small things like the method names and the loading of the library have changed.

Upgrade Guide
=============
1. Within your class change the ``$this->load->library('email');`` to ``$email = service('email');``.
2. From that on you have to replace every line starting with ``$this->email`` to ``$email``.
3. The methods in the Email class are named slightly different. All methods, except for ``send()``, ``attach()``, ``printDebugger()`` and ``clear()`` have a ``set`` as prefix followed by the previous method name. ``bcc()`` is now ``setBcc()`` and so on.
4. The config attributes in ``app/Config/Email.php`` have changed. You should have a look at the `Email Class Documentation </libraries/email.html#setting-email-preferences>`__ to have a list of the new attributes.

Code Example
============

Codeigniter Version 3.11
------------------------
::

    $this->load->library('email');

    $this->email->from('your@example.com', 'Your Name');
    $this->email->to('someone@example.com');
    $this->email->cc('another@another-example.com');
    $this->email->bcc('them@their-example.com');

    $this->email->subject('Email Test');
    $this->email->message('Testing the email class.');

    $this->email->send();

Codeigniter Version 4.x
-----------------------
::

    $email = service('email');

    $email->setFrom('your@example.com', 'Your Name');
    $email->setTo('someone@example.com');
    $email->setCC('another@another-example.com');
    $email->setBCC('them@their-example.com');

    $email->setSubject('Email Test');
    $email->setMessage('Testing the email class.');

    $email->send();
