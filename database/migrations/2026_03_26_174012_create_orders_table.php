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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // fields
            $table->string('customer_name');
            $table->string('customer_email');
            $table->decimal('total_price', 10, 2); // total price
            $table->string('currency', 3); // currency code (e.g., USD, EUR)
            $table->string('payment_method'); // e.g., credit card, PayPal
            $table->string('status'); // e.g., pending, completed, canceled
            $table->text('notes')->nullable(); // additional notes or instructions

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
