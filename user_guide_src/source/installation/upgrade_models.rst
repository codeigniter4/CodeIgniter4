Upgrade Models
##############

.. contents::
    :local:
    :depth: 2

Documentations
==============

- `Model Documentation CodeIgniter 3.x <http://codeigniter.com/userguide3/general/models.html>`_
- :doc:`Model Documentation CodeIgniter 4.x </models/model>`

What has been changed
=====================

- The CI4 model has much more functionality, including automatic database connection, basic CRUD, in-model validation, and automatic pagination.
- Since namespaces has been added to CodeIgniter 4, the models must be changed to support namespaces.

Upgrade Guide
=============

1. First, move all model files to the folder **app/Models**.
2. Add this line just after the opening php tag: ``namespace App\Models;``.
3. Below the ``namespace App\Models;`` line add this line: ``use CodeIgniter\Model;``.
4. Replace ``extends CI_Model`` with ``extends Model``.
5. Add the ``protected $table`` property and set the table name.
6. Add the ``protected $allowedFields`` property and set the array of field names to allow to insert/update.
7. Instead of CI3's ``$this->load->model('x');``, you would now use ``$this->x = new X();``, following namespaced conventions for your component. Alternatively, you can use the :php:func:`model()` function: ``$this->x = model('X');``.

If you use sub-directories in your model structure you have to change the namespace according to that.
Example: You have a version 3 model located in **application/models/users/user_contact.php** the namespace has to be ``namespace App\Models\Users;`` and the model path in the version 4 should look like this: **app/Models/Users/UserContact.php**

The new Model in CI4 has a lot of built-in methods. For example, the ``find($id)`` method. With this you can find data where the primary key is equal to ``$id``.
Inserting data is also easier than before. In CI4 there is an ``insert($data)`` method. You can optionally make use of all those built-in methods and migrate your code to the new methods.

You can find more information to those methods in :doc:`../models/model`.

Code Example
============

CodeIgniter Version 3.x
------------------------

Path: **application/models**:

.. literalinclude:: upgrade_models/ci3sample/001.php

CodeIgniter Version 4.x
-----------------------

Path: **app/Models**:

.. literalinclude:: upgrade_models/001.php

The above code is direct translation from CI3 to CI4. It uses Query Builder
directly in the model. Note that when you use Query Builder directly, you cannot
use features in CodeIgniter's Model.

If you want to use CodeIgniter's Model features, the code will be:

.. literalinclude:: upgrade_models/002.php
