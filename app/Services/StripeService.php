<?php

namespace App\Services;

use App\Models\Fine;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret_key'));
    }

    public function createCheckoutSession(Fine $fine)
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',

            'line_items' => [[
                'price_data' => [
                    'currency' => 'idr',
                    'product_data' => [
                        'name' => 'Denda ' . strtoupper($fine->type),
                    ],
                    'unit_amount' => $fine->amount, 
                ],
                'quantity' => 1,
            ]],

            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('stripe.cancel'),

            'metadata' => [
                'fine_id' => $fine->id,
            ],
        ]);

        return $session;
    }
}