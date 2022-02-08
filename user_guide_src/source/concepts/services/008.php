<?php

public static function renderer($viewPath = APPPATH . 'views/')
{
    return new \CodeIgniter\View\View($viewPath);
}
