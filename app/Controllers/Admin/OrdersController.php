<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;

class OrdersController extends BaseController
{
    public function index()
    {
        $model = new OrderModel();

        $paymentStatus = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        // Apply Filters
        if ($paymentStatus && in_array($paymentStatus, ['pending', 'success', 'failed'])) {
            $model->where('payment_status', $paymentStatus);
        }

        if ($search) {
            $model->groupStart()
                ->like('tracking_code', $search)
                ->orLike('buyer_name', $search)
                ->orLike('buyer_national_code', $search)
                ->orLike('buyer_phone', $search)
                ->groupEnd();
        }

        // Use join to get simcard number (optional but good for display)
        $model->select('orders.*, simcards.number as simcard_number');
        $model->join('simcards', 'simcards.id = orders.simcard_id', 'left');

        $data = [
            'orders' => $model->orderBy('created_at', 'DESC')->paginate(20),
            'pager'  => $model->pager,
            'status' => $paymentStatus,
            'search' => $search
        ];

        return view('admin/orders/index', $data);
    }
}
