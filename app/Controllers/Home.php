<?php

namespace App\Controllers;

use App\Models\SimcardModel;

class Home extends BaseController
{
    public function index()
    {
        return view('front/home');
    }

    public function ajaxSearchNumber()
    {
        $mobile = $this->request->getGet('mobile');

        if (!$mobile) {
            return $this->response->setJSON(['found' => false]);
        }

        $model = new SimcardModel();
        // Check if exists and is free
        $simcard = $model->where('number', $mobile)
                         ->where('status', 'free')
                         ->first();

        if ($simcard) {
            return $this->response->setJSON(['found' => true]);
        } else {
            return $this->response->setJSON(['found' => false]);
        }
    }

    public function order($number)
    {
        $model = new SimcardModel();
        $simcard = $model->where('number', $number)
                         ->where('status', 'free')
                         ->first();

        if (!$simcard) {
            return redirect()->to('/');
        }

        return view('front/order', ['simcard' => $simcard]);
    }
}
