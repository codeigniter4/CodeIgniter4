<?php namespace App\Controllers;

use CodeIgniter\Controller;

class Migrate extends Controller {

    public function index() {
        $migrate = \Config\Services::migrations();

        try {
            $migrate->current();
        } catch (\Exception $e) {
            // Do something with the error here...
        }
    }

}
