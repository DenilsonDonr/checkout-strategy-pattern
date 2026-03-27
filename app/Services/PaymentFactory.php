<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentMethod;
use App\Services\Payment\PayPalGateway;
use App\Services\Payment\StripeGateway;
use App\Services\Payment\TransferGateway;

class PaymentFactory
{
    public static function make(PaymentMethod $method): PaymentGatewayInterface
    {
        $class = match($method) {
            PaymentMethod::Stripe   => StripeGateway::class,
            PaymentMethod::PayPal   => PayPalGateway::class,
            PaymentMethod::Transfer => TransferGateway::class,
        };

        return app($class);
    }
}
