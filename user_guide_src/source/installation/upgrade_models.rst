Upgrade Models
##############

.. contents::
    :local:
    :depth: 1

Documentations
==============

- `Model Documentation Codeigniter 3.X <http://codeigniter.com/userguide3/general/models.html>`_
- `Model Documentation Codeigniter 4.X </models/model.html>`_

What has been changed
=====================

- The CI4 model has much more functionality, including automatic database connection, basic CRUD, in-model validation, and automatic pagination.
- Since namespaces has been added to CodeIgniter 4, the models must be changed to support namespaces.

Upgrade Guide
=============

1. First, move all model files to the folder ``app/Models``.
2. Add this line just after the opening php tag: ``namespace App\Models;``.
3. Below the ``namespace App\Models;`` line add this line: ``use CodeIgniter\Model;``.
4. Replace ``extends CI_Model`` with ``extends Model``.
5. Instead of CI3’s ``$this->load->model(x);``, you would now use ``$this->x = new X();``, following namespaced conventions for your component. Alternatively, you can use the ``model`` function: ``$this->x = model('X');``.

If you use sub-directories in your model structure you have to change the namespace according to that.
Example: You have a version 3 model located in ``application/models/users/user_contact.php`` the namespace has to be ``namespace App\Models\Users;`` and the model path in the version 4 should look like this: ``app/Models/Users/UserContact.php``

The new Model in CI4 has a lot of built-in methods. For example, the ``find($id)`` method. With this you can find data where the primary key is equal to ``$id``.
Inserting data is also easier than before. In CI4 there is an ``insert($data)`` method. You can optionally make use of all those built-in methods and migrate your code to the new methods.

You can find more information to those methods `here </models/model.html>`_.

Code Example
============

Codeigniter Version 3.11
------------------------

Path: ``application/models``::

    <?php

    class User_contact extends CI_Model
    {
        public function insert($name, $address, $email)
        {
            $this->db->insert('user_contacts', array(
                "name" => $name,
                "address" => $address,
                "email" => $email,
            ));
        }
    }

Codeigniter Version 4.x
-----------------------

Path: ``app/Models``::

    <?php

    namespace App\Models;

    use CodeIgniter\Model;

    class UserContact extends Model
    {
        // insert() method already implemented in parent
    }

To insert data you can just directly call the ``$model->insert()`` method because this method is built-in since CI 4.
