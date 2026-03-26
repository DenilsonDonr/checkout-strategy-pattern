<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('payments')]
#[Fillable(['order_id', 'gateway', 'amount', 'currency', 'status', 'gateway_reference', 'gateway_response', 'paid_at'])]
class Payment extends Model
{
    //
}
