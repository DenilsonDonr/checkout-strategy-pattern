<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;


#[Table('orders')]
#[Fillable(['customer_name', 'customer_email', 'total_price', 'currency', 'payment_method', 'status', 'notes'])]
class Order extends Model
{
    //
}
