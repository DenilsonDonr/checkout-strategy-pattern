<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkoutService) {}

    public function index(): Response
    {
        return Inertia::render('Checkout', [
            'paymentMethods' => collect(PaymentMethod::cases())->map(fn ($m) => [
                'value' => $m->value,
                'label' => $m->label(),
            ]),
            'stripeKey' => config('services.stripe.public_key'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name'   => ['required', 'string', 'max:255'],
            'customer_email'  => ['required', 'email'],
            'total_price'     => ['required', 'numeric', 'min:0.01'],
            'currency'        => ['required', 'string', 'size:3'],
            'payment_method'  => ['required', new Enum(PaymentMethod::class)],
            'notes'           => ['nullable', 'string'],
            'payload'         => ['nullable', 'array'],
        ]);

        $result = $this->checkoutService->process($validated);

        if (! $result['success']) {
            return back()->withErrors(['payment' => $result['message']])->withInput();
        }

        return redirect()->route('checkout.success', $result['order']->id);
    }

    public function success(int $orderId): Response
    {
        $order = Order::with('latestPayment')->findOrFail($orderId);

        return Inertia::render('CheckoutSuccess', [
            'order'   => $order,
            'payment' => $order->latestPayment,
        ]);
    }
}
