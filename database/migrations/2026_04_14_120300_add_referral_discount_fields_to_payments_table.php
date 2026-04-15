<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('referral_id')->nullable()->after('subscription_id')->constrained('referrals')->nullOnDelete();
            $table->decimal('original_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('original_amount');
        });

        DB::table('payments')->update(['original_amount' => DB::raw('amount'),'discount_amount' => 0]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['referral_id']);
            $table->dropColumn(['referral_id','original_amount','discount_amount']);
        });
    }
};
