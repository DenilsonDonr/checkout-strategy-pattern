<?php

namespace Tests\Unit;

use App\Services\Payment\StripeGateway;
use PHPUnit\Framework\TestCase;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;

class StripeGatewayTest extends TestCase
{
    public function test_successful_charge_returns_success_true(): void
    {
        $mockIntent = $this->createMock(PaymentIntent::class);
        $mockIntent->method('__get')->willReturnMap([
            ['status', 'succeeded'],
            ['id', 'pi_test_123'],
        ]);
        $mockIntent->method('toArray')->willReturn(['id' => 'pi_test_123', 'status' => 'succeeded']);

        $gateway = $this->getMockBuilder(StripeGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createIntent'])
            ->getMock();
        $gateway->method('createIntent')->willReturn($mockIntent);

        $result = $gateway->charge(99.99, 'USD', ['payment_method_id' => 'pm_card_visa']);

        $this->assertTrue($result['success']);
        $this->assertSame('pi_test_123', $result['reference']);
        $this->assertSame('succeeded', $result['raw']['status']);
    }

    public function test_declined_card_returns_success_false(): void
    {
        $exception = CardException::factory('Your card was declined.', 402, 'card_declined');

        $gateway = $this->getMockBuilder(StripeGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createIntent'])
            ->getMock();
        $gateway->method('createIntent')->willThrowException($exception);

        $result = $gateway->charge(99.99, 'USD', ['payment_method_id' => 'pm_card_chargeDeclined']);

        $this->assertFalse($result['success']);
        $this->assertNull($result['reference']);
        $this->assertStringContainsString('declined', strtolower($result['message']));
    }

    public function test_gateway_name_is_stripe(): void
    {
        $gateway = $this->getMockBuilder(StripeGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createIntent'])
            ->getMock();

        $this->assertSame('stripe', $gateway->gatewayName());
    }
}
