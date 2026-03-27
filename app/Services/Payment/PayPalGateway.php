<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class PayPalGateway implements PaymentGatewayInterface
{
    public function charge(float $amount, string $currency, array $payload): array
    {
        // Simulated PayPal response — ready to swap with PayPal Orders API v2
        $success   = isset($payload['paypal_email']) && str_contains($payload['paypal_email'], '@');
        $reference = $success ? 'PAYID-' . strtoupper(Str::random(20)) : null;

        return [
            'success'   => $success,
            'reference' => $reference,
            'message'   => $success ? 'PayPal payment approved.' : 'PayPal payment could not be processed.',
            'raw'       => [
                'id'             => $reference,
                'intent'         => 'CAPTURE',
                'status'         => $success ? 'COMPLETED' : 'DECLINED',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value'         => number_format($amount, 2),
                    ],
                ]],
            ],
        ];
    }

    public function gatewayName(): string
    {
        return 'paypal';
    }
}
