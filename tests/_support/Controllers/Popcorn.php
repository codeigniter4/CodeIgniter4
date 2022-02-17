<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use RuntimeException;

/**
 * This is a testing only controller, intended to blow up in multiple
 * ways to make sure we catch them.
 */
class Popcorn extends Controller
{
    use ResponseTrait;

    public function index()
    {
        return 'Hi there';
    }

    public function pop()
    {
        return $this->respond('Oops', 567, 'Surprise');
    }

    public function popper()
    {
        throw new RuntimeException('Surprise', 500);
    }

    public function weasel()
    {
        return $this->respond('', 200);
    }

    public function oops()
    {
        return $this->failUnauthorized();
    }

    public function goaway()
    {
        return redirect()->to('/');
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1834
     */
    public function index3()
    {
        return $this->response->setJSON(['lang' => $this->request->getLocale()]);
    }

    public function canyon()
    {
        echo 'Hello-o-o ' . $this->request->getGet('foo');
    }

    public function cat()
    {
    }

    public function json()
    {
        return $this->respond(['answer' => 42]);
    }

    public function xml()
    {
        return $this->respond('<my><pet>cat</pet></my>');
    }

    public function toindex()
    {
        return redirect()->route('testing-index');
    }

    public function echoJson()
    {
        return $this->response->setJSON($this->request->getJSON());
    }
}
