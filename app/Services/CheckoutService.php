<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * Process a full checkout: create order, charge gateway, persist payment.
     *
     * @param  array{
     *   customer_name: string,
     *   customer_email: string,
     *   total_price: float,
     *   currency: string,
     *   payment_method: string,
     *   notes: string|null,
     *   payload: array
     * } $data
     */
    public function process(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $method  = PaymentMethod::from($data['payment_method']);
            $gateway = PaymentFactory::make($method);

            $order = Order::create([
                'customer_name'  => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'total_price'    => $data['total_price'],
                'currency'       => $data['currency'],
                'payment_method' => $method->value,
                'status'         => 'pending',
                'notes'          => $data['notes'] ?? null,
            ]);

            $result = $gateway->charge(
                (float) $data['total_price'],
                $data['currency'],
                $data['payload'] ?? []
            );

            $paymentStatus = match(true) {
                $result['success'] && $gateway->gatewayName() === 'transfer' => 'pending',
                $result['success']                                            => 'success',
                default                                                       => 'failed',
            };

            $payment = Payment::create([
                'order_id'          => $order->id,
                'gateway'           => $gateway->gatewayName(),
                'amount'            => $data['total_price'],
                'currency'          => $data['currency'],
                'status'            => $paymentStatus,
                'gateway_reference' => $result['reference'],
                'gateway_response'  => $result['raw'],
                'paid_at'           => $result['success'] && $paymentStatus === 'success'
                                        ? now()
                                        : null,
            ]);

            $order->update(['status' => $paymentStatus === 'failed' ? 'failed' : 'paid']);

            return [
                'success' => $result['success'],
                'message' => $result['message'],
                'order'   => $order->fresh(),
                'payment' => $payment,
            ];
        });
    }
}
