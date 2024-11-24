<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loyalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_spent',
        'level',
        'discount',
        'discount_code' // Menggunakan discount_code untuk mencatat kode yang digunakan
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Calculate loyalty level based on total spent
     */
    public static function calculateLevel($totalSpent)
    {
        if ($totalSpent >= 100000) { // Level 5
            return ['level' => 1, 'discount' => 0]; // Diskon 30% untuk level 5
        } elseif ($totalSpent >= 500000) { // Level 4
            return ['level' => 2, 'discount' => 15]; // Diskon 25% untuk level 4
        } elseif ($totalSpent >= 1000000) { // Level 3
            return ['level' => 3, 'discount' => 20]; // Diskon 20% untuk level 3
        } elseif ($totalSpent >= 2500000) { // Level 2
            return ['level' => 4, 'discount' => 25]; // Diskon 15% untuk level 2
        } elseif ($totalSpent >= 5000000) { // Level 1
            return ['level' => 5, 'discount' => 30]; // Tidak ada diskon untuk level 1
        } else { // Level 0
            return ['level' => 0, 'discount' => 0]; // Tidak ada diskon untuk level 0
        }
    }


    /**
     * Check if the discount code has been used by this user.
     * Returns true if the discount code has already been used.
     */
    public function hasUsedDiscountCode($discountCode)
    {
        return $this->discount_code === $discountCode;
    }

    /**
     * Mark a discount code as used by this user.
     */
    public function markDiscountCodeAsUsed($discountCode)
    {
        $this->discount_code = $discountCode;
        $this->save();
    }
    // Model Loyalty


    public function orders()
    {
        return $this->hasMany(Order::class); // Loyalitas memiliki banyak order
    }
}
