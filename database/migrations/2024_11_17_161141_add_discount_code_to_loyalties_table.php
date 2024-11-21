<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('loyalties', function (Blueprint $table) {
            $table->string('discount_code')->nullable()->unique()->after('discount');
        });
    }
    
    public function down()
    {
        Schema::table('loyalties', function (Blueprint $table) {
            $table->dropColumn('discount_code');
        });
    }
    
};
