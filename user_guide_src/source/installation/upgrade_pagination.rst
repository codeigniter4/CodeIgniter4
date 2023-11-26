Upgrade Pagination
##################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Pagination Class Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/pagination.html>`_
- :doc:`Pagination Documentation CodeIgniter 4.X </libraries/pagination>`

What has been changed
=====================
- You have to change the views and also the controller in order to use the new pagination library.
- If you want to customize the pagination links, you need to create View Templates.
- In CI4 the pagination uses the actual page number only. You can't use the starting index (offset) for the items which is the default in CI3.
- If you use :doc:`CodeIgnite\\Model </models/model>`, you can use the built-in method in the Model class.

Upgrade Guide
=============
1. Within the views change to following:

    - ``<?php echo $this->pagination->create_links(); ?>`` to ``<?= $pager->links() ?>``

2. Within the controller you have to make the following changes:

    - You can use the built-in ``paginate()`` method on every Model. Have a look at the code example below to see how you setup the pagination on a specific model.

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_pagination/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_pagination/001.php
