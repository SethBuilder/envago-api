<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(int $amountCents, string $currency, array $paymentMethods, array $metadata): object
    {
        return PaymentIntent::create([
            'amount'               => $amountCents,
            'currency'             => $currency,
            'payment_method_types' => $paymentMethods,
            'metadata'             => $metadata,
        ]);
    }
}
