<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function charge(float $amount, string $currency, array $payload): array
    {
        try {
            $intent = $this->createIntent($amount, $currency, $payload['payment_method_id']);

            return [
                'success'   => $intent->status === 'succeeded',
                'reference' => $intent->id,
                'message'   => 'Payment succeeded.',
                'raw'       => $intent->toArray(),
            ];
        } catch (CardException $e) {
            return [
                'success'   => false,
                'reference' => null,
                'message'   => $e->getMessage(),
                'raw'       => ['error' => $e->getStripeCode() ?? 'card_error'],
            ];
        }
    }

    public function createIntent(float $amount, string $currency, string $paymentMethodId): PaymentIntent
    {
        return PaymentIntent::create([
            'amount'         => (int) ($amount * 100),
            'currency'       => strtolower($currency),
            'payment_method' => $paymentMethodId,
            'confirm'        => true,
            'return_url'     => url('/checkout'),
        ]);
    }

    public function gatewayName(): string
    {
        return 'stripe';
    }
}
