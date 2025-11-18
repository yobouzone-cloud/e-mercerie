<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'couturier_id',
        'mercerie_id',
        'total_amount',
        'status',
    ];

    public function couturier()
    {
        return $this->belongsTo(User::class, 'couturier_id');
    }

    public function mercerie()
    {
        return $this->belongsTo(User::class, 'mercerie_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Vérifie si la commande peut être acceptée (stock suffisant)
     */
    public function canBeAccepted()
    {
        foreach ($this->items as $item) {
            if (!$item->merchantSupply->hasSufficientStock($item->quantity)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Accepte la commande et décrémente les stocks
     */
    public function accept()
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Cette commande a déjà été traitée.');
        }

        if (!$this->canBeAccepted()) {
            throw new \Exception('Stock insuffisant pour certaines fournitures.');
        }

        \DB::transaction(function () {
            foreach ($this->items as $item) {
                $item->merchantSupply->safeDecrement($item->quantity);
            }
            
            $this->update(['status' => 'confirmed']);
        });
    }
}