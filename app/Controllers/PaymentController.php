<?php

namespace App\Controllers;

use App\Models\SimcardModel;
use App\Models\OrderModel;
use App\Libraries\ZarinpalGateway;

class PaymentController extends BaseController
{
    public function start()
    {
        $simcardId = $this->request->getPost('simcard_id');
        $simcardModel = new SimcardModel();
        $simcard = $simcardModel->find($simcardId);

        if (!$simcard || $simcard['status'] != 'free') {
            return redirect()->to('/')->with('error', 'سیم‌کارت نامعتبر یا فروخته شده است.');
        }

        // Validation
        if (!$this->validate([
            'buyer_name'          => 'required',
            'buyer_national_code' => 'required|exact_length[10]',
            'buyer_phone'         => 'required|exact_length[11]',
            'father_name'         => 'required',
            'birth_year'          => 'required',
            'birth_month'         => 'required',
            'birth_day'           => 'required',
            'rules'               => 'required'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $amount = $simcard['price']; // Rial
        $amountToman = $amount / 10;

        $trackingCode = $this->generateTrackingCode();
        $birthdate = $this->request->getPost('birth_year') . '-' .
                     $this->request->getPost('birth_month') . '-' .
                     $this->request->getPost('birth_day');

        // 1. Create Order
        $orderModel = new OrderModel();
        $orderId = $orderModel->insert([
            'tracking_code'       => $trackingCode,
            'simcard_id'          => $simcardId,
            'buyer_name'          => $this->request->getPost('buyer_name'),
            'buyer_national_code' => $this->request->getPost('buyer_national_code'),
            'buyer_phone'         => $this->request->getPost('buyer_phone'),
            'buyer_father_name'   => $this->request->getPost('father_name'),
            'buyer_birthdate'     => $birthdate,
            'amount'              => $amount,
            'payment_status'      => 'pending'
        ], true);

        // 2. Request Payment
        $zarinpal = new ZarinpalGateway();
        $description = "خرید سیم‌کارت " . $simcard['number'];
        $response = $zarinpal->request($amountToman, $description, $this->request->getPost('buyer_phone'));

        if ($response['status']) {
            // Update order with authority
            $orderModel->update($orderId, ['authority' => $response['authority']]);
            return redirect()->to($response['url']);
        } else {
            // Log error and show message
            return redirect()->back()->with('error', 'خطا در اتصال به درگاه پرداخت: ' . $response['message']);
        }
    }

    public function callback()
    {
        $authority = $this->request->getGet('Authority');
        $status = $this->request->getGet('Status');

        $orderModel = new OrderModel();
        $order = $orderModel->where('authority', $authority)->first();

        if (!$order) {
            return view('front/failed', ['message' => 'سفارش یافت نشد.']);
        }

        if ($status == 'OK') {
            $amountToman = $order['amount'] / 10;
            $zarinpal = new ZarinpalGateway();
            $verification = $zarinpal->verify($amountToman, $authority);

            if ($verification['status']) {
                // Success
                $orderModel->update($order['id'], [
                    'payment_status' => 'success',
                    'ref_id'         => $verification['ref_id']
                ]);

                // Update Simcard Status
                $simcardModel = new SimcardModel();
                $simcardModel->update($order['simcard_id'], ['status' => 'sold']);

                return view('front/success', ['order' => $order, 'ref_id' => $verification['ref_id']]);

            } else {
                // Verification Failed
                $orderModel->update($order['id'], ['payment_status' => 'failed']);
                return view('front/failed', ['message' => 'پرداخت تایید نشد.']);
            }
        } else {
            // User Canceled or Error
            $orderModel->update($order['id'], ['payment_status' => 'failed']);
            return view('front/failed', ['message' => 'عملیات پرداخت ناموفق بود یا توسط کاربر لغو شد.']);
        }
    }

    private function generateTrackingCode()
    {
        do {
            $code = rand(100000, 999999);
            $exists = (new OrderModel())->where('tracking_code', $code)->countAllResults();
        } while ($exists > 0);
        return (string)$code;
    }
}
