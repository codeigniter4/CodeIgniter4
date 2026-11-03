<?php

namespace App\Controllers;

use CodeIgniter\Controller;

abstract class BaseController extends Controller
{
    // ...

    protected $helpers = ['html', 'text'];

    // ...
}
