<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table('orders')]
#[Fillable(['customer_name', 'customer_email', 'total_price', 'currency', 'payment_method', 'status', 'notes'])]
class Order extends Model
{
    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'total_price'    => 'decimal:2',
    ];

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
