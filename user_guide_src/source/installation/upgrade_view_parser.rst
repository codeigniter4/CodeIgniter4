Upgrade View Parser
###################

.. contents::
    :local:
    :depth: 1


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
2. You have to change the render part in your controller from ``$this->parser->parse('blog_template', $data);`` to ``echo $parser->setData($data)->render('blog_template');``.

Code Example
============

CodeIgniter Version 3.11
------------------------
::

    $this->load->library('parser');

    $data = array(
        'blog_title' => 'My Blog Title',
        'blog_heading' => 'My Blog Heading'
    );

    $this->parser
        ->parse('blog_template', $data);

CodeIgniter Version 4.x
-----------------------
::

    $parser = service('parser');

    $data = [
        'blog_title'   => 'My Blog Title',
        'blog_heading' => 'My Blog Heading'
    ];

    echo $parser->setData($data)
        ->render('blog_template');
