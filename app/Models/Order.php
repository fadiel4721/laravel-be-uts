<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_time',
        'total_price',
        'total_item',
        'kasir_id',
        'payment_method',
        'discount_code',
        'is_used'
    ];

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id', 'id');
    }

    /**
     * After order is created, update user's loyalty
     */
    public static function boot()
    {
        parent::boot();

        static::created(function ($order) {
            $user = $order->kasir;
            $loyalty = Loyalty::firstOrCreate(['user_id' => $user->id]);
            
            // Update total spent
            $loyalty->total_spent += $order->total_price;

            // Calculate new level and discount
            $levelData = Loyalty::calculateLevel($loyalty->total_spent);
            $loyalty->level = $levelData['level'];
            $loyalty->discount = $levelData['discount'];

            $loyalty->save();
        });
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function loyalty()
    {
        return $this->belongsTo(Loyalty::class, 'kasir_id', 'user_id');
    }
}
