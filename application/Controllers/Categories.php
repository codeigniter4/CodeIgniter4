<?php

namespace App\Controllers;

use App\Controllers;

class Categories extends AdminController {

    protected $helpers = ['url', 'form', 'filesystem', 'html'];
    protected $session;
    protected $validation;
    protected $parser;

    public function __construct(...$params) {
        parent::__construct(...$params);
        $this->session = \Config\Services::session();
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
            'categories' => $model->paginate(10),
            'total_rows' => $model->total_rows(),
            'pager' => $model->pager
        ];
        return $this->template_output(view('categories/categories_list', $data));
    }

    public function parsear() {
        $db = \Config\Database::connect();
        $this->parser = \Config\Services::parser();
        $table_name = 'users';
        $fields = $db->getFieldNames($table_name);

        $fields_array = array();
        foreach ($fields as $field) {
            $fields_array[] = array('field' => $field);
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
            $data = array(
                'button' => 'Edit',
                'action' => site_url('categories/update_action'),
                'id' => set_value('id', $category['id']),
                'name' => set_value('name', $category['name']),
                'date' => set_value('date', $category['date']),
            );
            return $this->template_output(view('categories/categories_form', $data));
        } else {
            $this->session->setFlashdata('message', 'Record Not Found');
            redirect(site_url('categories'));
        }
    }

    public function create() {
        $data = array(
            'button' => 'Create',
            'action' => site_url('categories/create_action'),
            'id' => set_value('id'),
            'name' => set_value('name'),
            'date' => set_value('date'),
        );
        return $this->template_output(view('categories/categories_form', $data));
    }

    public function create_action() {
        $model = new \CategoriesModel();

        if ($this->validation->withRequest($this->request)->run() === FALSE) {
            $errors = $this->validation->getErrors();
            print_r($errors);
            $this->create();
        } else {
            $data = array(
                'name' => $this->request->getPost('name'),
                'date' => $this->request->getPost('date'),
            );

            $model->insert($data);
            $this->session->setFlashdata('message', 'Create Record Success');
            redirect(site_url('categories'));
        }
    }

    public function update_action() {
        $id = $this->request->getPost('id');
        if ($this->validation->withRequest($this->request)->run() === FALSE) {
            $this->edit($id);
        } else {
            $model = new \CategoriesModel();
            $category = $model->find($id);
            if ($category) {
                $category['name'] = $this->request->getPost('name');
                $category['date'] = $this->request->getPost('date');
                $model->update($id, $category);
                $this->session->setFlashdata('message', 'Update Record Success');
                redirect(site_url('categories'));
            }
        }
    }

    public function read($id) {
        $model = new \CategoriesModel();
        $category = $model->find($id);
        if ($category) {
            return $this->template_output(view('categories/categories_read', $category));            
        } else {
            $this->session->setFlashdata('message', 'Record Not Found');
            redirect(site_url('categories'));
        }
    }

    public function delete($id) {
        $model = new \CategoriesModel();
        $category = $model->find($id);

        if ($category) {
            $model->delete($id);
            $this->session->setFlashdata('message', 'Delete Record Success');
            redirect(site_url('categories'));
        } else {
            $this->session->setFlashdata('message', 'Record Not Found');
            redirect(site_url('categories'));
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