Upgrade HTML Tables
###################

.. contents::
    :local:
    :depth: 1


Documentations
==============

- `HTML Table Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/table.html>`_
- :doc:`HTML Table Documentation CodeIgniter 4.X </outgoing/table>`


What has been changed
=====================
- Only small things like the method names and the loading of the library have changed.

Upgrade Guide
=============
1. Within your class change the ``$this->load->library('table');`` to ``$table = new \CodeIgniter\View\Table();``.
2. From that on you have to replace every line starting with ``$this->table`` to ``$table``. For example: ``echo $this->table->generate($query);`` will become ``echo $table->generate($query);``
3. The methods in the HTML Table class could be named slightly different. The most important change in the naming is the switch from underscored method names to camelCase. The method ``set_heading()`` from version 3 is now named ``setHeading()`` and so on.

Code Example
============

CodeIgniter Version 3.11
------------------------
::

    $this->load->library('table');

    $this->table->set_heading('Name', 'Color', 'Size');

    $this->table->add_row('Fred', 'Blue', 'Small');
    $this->table->add_row('Mary', 'Red', 'Large');
    $this->table->add_row('John', 'Green', 'Medium');

    echo $this->table->generate();

CodeIgniter Version 4.x
-----------------------
::

    $table = new \CodeIgniter\View\Table();

    $table->setHeading('Name', 'Color', 'Size');

    $table->addRow('Fred', 'Blue', 'Small');
    $table->addRow('Mary', 'Red', 'Large');
    $table->addRow('John', 'Green', 'Medium');

    echo $table->generate();
