Upgrade Controllers
###################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Controller Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/general/controllers.html>`_
- :doc:`Controller Documentation CodeIgniter 4.X </incoming/controllers>`

What has been changed
=====================

- Since namespaces have been added to CodeIgniter 4, the controllers must be changed to support namespaces.
- The constructor of CI4 Controller does not automatically load core classes into the properties.
- CI4's Controller has a special constructor :ref:`initController() <controller-constructor>`.
- CI4 provides :doc:`Request </incoming/incomingrequest>` and :doc:`Responses </outgoing/response>`
  objects for you to work with - more powerful than the CI3-way.
- If you want a base controller (``MY_Controller`` in CI3), use **app/Controllers/BaseController.php**.

Upgrade Guide
=============

1. First, move all controller files to the folder **app/Controllers**.
2. Add this line just after the opening php tag: ``namespace App\Controllers;``
3. Replace ``extends CI_Controller`` with ``extends BaseController``.
4. Remove the line ``defined('BASEPATH') OR exit('No direct script access allowed');`` if it exists.

| If you use sub-directories in your controller structure, you have to change the namespace according to that.
| For example, you have a version 3 controller located in **application/controllers/users/auth/Register.php**,
    the namespace has to be ``namespace App\Controllers\Users\Auth;`` and the controller path in the version 4
    should look like this: **app/Controllers/Users/Auth/Register.php**. Make sure to have the first letters of
    the sub-directories as capitalized.
| After that you have to insert a ``use`` statement below the namespace definition in order to extend the ``BaseController``:
    ``use App\Controllers\BaseController;``

Code Example
============

CodeIgniter Version 3.x
------------------------

Path: **application/controllers**:

.. literalinclude:: upgrade_controllers/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

Path: **app/Controllers**:

.. literalinclude:: upgrade_controllers/001.php
