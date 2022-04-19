Upgrade View Parser
###################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Template Parser Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/parser.html>`_
- :doc:`View Parser Documentation CodeIgniter 4.X </outgoing/view_parser>`

What has been changed
=====================
- You have to change the implementation and loading of the Parser Library.
- The Views can copied from CI3. Usually no changes there are required.

Upgrade Guide
=============
1. Wherever you use the View Parser Library replace ``$this->load->library('parser');`` with ``$parser = service('parser');``.
2. You have to change the render part in your controller from ``$this->parser->parse('blog_template', $data);`` to ``return $parser->setData($data)->render('blog_template');``.

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_view_parser/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_view_parser/001.php
