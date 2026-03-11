<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {

            $session = $event->data->object;

            $fineId = $session->metadata->fine_id ?? null;

            if ($fineId) {
                $fine = Fine::find($fineId);

                $fine->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}