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
