Upgrade Validations
###################

.. contents::
    :local:
    :depth: 2

Documentations of Library
=========================

- `Form Validation Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/form_validation.html>`_
- :doc:`Validation Documentation CodeIgniter 4.X </libraries/validation>`

What has been changed
=====================
- If you want to change validation error display, you have to set CI4 :ref:`validation View templates <validation-customizing-error-display>`.
- CI4 validation has no Callbacks nor Callable in CI3.
  Use :ref:`Rule Classes <validation-using-rule-classes>` or
  :ref:`Closure Rule <validation-using-closure-rule>`
  instead.
- In CI3, Callbacks/Callable rules were prioritized, but in CI4, Closure Rules are
  not prioritized, and are checked in the order in which they are listed.
- CI4 validation format rules do not permit empty string.
- CI4 validation never changes your data.
- Since v4.3.0, :php:func:`validation_errors()` has been introduced, but the API is different from CI3's.

Upgrade Guide
=============
1. Within the view which contains the form you have to change:

    - ``<?php echo validation_errors(); ?>`` to ``<?= validation_list_errors() ?>``

2. Within the controller you have to change the following:

    - ``$this->load->helper(array('form', 'url'));`` to ``helper(['form', 'url']);``
    - remove the line ``$this->load->library('form_validation');``
    - ``if ($this->form_validation->run() == FALSE)`` to ``if (! $this->validate([]))``
    - ``$this->load->view('myform');`` to ``return view('myform', ['validation' => $this->validator,]);``

3. You have to change the validation rules. The new syntax is to set the rules as array in the controller:

   .. literalinclude:: upgrade_validations/001.php

Code Example
============

CodeIgniter Version 3.x
------------------------
Path: **application/views**::

    <html>
    <head>
        <title>My Form</title>
    </head>
    <body>

        <?php echo validation_errors(); ?>

        <?php echo form_open('form'); ?>

        <h5>Username</h5>
        <input type="text" name="username" value="" size="50" />

        <h5>Password</h5>
        <input type="text" name="password" value="" size="50" />

        <h5>Password Confirm</h5>
        <input type="text" name="passconf" value="" size="50" />

        <h5>Email Address</h5>
        <input type="text" name="email" value="" size="50" />

        <div><input type="submit" value="Submit" /></div>

        </form>

    </body>
    </html>

Path: **application/controllers**:

.. literalinclude:: upgrade_validations/ci3sample/002.php

CodeIgniter Version 4.x
-----------------------
Path: **app/Views**::

    <html>
    <head>
        <title>My Form</title>
    </head>
    <body>

        <?= validation_list_errors() ?>

        <?= form_open('form') ?>

        <h5>Username</h5>
        <input type="text" name="username" value="" size="50" />

        <h5>Password</h5>
        <input type="text" name="password" value="" size="50" />

        <h5>Password Confirm</h5>
        <input type="text" name="passconf" value="" size="50" />

        <h5>Email Address</h5>
        <input type="text" name="email" value="" size="50" />

        <div><input type="submit" value="Submit" /></div>

        </form>

    </body>
    </html>

Path: **app/Controllers**:

.. literalinclude:: upgrade_validations/002.php
