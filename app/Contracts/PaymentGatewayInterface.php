<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Process a payment charge.
     *
     * Returns an array with:
     *   - success: bool
     *   - reference: string|null  (gateway transaction ID)
     *   - message: string         (human-readable result)
     *   - raw: array              (full simulated gateway response)
     */
    public function charge(float $amount, string $currency, array $payload): array;

    /**
     * The gateway identifier (e.g. 'stripe', 'paypal', 'transfer').
     */
    public function gatewayName(): string;
}
