<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'merchant_supply_id',
        'quantity',
        'measure_requested',
        'price',
        'subtotal',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function merchantSupply()
    {
        return $this->belongsTo(MerchantSupply::class, 'merchant_supply_id');
    }
}
