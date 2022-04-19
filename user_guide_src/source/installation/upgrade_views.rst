Upgrade Views
#############

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `View Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/general/views.html>`_
- :doc:`View Documentation CodeIgniter 4.X </outgoing/views>`

What has been changed
=====================

- Your views look much like before, but they are invoked differently ... instead of CI3's
  ``$this->load->view(x);``, you can use ``return view(x);``.
- CI4 supports *View Cells* to build your response in pieces, and *View Layouts* for page layout.
- The template parser is still there, and substantially enhanced.

Upgrade Guide
=============

1. First, move all views  to the folder **app/Views**
2. Change the loading syntax of views in every script where you load views:
    - from ``$this->load->view('directory_name/file_name')`` to ``return view('directory_name/file_name');``
    - from ``$content = $this->load->view('file', $data, TRUE);`` to ``$content = view('file', $data);``
3. (optional) You can change the echo syntax in views from ``<?php echo $title; ?>`` to ``<?= $title ?>``

Code Example
============

CodeIgniter Version 3.x
------------------------

Path: **application/views**:

.. literalinclude:: upgrade_views/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

Path: **app/Views**:

.. literalinclude:: upgrade_views/001.php
