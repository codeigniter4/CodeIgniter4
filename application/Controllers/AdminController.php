<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class AdminController extends Controller {

    public $data;

    public function __construct(...$params) {
        parent::__construct(...$params);

//        $this->data['general'] = $this->general_model->get_data();
//        $this->data['categories'] = $this->category_model->get_data();

//        if (empty($this->session->userData['admin_user_id']) && !in_array('login', $this->uri->segment_array())) {
//            redirect('admin/users/login');
//        }
    }

    function template_output($content) {
        // Load the base template with output content available as $content
        $data['content'] = &$content;
        return view('base_dash', $data);
    }

}
