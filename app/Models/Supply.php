<?php

namespace App\Models;

use App\Models\Supply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supply extends Model
{
    use HasFactory;

    /**
     * Fillable attributes for mass assignment.
     */
    protected $fillable = ['name', 'category', 'unit', 'measure', 'sale_mode', 'description', 'image_url'];

    /**
     * Automatically append accessor attributes when model is serialized to array/json.
     */
    protected $appends = ['image_url'];

    public function merchantSupplies()
    {
        return $this->hasMany(MerchantSupply::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Ensure image_url returns a full URL when accessed (fallback to default image).
     */
    public function getImageUrlAttribute($value)
    {
        if ($value) {
            // If the stored value is already an absolute URL, return it; otherwise use asset()
            if (preg_match('#^https?://#i', $value)) {
                return $value;
            }
            return asset(ltrim($value, '/'));
        }

        return asset('images/default.png');
    }
}

