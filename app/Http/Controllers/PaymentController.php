<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $merchantRef = 'INV-' . Str::uuid();

        $payload = [
            'method' => $request->payment_method ?? 'BRIVA', 
            'merchant_ref' => $merchantRef,
            'amount' => 150000,
            'customer_name' => 'Dimas',
            'customer_email' => 'dimas@mail.com',
            'customer_phone' => '08123456789',
            'order_items' => [
                [
                    'name' => 'Premium Product',
                    'price' => 150000,
                    'quantity' => 1,
                ]
            ],
            'callback_url' => 'https://ngrok.com/r/ai/tripay/callback', 
            'return_url' => 'http://localhost:8000/payment/success',
            'expired_time' => now()->addMinutes(60)->timestamp,
        ];

        $signature = hash_hmac(
            'sha256',
            config('tripay.merchant_code') . $merchantRef . $payload['amount'],
            config('tripay.private_key')
        );

        $payload['merchant_code'] = config('tripay.merchant_code');
        $payload['signature'] = $signature;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('tripay.api_key'),
            'Accept' => 'application/json',
        ])->post(config('tripay.base_url') . '/transaction/create', $payload);

        return $response->json();
    }
}
