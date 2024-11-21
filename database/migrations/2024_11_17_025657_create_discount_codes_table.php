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
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('discount_percentage'); // Persentase diskon
            $table->boolean('is_active')->default(true); // Status aktif
            $table->timestamp('used_at')->nullable(); // Waktu digunakan
            $table->unsignedBigInteger('order_id')->nullable(); // ID order yang menggunakannya
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
