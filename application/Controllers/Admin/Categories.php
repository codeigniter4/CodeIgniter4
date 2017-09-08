<?php

namespace App\Controllers\Admin;

use App\Controllers;

class Categories extends Controllers\AdminController {

    protected $helpers = ['url', 'form', 'filesystem', 'html'];
    protected $session;
    protected $validation;
    protected $parser;
    protected $controllerPath = 'admin/categories';

    public function __construct(...$params) {
        parent::__construct(...$params);
        $this->session = \Config\Services::session();
        $this->session->start();
        $this->validation = \Config\Services::validation();

        $this->validation->setRules([
            //|is_unique[users.email]
            'name' => 'trim|required',
            'date' => 'required|min_length[8]'
                ], [
            'name' => [
                'required' => 'El campo email es requerido',
                'trim' => 'El formato del email no es correcto',
                'is_unique' => 'El email ya existe en base de datos'
            ],
            'date' => [
                'min_length' => 'Introduzca una fecha',
                'required' => 'Introduzca una fecha',
            ]
                ]
        );
    }

    public function index() {
        $model = new \CategoriesModel();

        $data = [
            'controllerPath' => $this->controllerPath,
            'categories' => $model->paginate(10),
            'total_rows' => $model->total_rows(),
            'pager' => $model->pager,
        ];
        return $this->template_output(view('categories/categories_list', $data));
    }

    public function parsear() {
        $db = \Config\Database::connect();
        $this->parser = \Config\Services::parser();
        $table_name = 'users';
        $fields = $db->getFieldNames($table_name);

        $fields_array = [];
        foreach ($fields as $field) {
            $fields_array[] = ['field' => $field];
        }

        $data = [
            'model' => ucfirst($table_name),
            'fields' => $fields_array,
        ];
        print_r($fields);
        if (!write_file('./abejita.php', $this->parser->setData($data)->render('users_list'))) {
            
        }
    }

    public function edit($id) {
        $model = new \CategoriesModel();
        $category = $model->find($id);

        if ($category) {
            $data = [
                'button' => 'Edit',
                'controllerPath' => $this->controllerPath,
                'action' => base_url($this->controllerPath . '/update_action'),
                'id' => $category['id'],
                'name' => $category['name'],
                'date' => $category['date'],
            ];
            return $this->template_output(view('categories/categories_form', $data));
        } else {
            $this->session->setFlashdata('message', 'Record Not Found');
            redirect(base_url($this->controllerPath));
        }
    }

    public function create() {
        $data = [
            'button' => 'Create',
            'controllerPath' => $this->controllerPath,
            'action' => base_url($this->controllerPath . '/create_action'),
            'id' => '',
            'name' => '',
            'date' => '',
        ];

        return $this->template_output(view('categories/categories_form', $data));
    }

    public function create_action() {
        $model = new \CategoriesModel();

        if ($this->validation->withRequest($this->request)->run() === FALSE) {
            $this->session->setFlashdata('errors', $this->validation->getErrors());
            return $this->create();
        } else {
            $data = [
                'name' => $this->request->getPost('name'),
                'date' => $this->request->getPost('date'),
            ];

            $model->insert($data);
            $this->session->setFlashdata('message', 'Create Record Success');
            redirect(base_url($this->controllerPath));
        }
    }

    public function update_action() {
        $id = $this->request->getPost('id');
        if ($this->validation->withRequest($this->request)->run() === FALSE) {
            $this->session->setFlashdata('errors', $this->validation->getErrors());
            return $this->edit($id);
        } else {
            $model = new \CategoriesModel();
            $category = $model->find($id);
            if ($category) {
                $category['name'] = $this->request->getPost('name');
                $category['date'] = $this->request->getPost('date');
                $model->update($id, $category);
                $this->session->setFlashdata('message', 'Update Record Success');
                redirect(base_url($this->controllerPath));
            }
        }
    }

    public function read($id) {
        $model = new \CategoriesModel();
        $category = $model->find($id);
        if ($category) {
            $data = ['controllerPath' => $this->controllerPath,
                'id' => $category['id'],
                'name' => $category['name'],
                'date' => $category['date'],
            ];
            return $this->template_output(view('categories/categories_read', $data));
        } else {
            $this->session->setFlashdata('message', 'Record Not Found');
            redirect(base_url($this->controllerPath));
        }
    }

    public function delete($id) {
        $model = new \CategoriesModel();
        $category = $model->find($id);

        if ($category) {
            $model->delete($id);
            $this->session->setFlashdata('message', 'Delete Record Success');
            redirect(base_url($this->controllerPath));
        } else {
            $this->session->setFlashdata('message', 'Record Not Found');
            redirect(base_url($this->controllerPath));
        }
    }

//
//    public function excel() {
//        $this->load->helper('exportexcel');
//        $namaFile = "categories.xls";
//        $judul = "categories";
//        $tablehead = 0;
//        $tablebody = 1;
//        $nourut = 1;
//        //penulisan header
//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
//        header("Content-Type: application/force-download");
//        header("Content-Type: application/octet-stream");
//        header("Content-Type: application/download");
//        header("Content-Disposition: attachment;filename=" . $namaFile . "");
//        header("Content-Transfer-Encoding: binary ");
//
//        xlsBOF();
//
//        $kolomhead = 0;
//        xlsWriteLabel($tablehead, $kolomhead++, "No");
//	xlsWriteLabel($tablehead, $kolomhead++, "Name");
//	xlsWriteLabel($tablehead, $kolomhead++, "Date");
//
//	foreach ($this->Categories_model->get_all() as $data) {
//            $kolombody = 0;
//
//            //ubah xlsWriteLabel menjadi xlsWriteNumber untuk kolom numeric
//            xlsWriteNumber($tablebody, $kolombody++, $nourut);
//	    xlsWriteLabel($tablebody, $kolombody++, $data->name);
//	    xlsWriteLabel($tablebody, $kolombody++, $data->date);
//
//	    $tablebody++;
//            $nourut++;
//        }
//
//        xlsEOF();
//        exit();
//    }
//
//    public function word() {
//        header("Content-type: application/vnd.ms-word");
//        header("Content-Disposition: attachment;Filename=categories.doc");
//
//        $data = array(
//            'categories_data' => $this->Categories_model->get_all(),
//            'start' => 0
//        );
//        
//        return view('categories/categories_doc',$data);
//    }
}

/* End of file Categories.php */
    /* Location: ./application/controllers/Categories.php */    