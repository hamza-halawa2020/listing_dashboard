<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->decimal('price_before_discount', 10, 2)->nullable()->after('description');
            $table->decimal('price_after_discount', 10, 2)->nullable()->after('price_before_discount');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('price_after_discount');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'price_before_discount',
                'price_after_discount',
                'discount_amount',
            ]);
        });
    }
};
