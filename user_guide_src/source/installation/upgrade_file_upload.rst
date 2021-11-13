Upgrade Working with Uploaded Files
###################################

.. contents::
    :local:
    :depth: 1


Documentations
==============
- `Output Class Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/file_uploading.html>`_
- :doc:`Working with Uploaded Files Documentation CodeIgniter 4.X </libraries/uploaded_files>`

What has been changed
=====================
- The functionality of the file upload has changed a lot. You can now check if the file got uploaded without errors and moving / storing files is simpler.

Upgrade Guide
=============
In CI4 you access uploaded files with ``$file = $this->request->getFile('userfile')``. From there you can validate if the file got uploaded successfully with ``$file->isValid()``.
To store the file you could use ``$path = $this->request->getFile('userfile')->store('head_img/', 'user_name.jpg');`` This will store the file in ``writable/uploads/head_img/user_name.jpg``.

You have to change your file uploading code to match the new methods.

Code Example
============

CodeIgniter Version 3.11
------------------------
::

    <?php

    class Upload extends CI_Controller {

        public function __construct()
        {
            parent::__construct();
            $this->load->helper(array('form', 'url'));
        }

        public function index()
        {
            $this->load->view('upload_form', array('error' => ' ' ));
        }

        public function do_upload()
        {
            $config['upload_path']   = './uploads/';
            $config['allowed_types'] = 'png|jpg|gif';
            $config['max_size']      = 100;
            $config['max_width']     = 1024;
            $config['max_height']    = 768;

            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('userfile'))
            {
                $error = array('error' => $this->upload->display_errors());

                $this->load->view('upload_form', $error);
            }
            else
            {
                $data = array('upload_data' => $this->upload->data());

                $this->load->view('upload_success', $data);
            }
        }
    }

CodeIgniter Version 4.x
-----------------------
::

    <?php

    namespace App\Controllers;

    class Upload extends BaseController {

        public function index()
        {
            echo view('upload_form', ['error' => ' ']);
        }

        public function do_upload()
        {
            $this->validate([
                'userfile' => 'uploaded[userfile]|max_size[userfile,100]'
                               . '|mime_in[userfile,image/png,image/jpg,image/gif]'
                               . '|ext_in[userfile,png,jpg,gif]|max_dims[userfile,1024,768]'
            ]);

            $file = $this->request->getFile('userfile');

            if (! $path = $file->store()) {
                echo view('upload_form', ['error' => "upload failed"]);
            } else {
                $data = ['upload_file_path' => $path];

                echo view('upload_success', $data);
            }
        }
    }
