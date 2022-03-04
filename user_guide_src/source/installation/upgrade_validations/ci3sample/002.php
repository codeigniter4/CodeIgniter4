<?php

class Form extends CI_Controller {

    public function index()
    {
        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');

        // Set validation rules

        if ($this->form_validation->run() == FALSE) {
                $this->load->view('myform');
        } else {
                $this->load->view('formsuccess');
        }
    }
}
