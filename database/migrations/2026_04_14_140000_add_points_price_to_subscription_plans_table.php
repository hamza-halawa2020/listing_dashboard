<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('points_price')->default(0)->after('price')
                ->comment('Number of points required to redeem this plan. 0 means not redeemable with points.');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('points_price');
        });
    }
};
