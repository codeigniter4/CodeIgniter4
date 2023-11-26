Upgrade Security
################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Security Class Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/security.html>`_
- :doc:`Security Documentation CodeIgniter 4.X </libraries/security>`

.. note::
    If you use the :doc:`../helpers/form_helper` and enable the CSRF filter globally, then :php:func:`form_open()` will automatically insert a hidden CSRF field in your forms. So you do not have to upgrade this by yourself.

What has been changed
=====================
- The method to implement CSRF tokens to HTML forms has been changed.

Upgrade Guide
=============
1. To enable CSRF protection in CI4 you have to enable it in **app/Config/Filters.php**:

   .. literalinclude:: upgrade_security/001.php

2. Within your HTML forms you have to remove the CSRF input field which looks similar to ``<input type="hidden" name="<?= $csrf['name'] ?>" value="<?= $csrf['hash'] ?>" />``.
3. Now, within your HTML forms you have to add ``<?= csrf_field() ?>`` somewhere in the form body, unless you are using ``form_open()``.

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_security/ci3sample/002.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_security/002.php
