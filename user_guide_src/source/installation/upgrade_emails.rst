Upgrade Emails
##############

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Email Documentation CodeIgniter 3.x <http://codeigniter.com/userguide3/libraries/email.html>`_
- :doc:`Email Documentation CodeIgniter 4.x </libraries/email>`

What has been changed
=====================
- Only small things like the method names and the loading of the library have changed.
- The behavior when using the SMTP protocol has been slightly changed. You may not be able to communicate with your SMTP server properly if you use the CI3 settings. See :ref:`email-ssl-tls-for-smtp` and :ref:`email-preferences`.

Upgrade Guide
=============
1. Within your class change the ``$this->load->library('email');`` to ``$email = service('email');``.
2. From that on you have to replace every line starting with ``$this->email`` to ``$email``.
3. The methods in the Email class are named slightly different. All methods, except for ``send()``, ``attach()``, ``printDebugger()`` and ``clear()`` have a ``set`` as prefix followed by the previous method name. ``bcc()`` is now ``setBcc()`` and so on.
4. The config attributes in **app/Config/Email.php** have changed. You should have a look at the :ref:`setting-email-preferences` to have a list of the new attributes.

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_emails/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_emails/001.php
