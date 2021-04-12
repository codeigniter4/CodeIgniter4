Upgrade Pagination
##################

.. contents::
    :local:
    :depth: 1


Documentations
==============

- `Pagination Class Documentation Codeigniter 3.X <http://codeigniter.com/userguide3/libraries/pagination.html>`_
- `Pagination Documentation Codeigniter 4.X <http://codeigniter.com/user_guide/libraries/pagination.html>`_


What has been changed
=====================
- In CI4 the pagination is a built-in method in the Model class. You have to change the views and also the controller in order to use the new pagination library.

Upgrade Guide
=============
1. Within the views change to following:

- ``<?php echo $this->pagination->create_links(); ?>`` to ``<?= $pager->links() ?>``

2. Within the controller you have to make the following changes:

- You can use the built-in ``paginate()`` method on every Model. Have a look at the code example below to see how you setup the pagination on a specific model.


Code Example
============

Codeigniter Version 3.11
------------------------
::

    $this->load->library('pagination');
    $config['base_url'] = base_url().'users/index/';
    $config['total_rows'] = $this->db->count_all('users');
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;
    $config['attributes'] = array('class' => 'pagination-link');
    $this->pagination->initialize($config);

    $data['users'] = $this->user_model->get_users(FALSE, $config['per_page'], $offset);

    $this->load->view('posts/index', $data);

Codeigniter Version 4.x
-----------------------
::

    $model = new \App\Models\UserModel();

    $data = [
        'users' => $model->paginate(10),
        'pager' => $model->pager,
    ];

    echo view('users/index', $data);

