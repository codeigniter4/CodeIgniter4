Upgrade Views
#############

.. contents::
    :local:
    :depth: 1

Documentations
==============

- `View Documentation Codeigniter 3.X <http://codeigniter.com/userguide3/general/views.html>`_
- `View Documentation Codeigniter 4.X </outgoing/views.html>`_

What has been changed
=====================

- Your views look much like before, but they are invoked differently … instead of CI3’s ``$this->load->view(x);``, you can use ``echo view(x);``.
- CI4 supports view “cells”, to build your response in pieces.
- The template parser is still there, and substantially enhanced.

Upgrade Guide
=============

1. First, move all views  to the folder ``app/Views``
2. Change the loading syntax of views in every script where you load views from ``$this->load->view('directory_name/file_name')`` to ``echo view('directory_name/file_name');``
3. (optional) You can change the echo syntax in views from ``<?php echo $title; ?>`` to ``<?= $title ?>``

Code Example
============

Codeigniter Version 3.11
------------------------

Path: ``application/views``::

    <html>
    <head>
        <title><?php echo $title; ?></title>
    </head>
    <body>
        <h1><?php echo $heading; ?></h1>

        <h3>My Todo List</h3>

        <ul>
        <?php foreach ($todo_list as $item): ?>
            <li><?php echo $item; ?></li>
        <?php endforeach; ?>
        </ul>

    </body>
    </html>

Codeigniter Version 4.x
-----------------------

Path: ``app/Views``::

    <html>
    <head>
        <title><?= esc($title) ?></title>
    </head>
    <body>
        <h1><?= esc($heading) ?></h1>

        <h3>My Todo List</h3>

        <ul>
        <?php foreach ($todo_list as $item): ?>
            <li><?= esc($item) ?></li>
        <?php endforeach; ?>
        </ul>

    </body>
    </html>
