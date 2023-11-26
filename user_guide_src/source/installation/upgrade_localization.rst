Upgrade Localization
####################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Language Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/language.html>`_
- :doc:`Localization Documentation CodeIgniter 4.X </outgoing/localization>`

What has been changed
=====================
- In CI4 the language files return the language lines as array.

Upgrade Guide
=============
1. Specify the default language in **Config/App.php**:

   .. literalinclude:: upgrade_localization/001.php

2. Now move your language files to **app/Language/<locale>**.
3. After that you have to change the syntax within the language files. Below in the Code Example you will see how the language array within the file should look like.
4. Remove from every file the language loader ``$this->lang->load($file, $lang);``.
5. Replace the method to load the language line ``$this->lang->line('error_email_missing')`` with ``echo lang('Errors.errorEmailMissing');``.

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_localization/ci3sample/002.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_localization/002.php
