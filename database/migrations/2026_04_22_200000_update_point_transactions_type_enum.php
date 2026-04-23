<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE point_transactions MODIFY COLUMN type ENUM(
            'referral_bonus',
            'referee_bonus',
            'signup_bonus',
            'subscription_bonus',
            'visit_bonus',
            'redeem',
            'admin_add',
            'admin_deduct',
            'expire',
            'adjustment'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE point_transactions MODIFY COLUMN type ENUM(
            'referral_bonus',
            'referee_bonus',
            'redeem',
            'admin_add',
            'admin_deduct',
            'expire',
            'adjustment'
        ) NOT NULL");
    }
};
