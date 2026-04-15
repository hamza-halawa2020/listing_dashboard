<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('referral_code_used');
            $table->enum('status', ['pending', 'qualified', 'rewarded', 'rejected'])->default('pending');
            $table->enum('trigger_type', ['register', 'first_payment', 'first_subscription'])->default('first_payment');
            $table->foreignId('qualified_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('qualified_subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('referee_reward_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->unsignedInteger('referrer_points_awarded')->default(0);
            $table->enum('referee_reward_type', ['none', 'points', 'fixed_discount', 'percent_discount'])->default('none');
            $table->decimal('referee_reward_value', 10, 2)->default(0);
            $table->unsignedInteger('referee_points_awarded')->default(0);
            $table->decimal('referee_discount_amount_applied', 10, 2)->default(0);
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamp('referee_reward_applied_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['referrer_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
