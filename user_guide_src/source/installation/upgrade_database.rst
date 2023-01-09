Upgrade Database
################

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Database Reference Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/database/index.html>`_
- :doc:`Working with Databases Documentation CodeIgniter 4.X </database/index>`

What has been changed
=====================
- The functionality in CI3 is basically the same as in CI4.
- `Database Caching <https://www.codeigniter.com/userguide3/database/caching.html>`_ functionality known from CI3 was removed.
- The method names have changed to camelCase and the :doc:`Query Builder <../database/query_builder>`
  now needs to be initialized before you can run queries on it.

Upgrade Guide
=============
1. Add your database credentials to **app/Config/Database.php**. The options are pretty much the same as in CI3 only some names have changed slightly.
2. Everywhere you have used the database you have to replace ``$this->load->database();`` with ``$db = db_connect();``.
3. If you use multiple databases use the following code to load additional databases ``$db = db_connect('group_name');``.
4. Now you have to change all database queries. The most important change here is to replace ``$this->db`` with just ``$db`` and adjust the method name and ``$db``. Here are some examples:

    - ``$this->db->query('YOUR QUERY HERE');`` to ``$db->query('YOUR QUERY HERE');``
    - ``$this->db->simple_query('YOUR QUERY')`` to ``$db->simpleQuery('YOUR QUERY')``
    - ``$this->db->escape("something")`` to ``$db->escape("something");``
    - ``$this->db->affected_rows();`` to ``$db->affectedRows();``
    - ``$query->result();`` to ``$query->getResult();``
    - ``$query->result_array();`` to ``$query->getResultArray();``
    - ``echo $this->db->count_all('my_table');`` to ``echo $db->table('my_table')->countAll();``

5. To use the new Query Builder Class you have to initialize the builder ``$builder = $db->table('mytable');`` after that you can run the queries on the ``$builder``. Here are some examples:

    - ``$this->db->get()`` to ``$builder->get();``
    - ``$this->db->get_where('mytable', array('id' => $id), $limit, $offset);`` to ``$builder->getWhere(['id' => $id], $limit, $offset);``
    - ``$this->db->select('title, content, date');`` to ``$builder->select('title, content, date');``
    - ``$this->db->select_max('age');`` to ``$builder->selectMax('age');``
    - ``$this->db->join('comments', 'comments.id = blogs.id');`` to ``$builder->join('comments', 'comments.id = blogs.id');``
    - ``$this->db->having('user_id',  45);`` to ``$builder->having('user_id',  45);``
6. CI4 does not provide `Database Caching <https://www.codeigniter.com/userguide3/database/caching.html>`_
   layer known from CI3, so if you need to cache the result, use :doc:`../libraries/caching` instead.

Code Example
============

CodeIgniter Version 3.x
------------------------

.. literalinclude:: upgrade_database/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

.. literalinclude:: upgrade_database/001.php
