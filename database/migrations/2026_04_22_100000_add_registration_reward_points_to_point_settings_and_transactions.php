<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_reward_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('points')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_reward_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('old_points')->default(0);
            $table->unsignedInteger('new_points')->default(0);
            $table->text('reason')->nullable();
            $table->foreignId('changed_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE point_transactions
            MODIFY COLUMN type ENUM(
                'referral_bonus',
                'referee_bonus',
                'signup_bonus',
                'redeem',
                'admin_add',
                'admin_deduct',
                'expire',
                'adjustment'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE point_transactions
            MODIFY COLUMN type ENUM(
                'referral_bonus',
                'referee_bonus',
                'redeem',
                'admin_add',
                'admin_deduct',
                'expire',
                'adjustment'
            ) NOT NULL
        ");

        Schema::dropIfExists('registration_reward_histories');
        Schema::dropIfExists('registration_reward_settings');
    }
};
