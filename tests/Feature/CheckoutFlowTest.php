<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\StripeGateway;
use App\Services\PaymentFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private array $basePayload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePayload = [
            'customer_name'  => 'John Doe',
            'customer_email' => 'john@example.com',
            'total_price'    => 99.99,
            'currency'       => 'USD',
            'notes'          => null,
        ];
    }

    // --- Stripe (mocked — no real API calls) ---

    public function test_stripe_checkout_succeeds_and_persists_order_and_payment(): void
    {
        $this->mockStripeGateway(success: true, reference: 'pi_test_success_123');

        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'payment_method' => 'stripe',
            'payload'        => ['payment_method_id' => 'pm_card_visa'],
        ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'john@example.com',
            'status'         => 'paid',
            'payment_method' => 'stripe',
        ]);

        $this->assertDatabaseHas('payments', [
            'gateway'           => 'stripe',
            'status'            => 'success',
            'gateway_reference' => 'pi_test_success_123',
        ]);
    }

    public function test_stripe_declined_card_redirects_back_with_error(): void
    {
        $this->mockStripeGateway(success: false, message: 'Your card was declined.');

        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'payment_method' => 'stripe',
            'payload'        => ['payment_method_id' => 'pm_card_chargeDeclined'],
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['payment']);

        $this->assertDatabaseHas('orders', ['status' => 'failed']);
        $this->assertDatabaseHas('payments', ['status' => 'failed']);
    }

    // --- PayPal (simulated) ---

    public function test_paypal_checkout_succeeds_with_valid_email(): void
    {
        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'payment_method' => 'paypal',
            'payload'        => ['paypal_email' => 'buyer@paypal.com'],
        ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'gateway' => 'paypal',
            'status'  => 'success',
        ]);
    }

    public function test_paypal_checkout_fails_without_email(): void
    {
        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'payment_method' => 'paypal',
            'payload'        => [],
        ]));

        $response->assertSessionHasErrors(['payment']);
        $this->assertDatabaseHas('payments', ['status' => 'failed']);
    }

    // --- Transfer (simulated) ---

    public function test_bank_transfer_checkout_creates_pending_payment(): void
    {
        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'payment_method' => 'transfer',
            'payload'        => [],
        ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', ['status' => 'paid']);
        $this->assertDatabaseHas('payments', [
            'gateway' => 'transfer',
            'status'  => 'pending',
        ]);
    }

    // --- Validation ---

    public function test_checkout_requires_customer_name(): void
    {
        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'customer_name'  => '',
            'payment_method' => 'stripe',
        ]));

        $response->assertSessionHasErrors(['customer_name']);
    }

    public function test_checkout_requires_valid_email(): void
    {
        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'customer_email' => 'not-an-email',
            'payment_method' => 'stripe',
        ]));

        $response->assertSessionHasErrors(['customer_email']);
    }

    public function test_checkout_rejects_invalid_payment_method(): void
    {
        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'payment_method' => 'bitcoin',
        ]));

        $response->assertSessionHasErrors(['payment_method']);
    }

    public function test_checkout_requires_positive_total(): void
    {
        $response = $this->post('/checkout', array_merge($this->basePayload, [
            'total_price'    => 0,
            'payment_method' => 'stripe',
        ]));

        $response->assertSessionHasErrors(['total_price']);
    }

    // --- Success page ---

    public function test_success_page_shows_order_details(): void
    {
        $order = Order::create([
            'customer_name'  => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'total_price'    => 49.99,
            'currency'       => 'USD',
            'payment_method' => 'stripe',
            'status'         => 'paid',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'gateway'  => 'stripe',
            'amount'   => 49.99,
            'currency' => 'USD',
            'status'   => 'success',
            'paid_at'  => now(),
        ]);

        $response = $this->get("/checkout/success/{$order->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CheckoutSuccess')
            ->has('order')
            ->has('payment')
        );
    }

    // --- Helper ---

    private function mockStripeGateway(bool $success, ?string $reference = null, string $message = 'Payment succeeded.'): void
    {
        $mock = $this->createMock(StripeGateway::class);
        $mock->method('charge')->willReturn([
            'success'   => $success,
            'reference' => $reference,
            'message'   => $message,
            'raw'       => ['status' => $success ? 'succeeded' : 'failed'],
        ]);
        $mock->method('gatewayName')->willReturn('stripe');

        $this->instance(StripeGateway::class, $mock);
    }
}
