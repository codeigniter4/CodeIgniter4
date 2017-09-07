<?php

$string = "<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class " . $c . " extends Controller {
    function __construct(...\$params) {
        parent::__construct(...\$params);
        \$this->load->model('$m');
        \$this->load->library('form_validation');

        \$this->session = \Config\Services::session();
        \$this->validation = \Config\Services::validation();";


if ($jenis_tabel == 'datatables_server') {
    $string .= "        \n\t\$this->load->library('datatables');";
}
        
$string .= "
    }";

if (($jenis_tabel == 'regular_table') or ($jenis_tabel == 'datatables_local')) {
    
$string .= "\n\n    public function index() {
        \$$c_url = \$this->" . $m . "->get_all();

        \$data = array(
            '" . $c_url . "_data' => \$$c_url
        );
        \$this->template_output(\$this->load->view('$v_url/$v_list', \$data, TRUE));
    }";

} 

if ($jenis_tabel == 'datatables_server') {
    
$string .="\n\n    public function index() {
        \$this->template_output(\$this->load->view('$v_url/$v_list', NULL, TRUE));
    } 
    
    public function json() {
        header('Content-Type: application/json');
        echo \$this->" . $m . "->json();
    }";

}
    
$string .= "\n\n    public function view(\$id) {
        \$row = \$this->" . $m . "->get_by_id(\$id);
        if (\$row) {
            \$data = array(";
foreach ($all as $row) {
    $string .= "\n\t\t'" . $row['column_name'] . "' => \$row->" . $row['column_name'] . ",";
}
$string .= "\n\t    );
            \$this->template_output(\$this->load->view('$v_url/$v_read', \$data, TRUE));
        } else {
            \$this->session->setFlashdata('message', 'Record Not Found');
            redirect(site_url('$c_url'));
        }
    }

    public function create() {
        \$data = array(
            'button' => 'Create',
            'action' => site_url('$v_url/create_action'),";
foreach ($all as $row) {
    $string .= "\n\t    '" . $row['column_name'] . "' => set_value('" . $row['column_name'] . "'),";
}
$string .= "\n\t);
        \$this->template_output(\$this->load->view('$v_url/$v_form', \$data, TRUE));
    }
    
    public function create_action() 
    {
        \$this->_rules();

        if (\$this->validation->withRequest($this->request)->run() === FALSE) {
            \$this->create();
        } else {
            \$data = array(";
foreach ($non_pk as $row) {
    $string .= "\n\t\t'" . $row['column_name'] . "' => $this->request->getPost('" . $row['column_name'] . "),";
}
$string .= "\n\t    );

            \$this->".$m."->insert(\$data);
            \$this->session->setFlashdata('message', 'Create Record Success');
            redirect(site_url('$v_url'));
        }
    }
    
    public function edit(\$id) {
        \$row = \$this->".$m."->get_by_id(\$id);

        if (\$row) {
            \$data = array(
                'button' => 'Update',
                'action' => site_url('$v_url/update_action'),";
foreach ($all as $row) {
    $string .= "\n\t\t'" . $row['column_name'] . "' => set_value('" . $row['column_name'] . "', \$row->". $row['column_name']."),";
}
$string .= "\n\t    );
            \$this->template_output(\$this->load->view('$v_url/$v_form', \$data, TRUE));
        } else {
            \$this->session->setFlashdata('message', 'Record Not Found');
            redirect(site_url('$v_url'));
        }
    }
    
    public function update_action() {
        \$this->_rules();

        if (\$this->validation->withRequest($this->request)->run() === FALSE) {
            \$this->edit($this->request->getPost('$pk'));
        } else {
            \$data = array(";
foreach ($non_pk as $row) {
    $string .= "\n\t\t'" . $row['column_name'] . "' => $this->request->getPost('" . $row['column_name'] . "),";
}    
$string .= "\n\t    );

            \$this->".$m."->update($this->request->getPost('$pk'), \$data);
            \$this->session->setFlashdata('message', 'Update Record Success');
            redirect(site_url('$v_url'));
        }
    }
    
    public function delete(\$id) {
        \$row = \$this->".$m."->get_by_id(\$id);

        if (\$row) {
            \$this->".$m."->delete(\$id);
            \$this->session->setFlashdata('message', 'Delete Record Success');
            redirect(site_url('$v_url'));
        } else {
            \$this->session->setFlashdata('message', 'Record Not Found');
            redirect(site_url('$v_url'));
        }
    }

    public function _rules() {";
foreach ($non_pk as $row) {
    $int = $row3['data_type'] == 'int' || $row['data_type'] == 'double' || $row['data_type'] == 'decimal' ? '|numeric' : '';
    $string .= "\n\t\$this->form_validation->set_rules('".$row['column_name']."', '".  strtolower(label($row['column_name']))."', 'trim|required$int');";
}    
$string .= "\n\n\t\$this->form_validation->set_rules('$pk', '$pk', 'trim');";
$string .= "\n\t\$this->form_validation->set_error_delimiters('<span class=\"text-danger\">', '</span>');
    }";

if ($export_excel == '1') {
    $string .= "\n\n    public function excel() {
        \$this->load->helper('exportexcel');
        \$namaFile = \"$table_name.xls\";
        \$judul = \"$table_name\";
        \$tablehead = 0;
        \$tablebody = 1;
        \$nourut = 1;
        //penulisan header
        header(\"Pragma: public\");
        header(\"Expires: 0\");
        header(\"Cache-Control: must-revalidate, post-check=0,pre-check=0\");
        header(\"Content-Type: application/force-download\");
        header(\"Content-Type: application/octet-stream\");
        header(\"Content-Type: application/download\");
        header(\"Content-Disposition: attachment;filename=\" . \$namaFile . \"\");
        header(\"Content-Transfer-Encoding: binary \");

        xlsBOF();

        \$kolomhead = 0;
        xlsWriteLabel(\$tablehead, \$kolomhead++, \"No\");";
foreach ($non_pk as $row) {
        $column_name = label($row['column_name']);
        $string .= "\n\txlsWriteLabel(\$tablehead, \$kolomhead++, \"$column_name\");";
}
$string .= "\n\n\tforeach (\$this->" . $m . "->get_all() as \$data) {
            \$kolombody = 0;

            //ubah xlsWriteLabel menjadi xlsWriteNumber untuk kolom numeric
            xlsWriteNumber(\$tablebody, \$kolombody++, \$nourut);";
foreach ($non_pk as $row) {
        $column_name = $row['column_name'];
        $xlsWrite = $row['data_type'] == 'int' || $row['data_type'] == 'double' || $row['data_type'] == 'decimal' ? 'xlsWriteNumber' : 'xlsWriteLabel';
        $string .= "\n\t    " . $xlsWrite . "(\$tablebody, \$kolombody++, \$data->$column_name);";
}
$string .= "\n\n\t    \$tablebody++;
            \$nourut++;
        }

        xlsEOF();
        exit();
    }";
}

if ($export_word == '1') {
    $string .= "\n\n    public function word() {
        header(\"Content-type: application/vnd.ms-word\");
        header(\"Content-Disposition: attachment;Filename=$table_name.doc\");

        \$data = array(
            '" . $table_name . "_data' => \$this->" . $m . "->get_all(),
            'start' => 0
        );
        
        \$this->load->view('" . $v_url ."/". $v_doc . "',\$data);
    }";
}

if ($export_pdf == '1') {
    $string .= "\n\n    function pdf() {
        \$data = array(
            '" . $table_name . "_data' => \$this->" . $m . "->get_all(),
            'start' => 0
        );
        
        ini_set('memory_limit', '32M');
        \$html = \$this->load->view('" . $c_url ."/". $v_pdf . "', \$data, true);
        \$this->load->library('pdf');
        \$pdf = \$this->pdf->load();
        \$pdf->WriteHTML(\$html);
        \$pdf->Output('" . $table_name . ".pdf', 'D'); 
    }";
}

$string .= "\n\n}\n\n/* End of file $c_file */
/* Location: ./application/controllers/$_file */";

$hasil_controller = createFile($string, $target . "controllers/" . $c_file);
