Upgrade Image Manipulation Class
################################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Image Manipulation Class Documentation CodeIgniter 3.x <https://www.codeigniter.com/userguide3/libraries/image_lib.html>`_
- :doc:`Image Manipulation Class Documentation CodeIgniter 4.x <../libraries/images>`

What has been changed
=====================
- The preferences passed to the constructor or ``initialize()`` method in CI3
  have been changed to be specified in the new methods in CI4.
- Some preferences like ``create_thumb`` are removed.
- In CI4, the ``save()`` method must be called to save the manipulated image.
- The ``display_errors()`` has been removed, and an exception will be thrown
  if an error occurs.

Upgrade Guide
=============
1. Within your class change the ``$this->load->library('image_lib');`` to
   ``$image = \Config\Services::image();``.
2. Change the preferences passed to the constructor or ``initialize()`` method
   to be specified in the corresponding methods.
3. Call the ``save()`` method to save the file.

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_images/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_images/001.php
