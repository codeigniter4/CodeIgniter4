<?php

namespace App\Libraries;

class ZarinpalGateway
{
    private $merchantId;
    private $sandbox;
    private $callbackUrl;

    public function __construct()
    {
        $this->merchantId = getenv('ZARINPAL_MERCHANT');
        $this->sandbox = getenv('ZARINPAL_SANDBOX') === 'true';
        $this->callbackUrl = base_url('payment/callback');
    }

    public function request($amount, $description, $mobile = '', $email = '')
    {
        $data = [
            'merchant_id'  => $this->merchantId,
            'amount'       => $amount, // Toman
            'callback_url' => $this->callbackUrl,
            'description'  => $description,
            'metadata'     => [
                'mobile' => $mobile,
                'email'  => $email,
            ],
        ];

        $url = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
            : 'https://api.zarinpal.com/pg/v4/payment/request.json';

        $response = $this->sendRequest($url, $data);

        if (isset($response['data']['code']) && $response['data']['code'] == 100) {
            $authority = $response['data']['authority'];
            $paymentUrl = $this->sandbox
                ? "https://sandbox.zarinpal.com/pg/StartPay/$authority"
                : "https://www.zarinpal.com/pg/StartPay/$authority";

            return [
                'status'    => true,
                'authority' => $authority,
                'url'       => $paymentUrl
            ];
        }

        return [
            'status'  => false,
            'message' => 'Error: ' . ($response['errors']['message'] ?? 'Unknown error')
        ];
    }

    public function verify($amount, $authority)
    {
        $data = [
            'merchant_id' => $this->merchantId,
            'amount'      => $amount,
            'authority'   => $authority,
        ];

        $url = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
            : 'https://api.zarinpal.com/pg/v4/payment/verify.json';

        $response = $this->sendRequest($url, $data);

        if (isset($response['data']['code']) && $response['data']['code'] == 100) {
            return [
                'status' => true,
                'ref_id' => $response['data']['ref_id']
            ];
        }

        return [
            'status'  => false,
            'message' => 'Error: ' . ($response['errors']['message'] ?? 'Unknown error')
        ];
    }

    private function sendRequest($url, $data)
    {
        $jsonData = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['errors' => ['message' => 'Curl Error: ' . $err]];
        }

        return json_decode($result, true);
    }
}
