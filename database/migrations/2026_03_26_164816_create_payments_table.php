<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Foreign key to the orders table
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            // fields
            $table->string('gateway'); // e.g., 'stripe', 'paypal'
            $table->decimal('amount', 10, 2); // amount paid
            $table->string('currency', 3); // e.g., 'USD'
            $table->string('status'); // e.g., 'pending', 'completed', 'failed'
            $table->string('gateway_reference')->nullable(); // reference from the payment gateway
            $table->json('gateway_response')->nullable(); // store the raw response from the gateway for debugging
            $table->timestamp('paid_at')->nullable(); // when the payment was completed

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
