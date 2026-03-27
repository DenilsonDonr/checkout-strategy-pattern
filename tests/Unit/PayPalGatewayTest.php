<?php

namespace Tests\Unit;

use App\Services\Payment\PayPalGateway;
use PHPUnit\Framework\TestCase;

class PayPalGatewayTest extends TestCase
{
    private PayPalGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new PayPalGateway();
    }

    public function test_valid_paypal_email_returns_success(): void
    {
        $result = $this->gateway->charge(50.00, 'USD', ['paypal_email' => 'buyer@example.com']);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['reference']);
        $this->assertStringStartsWith('PAYID-', $result['reference']);
        $this->assertSame('COMPLETED', $result['raw']['status']);
    }

    public function test_missing_paypal_email_returns_failure(): void
    {
        $result = $this->gateway->charge(50.00, 'USD', []);

        $this->assertFalse($result['success']);
        $this->assertNull($result['reference']);
    }

    public function test_invalid_paypal_email_returns_failure(): void
    {
        $result = $this->gateway->charge(50.00, 'USD', ['paypal_email' => 'not-an-email']);

        $this->assertFalse($result['success']);
    }

    public function test_gateway_name_is_paypal(): void
    {
        $this->assertSame('paypal', $this->gateway->gatewayName());
    }
}
