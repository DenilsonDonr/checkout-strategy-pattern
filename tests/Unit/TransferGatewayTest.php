<?php

namespace Tests\Unit;

use App\Services\Payment\TransferGateway;
use PHPUnit\Framework\TestCase;

class TransferGatewayTest extends TestCase
{
    private TransferGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new TransferGateway();
    }

    public function test_transfer_always_returns_success(): void
    {
        $result = $this->gateway->charge(200.00, 'USD', []);

        $this->assertTrue($result['success']);
    }

    public function test_transfer_status_is_pending(): void
    {
        $result = $this->gateway->charge(200.00, 'USD', []);

        $this->assertSame('pending', $result['raw']['status']);
    }

    public function test_transfer_reference_starts_with_trf(): void
    {
        $result = $this->gateway->charge(200.00, 'USD', []);

        $this->assertStringStartsWith('TRF-', $result['reference']);
    }

    public function test_transfer_includes_instructions_in_raw(): void
    {
        $result = $this->gateway->charge(200.00, 'USD', []);

        $this->assertArrayHasKey('instructions', $result['raw']);
    }

    public function test_gateway_name_is_transfer(): void
    {
        $this->assertSame('transfer', $this->gateway->gatewayName());
    }
}
