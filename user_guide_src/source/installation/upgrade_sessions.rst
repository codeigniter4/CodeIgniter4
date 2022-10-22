Upgrade Sessions
################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Session Library Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/sessions.html>`_
- :doc:`Session Library Documentation CodeIgniter 4.X </libraries/sessions>`

What has been changed
=====================
- Only small things like the method names and the loading of the library have changed.

Upgrade Guide
=============
1. Wherever you use the Session Library replace ``$this->load->library('session');`` with ``$session = session();``.
2. From that on you have to replace every line starting with ``$this->session`` with ``$session`` followed by the new method name.

    - To access session data use the syntax ``$session->item`` or ``$session->get('item')`` instead of the CI3 syntax ``$this->session->name``.
    - To set data use ``$session->set($array);`` instead of ``$this->session->set_userdata($array);``.
    - To remove data use ``unset($_SESSION['some_name']);`` or ``$session->remove('some_name');`` instead of ``$this->session->unset_userdata('some_name');``.
    - To mark session data as flashdata, which will only be available for the next request, use ``$session->markAsFlashdata('item');`` instead of ``$this->session->mark_as_flash('item');```

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_sessions/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_sessions/001.php
