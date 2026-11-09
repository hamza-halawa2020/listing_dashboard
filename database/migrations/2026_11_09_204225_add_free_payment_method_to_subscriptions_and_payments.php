<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `subscriptions` MODIFY `payment_method` ENUM('cash','credit_card','bank_transfer','fawry','vodafone_cash','online','instapay','free') NULL");
        DB::statement("ALTER TABLE `payments` MODIFY `payment_method` ENUM('cash','credit_card','bank_transfer','fawry','vodafone_cash','instapay','free') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `subscriptions` MODIFY `payment_method` ENUM('cash','credit_card','bank_transfer','fawry','vodafone_cash','online','instapay') NULL");
        DB::statement("ALTER TABLE `payments` MODIFY `payment_method` ENUM('cash','credit_card','bank_transfer','fawry','vodafone_cash','instapay') NOT NULL");
    }
};
