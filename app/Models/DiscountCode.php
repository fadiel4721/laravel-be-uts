<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'discount_percentage', 'is_active', 'used_at', 'order_id'];

    // Relasi dengan order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Method untuk mengecek apakah diskon aktif
    public static function active($code)
    {
        // Cari diskon berdasarkan kode dan pastikan diskon aktif
        return self::where('code', $code)
            ->where('is_active', true) // Memeriksa kolom 'is_active'
            ->whereNull('used_at') // Pastikan diskon belum digunakan
            ->first();
    }
}
