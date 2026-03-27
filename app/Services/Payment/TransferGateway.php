<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class TransferGateway implements PaymentGatewayInterface
{
    public function charge(float $amount, string $currency, array $payload): array
    {
        // Bank transfers are always "pending" — confirmed manually by an operator
        $reference = 'TRF-' . strtoupper(Str::random(12));

        return [
            'success'   => true,
            'reference' => $reference,
            'message'   => 'Transfer instructions sent. Payment pending bank confirmation.',
            'raw'       => [
                'reference'    => $reference,
                'bank_account' => '****4242',
                'amount'       => number_format($amount, 2),
                'currency'     => strtoupper($currency),
                'status'       => 'pending',
                'instructions' => 'Please transfer the amount to account ****4242 using reference ' . $reference,
            ],
        ];
    }

    public function gatewayName(): string
    {
        return 'transfer';
    }
}
