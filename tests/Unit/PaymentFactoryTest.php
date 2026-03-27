<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Services\Payment\PayPalGateway;
use App\Services\Payment\StripeGateway;
use App\Services\Payment\TransferGateway;
use App\Services\PaymentFactory;
use Tests\TestCase;

class PaymentFactoryTest extends TestCase
{
    public function test_it_creates_stripe_gateway(): void
    {
        $gateway = PaymentFactory::make(PaymentMethod::Stripe);

        $this->assertInstanceOf(StripeGateway::class, $gateway);
        $this->assertSame('stripe', $gateway->gatewayName());
    }

    public function test_it_creates_paypal_gateway(): void
    {
        $gateway = PaymentFactory::make(PaymentMethod::PayPal);

        $this->assertInstanceOf(PayPalGateway::class, $gateway);
        $this->assertSame('paypal', $gateway->gatewayName());
    }

    public function test_it_creates_transfer_gateway(): void
    {
        $gateway = PaymentFactory::make(PaymentMethod::Transfer);

        $this->assertInstanceOf(TransferGateway::class, $gateway);
        $this->assertSame('transfer', $gateway->gatewayName());
    }
}
