<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Stripe   = 'stripe';
    case PayPal   = 'paypal';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match($this) {
            self::Stripe   => 'Credit Card (Stripe)',
            self::PayPal   => 'PayPal',
            self::Transfer => 'Bank Transfer',
        };
    }
}
