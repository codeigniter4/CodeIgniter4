Upgrade Emails
##############

.. contents::
    :local:
    :depth: 2

Documentation
=============

- `Email Documentation CodeIgniter 3.x <http://codeigniter.com/userguide3/libraries/email.html>`_
- :doc:`Email Documentation CodeIgniter 4.x </libraries/email>`

What has been changed
=====================
- Only small things like the method names and the loading of the library have changed.
- The behavior when using the SMTP protocol has been slightly changed. You may not be able to communicate with your SMTP server properly if you use the CI3 settings. See :ref:`email-ssl-tls-for-smtp` and :ref:`email-preferences`.

Upgrade Guide
=============
1. Within your class, change ``$this->load->library('email');`` to ``$email = service('email');``.
2. From then on, you have to change ``$this->email`` to ``$email`` in every line starting with it.
3. The methods in the Email class are named slightly differently. All methods, except for ``send()``, ``attach()``, ``printDebugger()`` and ``clear()``, have ``set`` as a prefix followed by the previous method name. ``bcc()`` is now ``setBcc()`` and so on.
4. The config attributes in **app/Config/Email.php** have changed. You should have a look at the :ref:`setting-email-preferences` for a list of the new attributes.

Code Example
============

CodeIgniter Version 3.x
-----------------------

.. literalinclude:: upgrade_emails/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_emails/001.php
