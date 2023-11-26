Upgrade Emails
##############

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Email Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/email.html>`_
- :doc:`Email Documentation CodeIgniter 4.X </libraries/email>`

What has been changed
=====================
- Only small things like the method names and the loading of the library have changed.

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
