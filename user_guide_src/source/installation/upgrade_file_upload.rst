Upgrade Working with Uploaded Files
###################################

.. contents::
    :local:
    :depth: 2

Documentations
==============
- `Output Class Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/file_uploading.html>`_
- :doc:`Working with Uploaded Files Documentation CodeIgniter 4.X </libraries/uploaded_files>`

What has been changed
=====================
- The functionality of the file upload has changed a lot. You can now check if the file got uploaded without errors and moving / storing files is simpler.

Upgrade Guide
=============
In CI4 you access uploaded files with ``$file = $this->request->getFile('userfile')``. From there you can validate if the file got uploaded successfully with ``$file->isValid()``.
To store the file you could use ``$path = $this->request->getFile('userfile')->store('head_img/', 'user_name.jpg');``. This will store the file in **writable/uploads/head_img/user_name.jpg**.

You have to change your file uploading code to match the new methods.

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_file_upload/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_file_upload/001.php
